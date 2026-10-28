<?php

namespace Tests\Feature;

use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\User;
use App\Services\ArcadeWagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ArcadeSecurityAndCoinFlipTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private ArcadeGame $game;

    private User $userA;

    private Enrollment $enrollmentA;

    private User $userB;

    private Enrollment $enrollmentB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create();

        $this->game = ArcadeGame::create([
            'edition_id' => $this->edition->id,
            'slug' => 'coin-flip',
            'name' => 'Coin Flip',
            'is_active' => true,
            'is_pvp' => true,
            'min_wager' => 10,
            'max_wager' => 500,
            'daily_win_cap' => null,
            'payout_config' => ['house_edge_pct' => 10],
        ]);

        $participantA = Participant::factory()->create();
        $this->userA = $participantA->user;
        $this->enrollmentA = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participantA->id,
            'points' => 200,
        ]);

        $participantB = Participant::factory()->create();
        $this->userB = $participantB->user;
        $this->enrollmentB = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participantB->id,
            'points' => 200,
        ]);
    }

    public function test_arcade_policy_rules(): void
    {
        // Active game + enrolled participant = allowed
        $this->assertTrue(Gate::forUser($this->userA)->allows('play', $this->game));

        // Inactive game = denied
        $this->game->update(['is_active' => false]);
        $this->assertFalse(Gate::forUser($this->userA)->allows('play', $this->game));
        $this->game->update(['is_active' => true]);

        // Unenrolled participant = denied
        $nonEnrolledParticipant = Participant::factory()->create();
        $this->assertFalse(Gate::forUser($nonEnrolledParticipant->user)->allows('play', $this->game));

        // Non-participant user (e.g. admin or regular user) = denied
        $plainUser = User::factory()->create();
        $this->assertFalse(Gate::forUser($plainUser)->allows('play', $this->game));
    }

    public function test_wager_service_deduct_and_credit(): void
    {
        $service = app(ArcadeWagerService::class);

        DB::transaction(function () use ($service) {
            $locked = $service->deductWager($this->enrollmentA->id, 50);
            $this->assertEquals(150, $locked->points);

            $service->creditPayout($locked, 90);
            $this->assertEquals(240, $locked->points);
        });

        $this->enrollmentA->refresh();
        $this->assertEquals(240, $this->enrollmentA->points);
    }

    public function test_wager_service_prevents_negative_balance(): void
    {
        $service = app(ArcadeWagerService::class);

        $this->expectException(HttpException::class);

        DB::transaction(function () use ($service) {
            $service->deductWager($this->enrollmentA->id, 250); // Balance is only 200
        });
    }

    public function test_wager_service_enforces_daily_win_cap(): void
    {
        $service = app(ArcadeWagerService::class);
        $skillGame = ArcadeGame::factory()->create([
            'edition_id' => $this->edition->id,
            'slug' => 'binary-blitz',
            'daily_win_cap' => 100,
        ]);

        ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $skillGame->id,
            'wager' => 50,
            'outcome' => 'win',
            'payout' => 100,
            'started_at' => now(),
            'resolved_at' => now(),
        ]);

        $this->expectException(HttpException::class);
        $service->checkDailyWinCap($this->enrollmentA->id, $skillGame);
    }

    public function test_start_coin_flip_deducts_wager_and_creates_session(): void
    {
        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.coin-flip.start'), [
                'wager' => 50,
            ]);

        $response->assertStatus(201);
        $sessionId = $response->json('session_id');

        $this->assertDatabaseHas('arcade_sessions', [
            'id' => $sessionId,
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'challenger_enrollment_id' => null,
        ]);

        $this->enrollmentA->refresh();
        $this->assertEquals(150, $this->enrollmentA->points);
    }

    public function test_start_coin_flip_validates_wager_bounds(): void
    {
        // Below min_wager (10)
        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.coin-flip.start'), [
                'wager' => 5,
            ]);
        $response->assertStatus(422);

        // Above max_wager (500)
        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.coin-flip.start'), [
                'wager' => 501,
            ]);
        $response->assertStatus(422);
    }

    public function test_player_cannot_join_own_coin_flip(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
        ]);

        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.coin-flip.join', ['session' => $session->id]), [
                'wager' => 50,
            ]);

        $response->assertStatus(422);
    }

    public function test_player_cannot_join_with_mismatched_wager(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
        ]);

        $response = $this->actingAs($this->userB)
            ->postJson(route('arcade.coin-flip.join', ['session' => $session->id]), [
                'wager' => 40,
            ]);

        $response->assertStatus(422);
    }

    public function test_join_coin_flip_resolves_and_credits_winner(): void
    {
        // Start: User A has 150 points (50 wagered), User B has 200 points
        $this->enrollmentA->update(['points' => 150]);
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
        ]);

        $response = $this->actingAs($this->userB)
            ->postJson(route('arcade.coin-flip.join', ['session' => $session->id]), [
                'wager' => 50,
            ]);

        $response->assertStatus(200);
        $winner = $response->json('winner');
        $payout = $response->json('payout');

        // Total pot = 100, house edge 10% = 90 payout
        $this->assertEquals(90, $payout);
        $this->assertContains($winner, ['creator', 'challenger']);

        $session->refresh();
        $this->assertEquals($this->enrollmentB->id, $session->challenger_enrollment_id);
        $this->assertNotNull($session->resolved_at);
        $this->assertEquals($payout, $session->payout);

        $this->enrollmentA->refresh();
        $this->enrollmentB->refresh();

        if ($winner === 'creator') {
            $this->assertEquals('win', $session->outcome);
            $this->assertEquals(240, $this->enrollmentA->points); // 150 + 90
            $this->assertEquals(150, $this->enrollmentB->points); // 200 - 50
        } else {
            $this->assertEquals('loss', $session->outcome);
            $this->assertEquals(150, $this->enrollmentA->points); // untouched
            $this->assertEquals(240, $this->enrollmentB->points); // 200 - 50 + 90
        }
    }

    public function test_cannot_join_already_resolved_session(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'challenger_enrollment_id' => $this->enrollmentB->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'win',
            'resolved_at' => now(),
        ]);

        $response = $this->actingAs($this->userB)
            ->postJson(route('arcade.coin-flip.join', ['session' => $session->id]), [
                'wager' => 50,
            ]);

        $response->assertStatus(422);
    }

    public function test_status_endpoint_authorization(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
        ]);

        // Creator can view status
        $this->actingAs($this->userA)
            ->getJson(route('arcade.coin-flip.status', ['session' => $session->id]))
            ->assertStatus(200)
            ->assertJson(['outcome' => 'pending', 'payout' => 0]);

        // Third party user cannot view status before joining
        $this->actingAs($this->userB)
            ->getJson(route('arcade.coin-flip.status', ['session' => $session->id]))
            ->assertStatus(403);
    }

    public function test_cancel_session_rules_and_refund(): void
    {
        $this->enrollmentA->update(['points' => 150]);

        // Cannot cancel if less than 5 minutes old
        $recentSession = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'started_at' => now()->subMinutes(2),
        ]);

        $this->actingAs($this->userA)
            ->postJson(route('arcade.coin-flip.cancel', ['session' => $recentSession->id]))
            ->assertStatus(422);

        // Can cancel if 5+ minutes old
        $oldSession = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'started_at' => now()->subMinutes(6),
        ]);

        $this->actingAs($this->userA)
            ->postJson(route('arcade.coin-flip.cancel', ['session' => $oldSession->id]))
            ->assertStatus(200);

        $oldSession->refresh();
        $this->assertEquals('expired', $oldSession->outcome);
        $this->assertNotNull($oldSession->resolved_at);

        $this->enrollmentA->refresh();
        $this->assertEquals(200, $this->enrollmentA->points); // 150 + 50 refunded
    }

    public function test_pending_coin_flips_lobby_listing(): void
    {
        ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
        ]);

        // User B sees User A's pending game
        $response = $this->actingAs($this->userB)
            ->getJson(route('arcade.coin-flip.lobby'));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));

        // User A does not see their own pending game in the joinable list
        $response = $this->actingAs($this->userA)
            ->getJson(route('arcade.coin-flip.lobby'));

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }
}
