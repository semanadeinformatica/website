<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\EventType;
use App\Models\FinalPrize;
use App\Models\Participant;
use App\Models\Quest;
use App\Models\RaffleDraw;
use App\Models\Staff;
use App\Models\User;
use App\Services\RaffleWinnerSelector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class FinalPrizeDrawAdminTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private User $admin;

    private Event $closingEvent;

    private Quest $closingQuest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create(['year' => 2026]);
        $this->admin = User::factory()->admin()->create();

        $eventDay = EventDay::factory()->create(['edition_id' => $this->edition->id]);
        $eventType = EventType::factory()->create(['name' => 'talk']);

        $this->closingEvent = Event::factory()->create([
            'event_day_id' => $eventDay->id,
            'event_type_id' => $eventType->id,
            'name' => 'Sessão de Encerramento e Entrega de Prémios',
            'topic' => 'Encerramento',
            'enroll_in_site' => false,
        ]);

        $this->closingQuest = Quest::create([
            'name' => 'Assistir à Sessão de Encerramento',
            'edition_id' => $this->edition->id,
            'category' => 'TALK',
            'requirement_id' => $this->closingEvent->id,
            'requirement_type' => Event::class,
        ]);
    }

    private function makeDraw(int $place, array $overrides = []): RaffleDraw
    {
        return RaffleDraw::factory()->create(array_merge([
            'edition_id' => $this->edition->id,
            'place' => $place,
            'name' => "{$place}º Prémio Final",
            'is_active' => true,
        ], $overrides));
    }

    private function makeParticipant(int $points = 100, bool $attended = true): Enrollment
    {
        $enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'points' => $points,
        ]);

        if ($attended) {
            $enrollment->quests()->attach($this->closingQuest);
        }

        return $enrollment;
    }

    private function makeStaff(Enrollment $enrollment): void
    {
        $department = Department::factory()->create([
            'edition_id' => $this->edition->id,
        ]);

        Staff::factory()->create([
            'participant_id' => $enrollment->participant_id,
            'department_id' => $department->id,
        ]);
    }

    public function test_guest_and_non_admin_cannot_access_final_prize_draws(): void
    {
        $draw = $this->makeDraw(1);

        $this->post(route('admin.raffleDraws.pick-winner', $draw))
            ->assertRedirect(route('login'));

        $this->get(route('admin.raffleDraws.simulate'))
            ->assertRedirect(route('login'));

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.raffleDraws.pick-winner', $draw))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.raffleDraws.simulate'))
            ->assertForbidden();
    }

    public function test_admin_index_supplies_only_attending_non_staff_participants(): void
    {
        $this->makeDraw(3);
        $this->makeDraw(1);
        $this->makeDraw(2);

        $this->makeParticipant(points: 50);
        $this->makeParticipant(points: 0);
        $this->makeParticipant(points: 80, attended: false);

        $staff = $this->makeParticipant(points: 500);
        $this->makeStaff($staff);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.raffleDraws.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('CRUD/RaffleDraws/Index', false)
            ->has('items.data', 3)
            ->where('items.data.0.place', 1)
            ->where('items.data.1.place', 2)
            ->where('items.data.2.place', 3)
            ->where('eligibleCount', 1)
            ->where('totalEntries', 50)
            ->has('eligibleParticipants', 1)
            ->where('eligibleParticipants.0.points', 50)
            ->where('hasClosingEvent', true)
            ->where('requiredEventName', 'Sessão de Encerramento e Entrega de Prémios')
        );
    }

    public function test_draw_all_picks_first_to_third_and_excludes_previous_winners(): void
    {
        // Creation order intentionally does not match prize order.
        $third = $this->makeDraw(3);
        $first = $this->makeDraw(1);
        $second = $this->makeDraw(2);

        $participantA = $this->makeParticipant(points: 30);
        $participantB = $this->makeParticipant(points: 20);
        $participantC = $this->makeParticipant(points: 10);

        $pools = [];

        $this->mock(RaffleWinnerSelector::class, function (MockInterface $mock) use (&$pools) {
            $mock->shouldReceive('select')
                ->times(3)
                ->andReturnUsing(function ($entries) use (&$pools) {
                    $ids = collect($entries)->pluck('enrollment_id')->all();
                    $pools[] = $ids;

                    return $ids[0];
                });
        });

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.draw-all'), [
                'edition_id' => $this->edition->id,
            ])
            ->assertRedirect();

        $this->assertSame($participantA->id, $first->fresh()->winner_enrollment_id);
        $this->assertSame($participantB->id, $second->fresh()->winner_enrollment_id);
        $this->assertSame($participantC->id, $third->fresh()->winner_enrollment_id);

        $this->assertFalse($first->fresh()->is_active);
        $this->assertFalse($second->fresh()->is_active);
        $this->assertFalse($third->fresh()->is_active);
        $this->assertNotNull($first->fresh()->draw_at);

        // The selector receives the full pool first and then progressively
        // smaller pools, excluding everyone who already won.
        $this->assertCount(3, $pools[0]);
        $this->assertCount(2, $pools[1]);
        $this->assertNotContains($participantA->id, $pools[1]);
        $this->assertCount(1, $pools[2]);
        $this->assertNotContains($participantA->id, $pools[2]);
        $this->assertNotContains($participantB->id, $pools[2]);
    }

    public function test_draw_all_rolls_back_when_a_pending_draw_is_inactive(): void
    {
        $this->makeDraw(1);
        $this->makeDraw(2, ['is_active' => false]);
        $this->makeDraw(3);

        $this->makeParticipant(points: 10);

        $this->mock(RaffleWinnerSelector::class, function (MockInterface $mock) {
            $mock->shouldReceive('select')->once()->andReturnUsing(
                fn ($entries) => collect($entries)->pluck('enrollment_id')->first()
            );
        });

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.draw-all'), [
                'edition_id' => $this->edition->id,
            ])
            ->assertStatus(422);

        $this->assertSame(
            0,
            RaffleDraw::query()->whereNotNull('winner_enrollment_id')->count()
        );
    }

    public function test_staff_members_are_not_eligible_to_win(): void
    {
        $this->makeDraw(1);

        $regular = $this->makeParticipant(points: 10);
        $staff = $this->makeParticipant(points: 1000);
        $this->makeStaff($staff);

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.pick-winner', [
                'draw' => RaffleDraw::query()->value('id'),
            ]))
            ->assertRedirect();

        $this->assertSame(
            $regular->id,
            RaffleDraw::query()->value('winner_enrollment_id')
        );
    }

    public function test_participants_must_have_attended_the_closing_session(): void
    {
        $this->makeDraw(1);
        $participant = $this->makeParticipant(points: 100, attended: false);

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.pick-winner', [
                'draw' => RaffleDraw::query()->value('id'),
            ]))
            ->assertStatus(422);

        $this->assertNull(RaffleDraw::query()->value('winner_enrollment_id'));

        $participant->quests()->attach($this->closingQuest);

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.pick-winner', [
                'draw' => RaffleDraw::query()->value('id'),
            ]))
            ->assertRedirect();

        $this->assertSame(
            $participant->id,
            RaffleDraw::query()->value('winner_enrollment_id')
        );
    }

    public function test_free_entry_closing_session_can_be_scanned_by_staff(): void
    {
        $this->makeDraw(1);

        $participant = Participant::factory()->create([
            'quest_code' => 'SCAN-123',
        ]);

        $enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
            'points' => 150,
        ]);

        // The closing session is free entry, so the participant is not in the
        // event enrollment pivot. The staff scan is the attendance record.
        $this->assertFalse($enrollment->events()->whereKey($this->closingEvent->id)->exists());

        $staffUser = User::factory()->create();
        $department = Department::factory()->create(['edition_id' => $this->edition->id]);
        Staff::factory()->create([
            'participant_id' => $staffUser->usertype_id,
            'department_id' => $department->id,
        ]);

        $this->actingAs($staffUser)
            ->post(route('quest.give', $this->closingQuest), [
                'quest_code' => 'SCAN-123',
            ])
            ->assertRedirect();

        $this->assertTrue(
            $enrollment->quests()->whereKey($this->closingQuest->id)->exists()
        );

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.pick-winner', [
                'draw' => RaffleDraw::query()->value('id'),
            ]))
            ->assertRedirect();

        $this->assertSame(
            $enrollment->id,
            RaffleDraw::query()->value('winner_enrollment_id')
        );
    }

    public function test_reset_all_clears_only_the_selected_edition(): void
    {
        $this->makeDraw(1, [
            'winner_enrollment_id' => $this->makeParticipant()->id,
            'is_active' => false,
            'draw_at' => now(),
        ]);
        $this->makeDraw(2, [
            'winner_enrollment_id' => $this->makeParticipant()->id,
            'is_active' => false,
            'draw_at' => now(),
        ]);

        $otherEdition = Edition::factory()->create(['year' => 2025]);
        $otherDraw = RaffleDraw::factory()->create([
            'edition_id' => $otherEdition->id,
            'place' => 1,
            'winner_enrollment_id' => Enrollment::factory()->create([
                'edition_id' => $otherEdition->id,
            ])->id,
            'is_active' => false,
            'draw_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.reset-all'), [
                'edition_id' => $this->edition->id,
            ])
            ->assertRedirect();

        RaffleDraw::query()
            ->where('edition_id', $this->edition->id)
            ->get()
            ->each(function (RaffleDraw $draw) {
                $this->assertNull($draw->winner_enrollment_id);
                $this->assertTrue($draw->is_active);
                $this->assertNull($draw->draw_at);
            });

        $otherDraw->refresh();
        $this->assertNotNull($otherDraw->winner_enrollment_id);
        $this->assertFalse($otherDraw->is_active);
    }

    public function test_simulate_dry_runs_pending_prizes_without_saving_winners(): void
    {
        $this->makeDraw(1);
        $this->makeDraw(2);
        $this->makeDraw(3);

        FinalPrize::factory()->create([
            'edition_id' => $this->edition->id,
            'place' => 1,
            'image_path' => 'final-prize-images/first-prize.png',
        ]);

        $this->makeParticipant(points: 100);
        $this->makeParticipant(points: 50);
        $this->makeParticipant(points: 25);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.raffleDraws.simulate', [
                'edition_id' => $this->edition->id,
            ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('CRUD/RaffleDraws/Simulate', false)
            ->where('isSimulation', true)
            ->has('prizes', 3)
            ->where('prizes.0.place', 1)
            ->where('prizes.1.place', 2)
            ->where('prizes.2.place', 3)
            ->where('prizes.0.image_url', Storage::disk('public')->url('final-prize-images/first-prize.png'))
            ->where('prizes.0.is_simulated', true)
            ->has('prizes.0.winner')
            ->has('prizes.1.winner')
            ->has('prizes.2.winner')
        );

        $this->assertSame(
            0,
            RaffleDraw::query()->whereNotNull('winner_enrollment_id')->count()
        );
    }

    public function test_simulate_replays_stored_winners(): void
    {
        $firstWinner = $this->makeParticipant(points: 100);
        $secondWinner = $this->makeParticipant(points: 50);
        $thirdWinner = $this->makeParticipant(points: 25);

        $first = $this->makeDraw(1, [
            'winner_enrollment_id' => $firstWinner->id,
            'is_active' => false,
        ]);
        $second = $this->makeDraw(2, [
            'winner_enrollment_id' => $secondWinner->id,
            'is_active' => false,
        ]);
        $third = $this->makeDraw(3, [
            'winner_enrollment_id' => $thirdWinner->id,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.raffleDraws.simulate', [
                'edition_id' => $this->edition->id,
            ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('CRUD/RaffleDraws/Simulate', false)
            ->where('isSimulation', false)
            ->has('prizes', 3)
            ->where('prizes.0.winner.id', $firstWinner->id)
            ->where('prizes.1.winner.id', $secondWinner->id)
            ->where('prizes.2.winner.id', $thirdWinner->id)
        );

        $this->assertSame($firstWinner->id, $first->fresh()->winner_enrollment_id);
        $this->assertSame($secondWinner->id, $second->fresh()->winner_enrollment_id);
        $this->assertSame($thirdWinner->id, $third->fresh()->winner_enrollment_id);
    }

    public function test_create_forces_the_closing_session_and_unique_places(): void
    {
        $payload = [
            'edition_id' => $this->edition->id,
            'place' => 1,
            'name' => 'Sorteio Inválido',
            'is_active' => true,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.store'), $payload)
            ->assertRedirect(route('admin.raffleDraws.index'));

        $this->assertDatabaseHas('raffle_draws', [
            'edition_id' => $this->edition->id,
            'place' => 1,
            'required_event_id' => $this->closingEvent->id,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.store'), $payload)
            ->assertSessionHasErrors('place');
    }
}
