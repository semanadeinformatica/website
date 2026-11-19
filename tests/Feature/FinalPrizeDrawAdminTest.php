<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\EventType;
use App\Models\Quest;
use App\Models\RaffleDraw;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalPrizeDrawAdminTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private User $admin;

    private Event $closingEvent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create(['year' => 2026]);
        $this->admin = User::factory()->admin()->create();

        $eventDay = EventDay::factory()->create(['edition_id' => $this->edition->id]);
        $eventType = EventType::factory()->create();

        $this->closingEvent = Event::factory()->create([
            'event_day_id' => $eventDay->id,
            'event_type_id' => $eventType->id,
            'name' => 'Sessão de Encerramento e Entrega de Prémios',
        ]);
    }

    private function createParticipant(int $points = 100, bool $attendClosing = true): Enrollment
    {
        $enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'points' => $points,
        ]);

        if ($attendClosing) {
            $enrollment->events()->attach($this->closingEvent->id);
        }

        return $enrollment;
    }

    public function test_guest_and_non_admin_cannot_pick_winner(): void
    {
        $draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'is_active' => true,
        ]);

        $this->post(route('admin.raffleDraws.pick-winner', $draw))
            ->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user)
            ->post(route('admin.raffleDraws.pick-winner', $draw))
            ->assertForbidden();
    }

    public function test_admin_index_supplies_raffle_and_eligible_participants(): void
    {
        $draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'name' => '1º Prémio Final',
            'is_active' => true,
        ]);

        $this->createParticipant(points: 50, attendClosing: true);
        $this->createParticipant(points: 0, attendClosing: true); // not eligible (0 pts)
        $this->createParticipant(points: 80, attendClosing: false); // not eligible (no attendance)

        $response = $this->actingAs($this->admin)
            ->get(route('admin.raffleDraws.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('CRUD/RaffleDraws/Index', false)
            ->where('eligibleCount', 1)
            ->where('totalTickets', 50)
            ->has('eligibleParticipants', 1)
            ->where('closingEventName', 'Sessão de Encerramento e Entrega de Prémios')
            ->has('items.data', 1)
        );
    }

    public function test_cannot_draw_when_no_participants_eligible(): void
    {
        $draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'is_active' => true,
        ]);

        $this->createParticipant(points: 0, attendClosing: true);
        $this->createParticipant(points: 100, attendClosing: false);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.pick-winner', $draw));

        $response->assertStatus(422);
        $this->assertNull($draw->fresh()->winner_enrollment_id);
    }

    public function test_participant_with_closing_session_quest_is_eligible(): void
    {
        $draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'is_active' => true,
        ]);

        $enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'points' => 200,
        ]);

        $quest = Quest::create([
            'name' => 'Assistir à Sessão de Encerramento',
            'edition_id' => $this->edition->id,
            'category' => 'TALK',
            'requirement_id' => $this->closingEvent->id,
            'requirement_type' => Event::class,
        ]);
        $enrollment->quests()->attach($quest->id);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.pick-winner', $draw));

        $response->assertRedirect();
        $this->assertEquals($enrollment->id, $draw->fresh()->winner_enrollment_id);
        $this->assertFalse($draw->fresh()->is_active);
    }

    public function test_winner_of_one_raffle_is_excluded_from_other_raffles_in_edition(): void
    {
        $draw1 = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'name' => '1º Prémio Final',
            'is_active' => true,
        ]);

        $draw2 = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'name' => '2º Prémio Final',
            'is_active' => true,
        ]);

        $e1 = $this->createParticipant(points: 10, attendClosing: true);
        $e2 = $this->createParticipant(points: 20, attendClosing: true);

        // Draw #1
        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.pick-winner', $draw1))
            ->assertRedirect();

        $winner1 = $draw1->fresh()->winner_enrollment_id;
        $this->assertNotNull($winner1);

        // Draw #2 must pick the other participant!
        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.pick-winner', $draw2))
            ->assertRedirect();

        $winner2 = $draw2->fresh()->winner_enrollment_id;
        $this->assertNotNull($winner2);

        $this->assertNotEquals($winner1, $winner2);
    }

    public function test_admin_can_reset_winner(): void
    {
        $e1 = $this->createParticipant(points: 50, attendClosing: true);

        $draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'winner_enrollment_id' => $e1->id,
            'is_active' => false,
            'draw_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.raffleDraws.reset-winner', $draw))
            ->assertRedirect();

        $fresh = $draw->fresh();
        $this->assertNull($fresh->winner_enrollment_id);
        $this->assertTrue($fresh->is_active);
        $this->assertNull($fresh->draw_at);
    }
}
