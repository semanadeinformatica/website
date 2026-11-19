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
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RafflePickWinnerTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private RaffleDraw $draw;

    private Quest $closingQuest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create(['year' => 2026]);

        $eventDay = EventDay::factory()->create(['edition_id' => $this->edition->id]);
        $eventType = EventType::factory()->create(['name' => 'talk']);

        $closingEvent = Event::factory()->create([
            'event_day_id' => $eventDay->id,
            'event_type_id' => $eventType->id,
            'name' => 'Sessão de Encerramento e Entrega de Prémios',
            'topic' => 'Encerramento',
        ]);

        $this->closingQuest = Quest::create([
            'name' => 'Assistir à Sessão de Encerramento',
            'edition_id' => $this->edition->id,
            'category' => 'TALK',
            'requirement_id' => $closingEvent->id,
            'requirement_type' => Event::class,
        ]);

        $this->draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'place' => 1,
            'is_active' => true,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function participantEnrollment(int $points = 100): Enrollment
    {
        $enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'points' => $points,
        ]);

        $enrollment->quests()->attach($this->closingQuest);

        return $enrollment;
    }

    public function test_admin_can_pick_a_winner_and_close_the_draw(): void
    {
        $first = $this->participantEnrollment(3);
        $second = $this->participantEnrollment(1);

        $response = $this->actingAs($this->admin())
            ->from(route('admin.index'))
            ->post(route('admin.raffleDraws.pick-winner', $this->draw));

        $response->assertRedirect(route('admin.index'));
        $response->assertSessionHas('flash');

        $this->draw->refresh();

        $this->assertContains($this->draw->winner_enrollment_id, [$first->id, $second->id]);
        $this->assertFalse($this->draw->is_active);
        $this->assertNotNull($this->draw->draw_at);
    }

    public function test_winner_is_logged(): void
    {
        Log::spy();

        $this->participantEnrollment(1);

        $this->actingAs($this->admin())
            ->post(route('admin.raffleDraws.pick-winner', $this->draw));

        Log::shouldHaveReceived('info')->withArgs(
            fn (string $message, array $context) => $message === 'Raffle draw winner selected'
                && $context['draw_id'] === $this->draw->id
        );
    }

    public function test_cannot_pick_a_winner_twice(): void
    {
        $this->participantEnrollment(1);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.raffleDraws.pick-winner', $this->draw));

        $this->draw->refresh();
        $winnerId = $this->draw->winner_enrollment_id;

        $response = $this->actingAs($admin)
            ->post(route('admin.raffleDraws.pick-winner', $this->draw));

        $response->assertStatus(422);

        $this->draw->refresh();
        $this->assertSame($winnerId, $this->draw->winner_enrollment_id);
    }

    public function test_cannot_pick_a_winner_for_an_inactive_draw(): void
    {
        $this->participantEnrollment(1);
        $this->draw->update(['is_active' => false]);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.raffleDraws.pick-winner', $this->draw));

        $response->assertStatus(422);

        $this->draw->refresh();
        $this->assertNull($this->draw->winner_enrollment_id);
    }

    public function test_cannot_pick_a_winner_without_eligible_participants(): void
    {
        // Points without attendance must not enter the draw.
        Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'points' => 100,
        ]);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.raffleDraws.pick-winner', $this->draw));

        $response->assertStatus(422);

        $this->draw->refresh();
        $this->assertNull($this->draw->winner_enrollment_id);
        $this->assertTrue($this->draw->is_active);
    }

    public function test_guest_cannot_pick_a_winner(): void
    {
        $this->participantEnrollment(1);

        $this->post(route('admin.raffleDraws.pick-winner', $this->draw))
            ->assertRedirect(route('login'));
    }
}
