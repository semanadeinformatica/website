<?php

namespace Tests\Feature;

use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class BugSweeperGameTest extends TestCase
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
            'slug' => 'bug-sweeper',
            'name' => 'Bug Sweeper',
            'is_active' => true,
            'is_pvp' => false,
            'min_wager' => 10,
            'max_wager' => 200,
            'daily_win_cap' => 1000,
            'payout_config' => [
                'bomb_count' => 3,
                'click_multipliers' => [1.2, 1.5, 2.0, 3.0, 5.0],
            ],
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

    public function test_bug_sweeper_policy_rules(): void
    {
        $this->assertTrue(Gate::forUser($this->userA)->allows('play', $this->game));

        // Inactive game
        $this->game->update(['is_active' => false]);
        $this->assertFalse(Gate::forUser($this->userA)->allows('play', $this->game));
        $this->game->update(['is_active' => true]);

        // Unenrolled participant
        $nonEnrolled = Participant::factory()->create();
        $this->assertFalse(Gate::forUser($nonEnrolled->user)->allows('play', $this->game));

        // Plain user
        $plain = User::factory()->create();
        $this->assertFalse(Gate::forUser($plain)->allows('play', $this->game));
    }

    public function test_start_bug_sweeper_deducts_wager_and_creates_session(): void
    {
        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.start'), [
                'wager' => 50,
            ]);

        $response->assertStatus(201);
        $sessionId = $response->json('session_id');
        $this->assertNotNull($sessionId);
        $this->assertEquals(9, $response->json('grid_size'));
        $this->assertEquals(3, $response->json('bomb_count'));
        $this->assertEquals([1.2, 1.5, 2.0, 3.0, 5.0], $response->json('click_multipliers'));

        // Crucial security check: bombs MUST NOT be exposed to client
        $this->assertArrayNotHasKey('bombs', $response->json());
        $this->assertArrayNotHasKey('bomb_positions', $response->json());
        $this->assertArrayNotHasKey('server_state', $response->json());

        // Points deducted
        $this->enrollmentA->refresh();
        $this->assertEquals(150, $this->enrollmentA->points);

        // Session exists in DB with hidden server state
        $session = ArcadeSession::findOrFail($sessionId);
        $this->assertEquals($this->enrollmentA->id, $session->enrollment_id);
        $this->assertEquals('pending', $session->outcome);
        $this->assertEquals(50, $session->wager);
        $this->assertCount(3, $session->server_state['bombs']);
        $this->assertEquals(0, $session->server_state['clicks']);
    }

    public function test_start_bug_sweeper_validates_wager_bounds(): void
    {
        // Below min wager (10)
        $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.start'), ['wager' => 5])
            ->assertStatus(422);

        // Above max wager (200)
        $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.start'), ['wager' => 250])
            ->assertStatus(422);

        // Exceeding balance (balance is 200, wager 201 rejected by bounds or balance)
        $this->enrollmentA->update(['points' => 30]);
        $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.start'), ['wager' => 50])
            ->assertStatus(422);
    }

    public function test_click_safe_cell_updates_multiplier_and_clicks(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'server_state' => [
                'bombs' => [0, 1, 2],
                'clicks' => 0,
                'clicked' => [],
            ],
            'started_at' => now(),
        ]);

        // Cell 3 is safe
        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.click', ['session' => $session->id]), [
                'cell_index' => 3,
            ]);

        $response->assertStatus(200);
        $this->assertFalse($response->json('is_bomb'));
        $this->assertEquals(1.2, $response->json('current_multiplier'));
        $this->assertEquals(1, $response->json('clicks'));
        $this->assertTrue($response->json('can_cashout'));
        $this->assertArrayNotHasKey('bomb_positions', $response->json());

        $session->refresh();
        $this->assertEquals(1, $session->server_state['clicks']);
        $this->assertEquals([3], $session->server_state['clicked']);
        $this->assertEquals('pending', $session->outcome);
    }

    public function test_cannot_click_already_revealed_cell(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'server_state' => [
                'bombs' => [0, 1, 2],
                'clicks' => 1,
                'clicked' => [3],
            ],
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.click', ['session' => $session->id]), [
                'cell_index' => 3,
            ]);

        $response->assertStatus(422);
        $this->assertEquals('Célula já revelada', $response->json('message'));
    }

    public function test_cannot_click_invalid_cell_index(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'server_state' => [
                'bombs' => [0, 1, 2],
                'clicks' => 0,
                'clicked' => [],
            ],
            'started_at' => now(),
        ]);

        $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.click', ['session' => $session->id]), ['cell_index' => 9])
            ->assertStatus(422);

        $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.click', ['session' => $session->id]), ['cell_index' => -1])
            ->assertStatus(422);
    }

    public function test_click_bomb_cell_ends_game_in_loss_and_reveals_all_bombs(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'server_state' => [
                'bombs' => [0, 4, 8],
                'clicks' => 1,
                'clicked' => [1],
            ],
            'started_at' => now(),
        ]);

        // Cell 4 is a bomb
        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.click', ['session' => $session->id]), [
                'cell_index' => 4,
            ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('is_bomb'));
        $this->assertEquals('loss', $response->json('outcome'));
        $this->assertEquals([0, 4, 8], $response->json('bomb_positions'));

        $session->refresh();
        $this->assertEquals('loss', $session->outcome);
        $this->assertEquals(0, $session->payout);
        $this->assertNotNull($session->resolved_at);

        // Player receives no refund/payout
        $this->enrollmentA->refresh();
        $this->assertEquals(200, $this->enrollmentA->points);
    }

    public function test_cannot_click_or_cashout_after_session_resolved(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'loss',
            'server_state' => [
                'bombs' => [0, 4, 8],
                'clicks' => 1,
                'clicked' => [4],
            ],
            'resolved_at' => now(),
        ]);

        // Further click rejected
        $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.click', ['session' => $session->id]), ['cell_index' => 2])
            ->assertStatus(422);

        // Cashout rejected
        $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.cashout', ['session' => $session->id]))
            ->assertStatus(422);
    }

    public function test_cashout_with_zero_clicks_is_rejected(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'server_state' => [
                'bombs' => [0, 1, 2],
                'clicks' => 0,
                'clicked' => [],
            ],
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.cashout', ['session' => $session->id]));

        $response->assertStatus(422);
        $this->assertEquals('É necessário pelo menos 1 clique seguro para recolher os pontos', $response->json('message'));
    }

    public function test_valid_cashout_credits_payout_and_resolves_session(): void
    {
        $this->enrollmentA->update(['points' => 150]); // Wager of 50 already deducted

        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'server_state' => [
                'bombs' => [0, 1, 2],
                'clicks' => 2, // Multiplier for 2 clicks is 1.5 => payout = floor(50 * 1.5) = 75
                'clicked' => [3, 4],
            ],
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.cashout', ['session' => $session->id]));

        $response->assertStatus(200);
        $this->assertEquals(75, $response->json('payout'));
        $this->assertEquals('cashout', $response->json('outcome'));
        $this->assertEquals([0, 1, 2], $response->json('bomb_positions'));

        $session->refresh();
        $this->assertEquals('cashout', $session->outcome);
        $this->assertEquals(75, $session->payout);
        $this->assertNotNull($session->resolved_at);

        // Balance updated
        $this->enrollmentA->refresh();
        $this->assertEquals(225, $this->enrollmentA->points); // 150 + 75
    }

    public function test_auto_cashout_when_board_is_cleared(): void
    {
        $this->enrollmentA->update(['points' => 150]);

        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'server_state' => [
                'bombs' => [0, 1, 2],
                'clicks' => 4, // 5th click reaches max multiplier (5.0)
                'clicked' => [3, 4, 5, 6],
            ],
            'started_at' => now(),
        ]);

        // Cell 7 is the 5th safe click => multiplier 5.0 => payout = 250
        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.click', ['session' => $session->id]), [
                'cell_index' => 7,
            ]);

        $response->assertStatus(200);
        $this->assertFalse($response->json('is_bomb'));
        $this->assertEquals(5.0, $response->json('current_multiplier'));
        $this->assertEquals(250, $response->json('payout'));
        $this->assertEquals('cashout', $response->json('outcome'));
        $this->assertEquals([0, 1, 2], $response->json('bomb_positions'));

        $session->refresh();
        $this->assertEquals('cashout', $session->outcome);
        $this->assertEquals(250, $session->payout);
        $this->assertNotNull($session->resolved_at);

        $this->enrollmentA->refresh();
        $this->assertEquals(400, $this->enrollmentA->points); // 150 + 250
    }

    public function test_unauthorized_user_cannot_access_or_tamper_with_session(): void
    {
        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'server_state' => [
                'bombs' => [0, 1, 2],
                'clicks' => 1,
                'clicked' => [3],
            ],
            'started_at' => now(),
        ]);

        // User B cannot click User A's session
        $this->actingAs($this->userB)
            ->postJson(route('arcade.bug-sweeper.click', ['session' => $session->id]), ['cell_index' => 4])
            ->assertStatus(403);

        // User B cannot cashout User A's session
        $this->actingAs($this->userB)
            ->postJson(route('arcade.bug-sweeper.cashout', ['session' => $session->id]))
            ->assertStatus(403);
    }

    public function test_daily_win_cap_enforced_on_bug_sweeper(): void
    {
        $this->game->update(['daily_win_cap' => 300]);

        // Prior cashout today of 300
        ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'cashout',
            'payout' => 300,
            'started_at' => now(),
            'resolved_at' => now(),
            'created_at' => now(),
        ]);

        // Starting a new game is rejected
        $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.start'), ['wager' => 50])
            ->assertStatus(422)
            ->assertJson(['message' => 'Limite diário atingido']);
    }

    public function test_auto_settlement_respects_remaining_daily_win_cap(): void
    {
        $this->game->update(['daily_win_cap' => 300]);

        ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'cashout',
            'payout' => 290,
            'started_at' => now(),
            'resolved_at' => now(),
            'created_at' => now(),
        ]);

        $abandoned = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'server_state' => [
                'bombs' => [0, 1, 2],
                'clicks' => 1,
                'clicked' => [3],
            ],
            'started_at' => now(),
        ]);

        // Nominal payout would be 60, but only 10 remains under the cap.
        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.start'), ['wager' => 20]);

        $response->assertStatus(201);
        $this->assertSame(190, $response->json('balance')); // 200 + 10 - 20

        $abandoned->refresh();
        $this->assertSame('cashout', $abandoned->outcome);
        $this->assertSame(10, $abandoned->payout);

        $this->enrollmentA->refresh();
        $this->assertSame(190, $this->enrollmentA->points);
    }

    public function test_cashout_respects_remaining_daily_win_cap(): void
    {
        $this->game->update(['daily_win_cap' => 300]);

        ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'cashout',
            'payout' => 290,
            'started_at' => now(),
            'resolved_at' => now(),
            'created_at' => now(),
        ]);

        $session = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'server_state' => [
                'bombs' => [0, 1, 2],
                'clicks' => 1,
                'clicked' => [3],
            ],
            'started_at' => now(),
        ]);

        // Nominal payout would be 60, but only 10 remains under the cap.
        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.cashout', ['session' => $session->id]));

        $response->assertStatus(200);
        $this->assertSame(10, $response->json('payout'));

        $session->refresh();
        $this->assertSame('cashout', $session->outcome);
        $this->assertSame(10, $session->payout);

        $this->enrollmentA->refresh();
        $this->assertSame(210, $this->enrollmentA->points);
    }

    public function test_start_auto_resolves_abandoned_session_with_safe_clicks(): void
    {
        // User A has an abandoned session where they made 1 click (1.2x) with 50 wager
        $abandoned = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'server_state' => [
                'bombs' => [0, 1, 2],
                'clicks' => 1,
                'clicked' => [3],
            ],
            'started_at' => now(),
        ]);

        // Starting a new game auto-cashes out the abandoned session (50 * 1.2 = 60 payout)
        // and deducts the new wager (20): 200 + 60 - 20 = 240
        $response = $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.start'), ['wager' => 20]);

        $response->assertStatus(201);
        $this->assertSame(240, $response->json('balance'));

        $abandoned->refresh();
        $this->assertSame('cashout', $abandoned->outcome);
        $this->assertSame(60, $abandoned->payout);
        $this->assertNotNull($abandoned->resolved_at);

        $this->enrollmentA->refresh();
        $this->assertSame(240, $this->enrollmentA->points);
    }

    public function test_start_forfeits_abandoned_session_with_zero_clicks(): void
    {
        // User A has an abandoned session where they clicked 0 cells
        $abandoned = ArcadeSession::create([
            'enrollment_id' => $this->enrollmentA->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'pending',
            'server_state' => [
                'bombs' => [0, 1, 2],
                'clicks' => 0,
                'clicked' => [],
            ],
            'started_at' => now(),
        ]);

        // Starting a new game resolves previous as loss and deducts new wager (20): 200 - 20 = 180
        $this->actingAs($this->userA)
            ->postJson(route('arcade.bug-sweeper.start'), ['wager' => 20])
            ->assertStatus(201);

        $abandoned->refresh();
        $this->assertSame('loss', $abandoned->outcome);
        $this->assertSame(0, $abandoned->payout);
        $this->assertNotNull($abandoned->resolved_at);

        $this->enrollmentA->refresh();
        $this->assertSame(180, $this->enrollmentA->points);
    }
}
