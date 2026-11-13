<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\RaffleDraw;
use App\Models\RaffleTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RafflePickWinnerTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private RaffleDraw $draw;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create();
        $this->draw = RaffleDraw::factory()->create([
            'edition_id' => $this->edition->id,
            'is_active' => true,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function enrollmentWithTickets(int $quantity): Enrollment
    {
        $participant = Participant::factory()->create();

        $enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
            'points' => 100,
        ]);

        RaffleTicket::factory()->create([
            'raffle_draw_id' => $this->draw->id,
            'enrollment_id' => $enrollment->id,
            'quantity' => $quantity,
        ]);

        return $enrollment;
    }

    public function test_admin_can_pick_a_winner_and_close_the_draw(): void
    {
        $first = $this->enrollmentWithTickets(3);
        $second = $this->enrollmentWithTickets(1);

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

        $this->enrollmentWithTickets(1);

        $this->actingAs($this->admin())
            ->post(route('admin.raffleDraws.pick-winner', $this->draw));

        Log::shouldHaveReceived('info')->withArgs(
            fn (string $message, array $context) => $message === 'Raffle draw winner selected'
                && $context['draw_id'] === $this->draw->id
        );
    }

    public function test_cannot_pick_a_winner_twice(): void
    {
        $this->enrollmentWithTickets(1);

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
        $this->enrollmentWithTickets(1);
        $this->draw->update(['is_active' => false]);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.raffleDraws.pick-winner', $this->draw));

        $response->assertStatus(422);

        $this->draw->refresh();
        $this->assertNull($this->draw->winner_enrollment_id);
    }

    public function test_cannot_pick_a_winner_without_tickets(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.raffleDraws.pick-winner', $this->draw));

        $response->assertStatus(422);

        $this->draw->refresh();
        $this->assertNull($this->draw->winner_enrollment_id);
        $this->assertTrue($this->draw->is_active);
    }

    public function test_non_admin_cannot_pick_a_winner(): void
    {
        $this->enrollmentWithTickets(1);
        $participant = Participant::factory()->create();

        $response = $this->actingAs($participant->user)
            ->post(route('admin.raffleDraws.pick-winner', $this->draw));

        $response->assertForbidden();

        $this->draw->refresh();
        $this->assertNull($this->draw->winner_enrollment_id);
    }

    public function test_guest_cannot_pick_a_winner(): void
    {
        $this->enrollmentWithTickets(1);

        $response = $this->post(route('admin.raffleDraws.pick-winner', $this->draw));

        $response->assertRedirect(route('login'));
    }
}
