<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\RaffleDraw;
use App\Models\RaffleTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RaffleControllerTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->edition = Edition::factory()->create();
    }

    /**
     * @return array{0: User, 1: Enrollment}
     */
    private function participantWithEnrollment(int $points = 500): array
    {
        $participant = Participant::factory()->create();

        $enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
            'points' => $points,
        ]);

        return [$participant->user, $enrollment];
    }

    private function draw(array $attributes = []): RaffleDraw
    {
        return RaffleDraw::factory()->create(array_merge([
            'edition_id' => $this->edition->id,
            'ticket_price' => 25,
            'is_active' => true,
        ], $attributes));
    }

    public function test_guest_can_browse_raffle_draws(): void
    {
        $draw = $this->draw();

        $response = $this->get(route('raffle.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Raffle', false)
            ->has('draws', 1)
            ->where('draws.0.id', $draw->id)
            ->where('draws.0.total_tickets', 0)
            ->where('myTickets', [])
            ->where('points', null)
            ->where('isEnrolled', false)
        );
    }

    public function test_enrolled_participant_sees_points_and_own_tickets(): void
    {
        [$user, $enrollment] = $this->participantWithEnrollment(300);
        $draw = $this->draw();

        RaffleTicket::factory()->create([
            'raffle_draw_id' => $draw->id,
            'enrollment_id' => $enrollment->id,
            'quantity' => 3,
        ]);

        $response = $this->actingAs($user)->get(route('raffle.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Raffle', false)
            ->where('points', 300)
            ->where('isEnrolled', true)
            ->where('draws.0.total_tickets', 3)
            ->where('myTickets.'.$draw->id, 3)
        );
    }

    public function test_participant_not_enrolled_has_no_points(): void
    {
        $participant = Participant::factory()->create();

        $response = $this->actingAs($participant->user)->get(route('raffle.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Raffle', false)
            ->where('myTickets', [])
            ->where('points', null)
            ->where('isEnrolled', false)
        );
    }

    public function test_purchase_deducts_points_and_creates_ticket(): void
    {
        [$user, $enrollment] = $this->participantWithEnrollment(100);
        $draw = $this->draw();

        $response = $this->actingAs($user)
            ->from(route('raffle.index'))
            ->post(route('raffle.buy', $draw), ['quantity' => 2]);

        $response->assertRedirect(route('raffle.index'));
        $response->assertSessionHas('flash');

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'points' => 50,
        ]);

        $this->assertDatabaseHas('raffle_tickets', [
            'raffle_draw_id' => $draw->id,
            'enrollment_id' => $enrollment->id,
            'quantity' => 2,
        ]);
    }

    public function test_repeat_purchase_increments_existing_ticket(): void
    {
        [$user, $enrollment] = $this->participantWithEnrollment(100);
        $draw = $this->draw();

        $this->actingAs($user)->post(route('raffle.buy', $draw), ['quantity' => 1]);
        $this->actingAs($user)->post(route('raffle.buy', $draw), ['quantity' => 2]);

        $this->assertDatabaseCount('raffle_tickets', 1);
        $this->assertDatabaseHas('raffle_tickets', [
            'raffle_draw_id' => $draw->id,
            'enrollment_id' => $enrollment->id,
            'quantity' => 3,
        ]);
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'points' => 25,
        ]);
    }

    public function test_insufficient_balance_is_rejected(): void
    {
        [$user, $enrollment] = $this->participantWithEnrollment(10);
        $draw = $this->draw();

        $response = $this->actingAs($user)
            ->post(route('raffle.buy', $draw), ['quantity' => 1]);

        $response->assertStatus(422);

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'points' => 10,
        ]);
        $this->assertDatabaseCount('raffle_tickets', 0);
    }

    public function test_inactive_draw_is_rejected(): void
    {
        [$user] = $this->participantWithEnrollment();
        $draw = $this->draw(['is_active' => false]);

        $response = $this->actingAs($user)
            ->post(route('raffle.buy', $draw), ['quantity' => 1]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('raffle_tickets', 0);
    }

    public function test_completed_draw_is_rejected(): void
    {
        [$user, $enrollment] = $this->participantWithEnrollment();
        $draw = $this->draw(['winner_enrollment_id' => $enrollment->id]);

        $response = $this->actingAs($user)
            ->post(route('raffle.buy', $draw), ['quantity' => 1]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('raffle_tickets', 0);
    }

    public function test_past_deadline_is_rejected(): void
    {
        [$user] = $this->participantWithEnrollment();
        $draw = $this->draw(['draw_at' => now()->subMinute()]);

        $response = $this->actingAs($user)
            ->post(route('raffle.buy', $draw), ['quantity' => 1]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('raffle_tickets', 0);
    }

    public function test_draw_from_another_edition_is_rejected(): void
    {
        [$user, $enrollment] = $this->participantWithEnrollment();

        $otherEdition = Edition::factory()->create([
            'year' => now()->year - 1,
        ]);
        $draw = RaffleDraw::factory()->create([
            'edition_id' => $otherEdition->id,
            'ticket_price' => 25,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('raffle.buy', $draw), ['quantity' => 1]);

        $response->assertNotFound();

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'points' => 500,
        ]);
        $this->assertDatabaseCount('raffle_tickets', 0);
    }

    public function test_non_participant_cannot_buy_tickets(): void
    {
        $admin = User::factory()->admin()->create();
        $draw = $this->draw();

        $response = $this->actingAs($admin)
            ->post(route('raffle.buy', $draw), ['quantity' => 1]);

        $response->assertForbidden();
        $this->assertDatabaseCount('raffle_tickets', 0);
    }

    public function test_participant_not_enrolled_cannot_buy_tickets(): void
    {
        $participant = Participant::factory()->create();
        $draw = $this->draw();

        $response = $this->actingAs($participant->user)
            ->post(route('raffle.buy', $draw), ['quantity' => 1]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('raffle_tickets', 0);
    }

    public function test_quantity_is_validated(): void
    {
        [$user] = $this->participantWithEnrollment();
        $draw = $this->draw();

        foreach ([0, 51, 'invalid'] as $quantity) {
            $response = $this->actingAs($user)
                ->from(route('raffle.index'))
                ->post(route('raffle.buy', $draw), ['quantity' => $quantity]);

            $response->assertRedirect(route('raffle.index'));
            $response->assertSessionHasErrors('quantity');
        }

        $this->assertDatabaseCount('raffle_tickets', 0);
    }

    public function test_guest_cannot_buy_tickets(): void
    {
        $draw = $this->draw();

        $response = $this->post(route('raffle.buy', $draw), ['quantity' => 1]);

        $response->assertRedirect(route('login'));
    }

    public function test_completed_draw_loads_winner_in_public_index(): void
    {
        [$user, $enrollment] = $this->participantWithEnrollment(100);
        $draw = $this->draw([
            'is_active' => false,
            'winner_enrollment_id' => $enrollment->id,
        ]);

        $response = $this->get(route('raffle.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Raffle', false)
            ->where('draws.0.winner.id', $enrollment->id)
            ->where('draws.0.winner.participant.user.name', $user->name)
            ->where('draws.0.winner.participant.user.profile_photo_url', $user->profile_photo_url)
            ->missing('draws.0.winner.points')
            ->missing('draws.0.winner.participant.cv_path')
            ->missing('draws.0.winner.participant.cv_url')
            ->missing('draws.0.winner.participant.quest_code')
            ->missing('draws.0.winner.participant.quest_qr_code')
            ->missing('draws.0.winner.participant.social_media')
        );
    }

    public function test_rate_limit_is_enforced(): void
    {
        [$user] = $this->participantWithEnrollment(1000);
        $draw = $this->draw(['ticket_price' => 1]);

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)
                ->post(route('raffle.buy', $draw), ['quantity' => 1])
                ->assertRedirect();
        }

        $response = $this->actingAs($user)
            ->post(route('raffle.buy', $draw), ['quantity' => 1]);

        $response->assertStatus(429);
    }
}
