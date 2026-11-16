<?php

namespace Tests\Feature;

use App\Models\ArcadeGame;
use App\Models\ArcadeNetFlowReview;
use App\Models\ArcadeSession;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\User;
use App\Services\ArcadeNetFlowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ArcadeNetFlowTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private ArcadeGame $game;

    private User $admin;

    private Enrollment $alice;

    private Enrollment $bob;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create();
        $this->admin = User::factory()->admin()->create();

        $this->game = ArcadeGame::factory()->create([
            'edition_id' => $this->edition->id,
            'slug' => 'coin-flip',
            'is_pvp' => true,
            'min_wager' => 10,
            'max_wager' => 500,
        ]);

        $this->alice = $this->enrollmentFor(Participant::factory()->create());
        $this->bob = $this->enrollmentFor(Participant::factory()->create());
    }

    public function test_service_aggregates_net_flow_between_a_pair(): void
    {
        $this->resolvedSession($this->alice, $this->bob, 100, true);
        $this->resolvedSession($this->alice, $this->bob, 100, true);
        $this->resolvedSession($this->bob, $this->alice, 100, false);
        $this->resolvedSession($this->bob, $this->alice, 100, true);

        $pair = app(ArcadeNetFlowService::class)
            ->pairs($this->edition->id)
            ->firstOrFail();

        $aliceIsLow = $pair['low']['enrollment_id'] === $this->alice->id;

        $this->assertSame(4, $pair['matches']);
        $this->assertSame(400, $pair['total_wagered']);
        $this->assertSame(3, $aliceIsLow ? $pair['low_wins'] : $pair['high_wins']);
        $this->assertSame(1, $aliceIsLow ? $pair['high_wins'] : $pair['low_wins']);
        $this->assertSame(100, $aliceIsLow ? $pair['low_losses'] : $pair['high_losses']);
        $this->assertSame(300, $aliceIsLow ? $pair['high_losses'] : $pair['low_losses']);
        $this->assertSame(200, $pair['net_flow']);
        $this->assertSame(
            $aliceIsLow ? 'high_to_low' : 'low_to_high',
            $pair['flow_direction'],
        );
        $this->assertSame(
            $aliceIsLow ? 'low' : 'high',
            $pair['dominant'],
        );
        $this->assertSame(0.75, $pair['dominant_share']);
        $this->assertSame(ArcadeNetFlowService::RISK_MEDIUM, $pair['risk_level']);
        $this->assertContains('one_sided', $pair['risk_reasons']);
        $this->assertContains('high_volume', $pair['risk_reasons']);
        $this->assertSame(ArcadeNetFlowService::REVIEW_UNREVIEWED, $pair['review_state']);
        $this->assertNull($pair['review']);
    }

    public function test_service_ignores_pending_non_pvp_and_other_edition_sessions(): void
    {
        $this->resolvedSession($this->alice, $this->bob, 50, true);

        ArcadeSession::create([
            'enrollment_id' => $this->alice->id,
            'challenger_enrollment_id' => $this->bob->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
        ]);

        $singlePlayerGame = ArcadeGame::factory()->create([
            'edition_id' => $this->edition->id,
            'slug' => 'high-low',
            'is_pvp' => false,
        ]);
        ArcadeSession::create([
            'enrollment_id' => $this->alice->id,
            'challenger_enrollment_id' => $this->bob->id,
            'arcade_game_id' => $singlePlayerGame->id,
            'wager' => 50,
            'outcome' => 'win',
            'resolved_at' => now(),
        ]);

        $otherEdition = Edition::factory()->create(['year' => now()->year - 1]);
        $otherGame = ArcadeGame::factory()->create([
            'edition_id' => $otherEdition->id,
            'slug' => 'coin-flip',
            'is_pvp' => true,
        ]);
        ArcadeSession::create([
            'enrollment_id' => $this->alice->id,
            'challenger_enrollment_id' => $this->bob->id,
            'arcade_game_id' => $otherGame->id,
            'wager' => 50,
            'outcome' => 'win',
            'resolved_at' => now(),
        ]);

        $pairs = app(ArcadeNetFlowService::class)->pairs($this->edition->id);

        $this->assertCount(1, $pairs);
        $this->assertSame(1, $pairs->first()['matches']);
        $this->assertSame(50, $pairs->first()['net_flow']);
    }

    public function test_service_flags_a_high_risk_pair(): void
    {
        foreach (range(1, 5) as $ignored) {
            $this->resolvedSession($this->alice, $this->bob, 100, true);
        }

        $pair = app(ArcadeNetFlowService::class)
            ->pairs($this->edition->id)
            ->firstOrFail();

        $this->assertSame(ArcadeNetFlowService::RISK_HIGH, $pair['risk_level']);
        $this->assertSame(500, $pair['net_flow']);
        $this->assertSame(1.0, $pair['dominant_share']);
        $this->assertContains('one_sided', $pair['risk_reasons']);
        $this->assertContains('high_volume', $pair['risk_reasons']);
        $this->assertContains('repeated_pair', $pair['risk_reasons']);
    }

    public function test_admin_can_view_the_net_flow_dashboard(): void
    {
        $this->resolvedSession($this->alice, $this->bob, 100, true);
        $this->resolvedSession($this->alice, $this->bob, 100, true);
        $this->resolvedSession($this->bob, $this->alice, 100, false);
        $this->resolvedSession($this->bob, $this->alice, 100, true);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.arcadeNetFlow.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ArcadeNetFlow')
            ->has('pairs.data', 1)
            ->where('pairs.data.0.matches', 4)
            ->where('pairs.data.0.net_flow', 200)
            ->where('pairs.data.0.risk_level', ArcadeNetFlowService::RISK_MEDIUM)
            ->where('pairs.data.0.review_state', ArcadeNetFlowService::REVIEW_UNREVIEWED)
            ->where('pairs.data.0.low.name', $this->alice->participant->user->name)
            ->where('pairs.data.0.high.name', $this->bob->participant->user->name)
            ->has('summary')
            ->has('filters')
            ->has('editions', 1)
        );
    }

    public function test_admin_can_filter_by_risk_search_and_review_status(): void
    {
        $this->resolvedSession($this->alice, $this->bob, 100, true);
        $this->resolvedSession($this->alice, $this->bob, 100, true);
        $this->resolvedSession($this->bob, $this->alice, 100, false);
        $this->resolvedSession($this->bob, $this->alice, 100, true);

        $carolParticipant = Participant::factory()->create();
        $carolParticipant->user->update(['name' => 'Carol Silva']);
        $carol = $this->enrollmentFor($carolParticipant);
        $dave = $this->enrollmentFor(Participant::factory()->create());
        $this->resolvedSession($carol, $dave, 10, true);

        $highRiskResponse = $this->actingAs($this->admin)
            ->get(route('admin.arcadeNetFlow.index', ['risk' => 'medium']));

        $highRiskResponse->assertInertia(fn (Assert $page) => $page
            ->has('pairs.data', 1)
            ->where('pairs.data.0.low.name', $this->alice->participant->user->name)
        );

        $searchResponse = $this->actingAs($this->admin)
            ->get(route('admin.arcadeNetFlow.index', ['query' => 'Carol']));

        $searchResponse->assertInertia(fn (Assert $page) => $page
            ->has('pairs.data', 1)
            ->where('pairs.data.0.low.name', $carol->participant->user->name)
        );

        ArcadeNetFlowReview::create([
            'low_enrollment_id' => min($this->alice->id, $this->bob->id),
            'high_enrollment_id' => max($this->alice->id, $this->bob->id),
            'status' => ArcadeNetFlowReview::STATUS_CLEARED,
            'reviewed_by' => $this->admin->id,
            'reviewed_match_count' => 4,
            'reviewed_at' => now(),
        ]);

        $reviewedResponse = $this->actingAs($this->admin)
            ->get(route('admin.arcadeNetFlow.index', ['review_status' => 'cleared']));

        $reviewedResponse->assertInertia(fn (Assert $page) => $page
            ->has('pairs.data', 1)
            ->where('pairs.data.0.low.name', $this->alice->participant->user->name)
            ->where('pairs.data.0.review_state', ArcadeNetFlowReview::STATUS_CLEARED)
        );
    }

    public function test_admin_can_review_a_pair_and_new_activity_reopens_it(): void
    {
        $this->resolvedSession($this->alice, $this->bob, 100, true);
        $this->resolvedSession($this->alice, $this->bob, 100, true);

        $lowId = min($this->alice->id, $this->bob->id);
        $highId = max($this->alice->id, $this->bob->id);

        $this->actingAs($this->admin)
            ->post(route('admin.arcadeNetFlow.review'), [
                'low_enrollment_id' => $lowId,
                'high_enrollment_id' => $highId,
                'status' => ArcadeNetFlowReview::STATUS_CLEARED,
                'note' => 'Confirmado com os participantes.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('arcade_net_flow_reviews', [
            'low_enrollment_id' => $lowId,
            'high_enrollment_id' => $highId,
            'status' => ArcadeNetFlowReview::STATUS_CLEARED,
            'reviewed_by' => $this->admin->id,
            'reviewed_match_count' => 2,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.arcadeNetFlow.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('pairs.data.0.review_state', ArcadeNetFlowReview::STATUS_CLEARED)
                ->where('pairs.data.0.review.status', ArcadeNetFlowReview::STATUS_CLEARED)
                ->where('pairs.data.0.review.has_new_activity', false)
                ->where('pairs.data.0.review.note', 'Confirmado com os participantes.')
            );

        $this->resolvedSession($this->bob, $this->alice, 100, true);

        $this->actingAs($this->admin)
            ->get(route('admin.arcadeNetFlow.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('pairs.data.0.review_state', ArcadeNetFlowService::REVIEW_UNREVIEWED)
                ->where('pairs.data.0.review.status', ArcadeNetFlowReview::STATUS_CLEARED)
                ->where('pairs.data.0.review.has_new_activity', true)
            );

        $this->actingAs($this->admin)
            ->post(route('admin.arcadeNetFlow.review'), [
                'low_enrollment_id' => $lowId,
                'high_enrollment_id' => $highId,
                'status' => ArcadeNetFlowReview::STATUS_FLAGGED,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('arcade_net_flow_reviews', [
            'low_enrollment_id' => $lowId,
            'high_enrollment_id' => $highId,
            'status' => ArcadeNetFlowReview::STATUS_FLAGGED,
            'reviewed_match_count' => 3,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.arcadeNetFlow.review'), [
                'low_enrollment_id' => $lowId,
                'high_enrollment_id' => $highId,
                'status' => ArcadeNetFlowReview::STATUS_OPEN,
            ])
            ->assertRedirect();

        $this->actingAs($this->admin)
            ->get(route('admin.arcadeNetFlow.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('pairs.data.0.review_state', ArcadeNetFlowService::REVIEW_UNREVIEWED)
            );
    }

    public function test_review_rejects_a_pair_without_resolved_sessions(): void
    {
        $carol = $this->enrollmentFor(Participant::factory()->create());
        $dave = $this->enrollmentFor(Participant::factory()->create());

        $this->actingAs($this->admin)
            ->post(route('admin.arcadeNetFlow.review'), [
                'low_enrollment_id' => min($carol->id, $dave->id),
                'high_enrollment_id' => max($carol->id, $dave->id),
                'status' => ArcadeNetFlowReview::STATUS_CLEARED,
            ])
            ->assertSessionHasErrors('low_enrollment_id');

        $this->assertDatabaseCount('arcade_net_flow_reviews', 0);
    }

    public function test_non_admin_cannot_access_net_flow_monitoring(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.arcadeNetFlow.index'))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_net_flow_monitoring(): void
    {
        $this->get(route('admin.arcadeNetFlow.index'))
            ->assertRedirect(route('login'));
    }

    private function enrollmentFor(Participant $participant): Enrollment
    {
        return Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
            'points' => 1000,
        ]);
    }

    private function resolvedSession(
        Enrollment $creator,
        Enrollment $challenger,
        int $wager,
        bool $creatorWon,
    ): ArcadeSession {
        $payout = (int) ($wager * 2 * 0.9);

        return ArcadeSession::create([
            'enrollment_id' => $creator->id,
            'challenger_enrollment_id' => $challenger->id,
            'arcade_game_id' => $this->game->id,
            'wager' => $wager,
            'outcome' => $creatorWon ? 'win' : 'loss',
            'payout' => $payout,
            'resolved_at' => now(),
        ]);
    }
}
