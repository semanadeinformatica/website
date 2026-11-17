<?php

namespace Tests\Feature;

use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArcadeBetLimitTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create();
    }

    public function test_high_low_enforces_the_daily_bet_limit(): void
    {
        $game = $this->game('high-low', 2, [
            'under_threshold' => 0,
            'over_threshold' => 52,
            'exact_number' => 50,
            'under_multiplier' => 2.0,
            'over_multiplier' => 2.0,
            'exact_multiplier' => 20.0,
        ]);
        [$user, $enrollment] = $this->enrollment();

        $play = fn () => $this->actingAs($user)->postJson(
            route('arcade.high-low.play', [
                'edition' => $this->edition->id,
                'wager' => 10,
                'bet_type' => 'under',
            ]),
        );

        $play()->assertOk();
        $play()->assertOk();
        $this->assertSame(80, $enrollment->fresh()->points);

        $play()
            ->assertStatus(422)
            ->assertJson(['message' => 'Limite diário de apostas atingido']);

        $this->assertSame(80, $enrollment->fresh()->points);
        $this->assertSame(2, ArcadeSession::where('arcade_game_id', $game->id)->count());
    }

    public function test_mastermind_enforces_the_daily_bet_limit(): void
    {
        $game = $this->game('mastermind', 1, [
            'max_attempts' => 5,
            'attempt_multipliers' => [
                '1' => 3.0,
                '2' => 2.0,
                '3' => 1.5,
                '4' => 1.2,
                '5' => 1.0,
            ],
        ]);
        [$user, $enrollment] = $this->enrollment();

        $this->actingAs($user)
            ->postJson(route('arcade.mastermind.start'), ['wager' => 10])
            ->assertStatus(201);

        $this->actingAs($user)
            ->postJson(route('arcade.mastermind.start'), ['wager' => 10])
            ->assertStatus(422)
            ->assertJson(['message' => 'Limite diário de apostas atingido']);

        $this->assertSame(90, $enrollment->fresh()->points);
        $this->assertSame(1, ArcadeSession::where('arcade_game_id', $game->id)->count());
    }

    public function test_bug_sweeper_enforces_the_daily_bet_limit(): void
    {
        $game = $this->game('bug-sweeper', 1, [
            'bomb_count' => 3,
            'click_multipliers' => [1.2, 1.5, 2.0, 3.0, 5.0],
        ]);
        [$user, $enrollment] = $this->enrollment();

        $this->actingAs($user)
            ->postJson(route('arcade.bug-sweeper.start'), ['wager' => 10])
            ->assertStatus(201);

        $this->actingAs($user)
            ->postJson(route('arcade.bug-sweeper.start'), ['wager' => 10])
            ->assertStatus(422)
            ->assertJson(['message' => 'Limite diário de apostas atingido']);

        $this->assertSame(90, $enrollment->fresh()->points);
        $this->assertSame(1, ArcadeSession::where('arcade_game_id', $game->id)->count());
    }

    public function test_pending_bug_sweeper_payout_is_settled_when_bet_limit_blocks_new_start(): void
    {
        $game = $this->game('bug-sweeper', 1, [
            'bomb_count' => 3,
            'click_multipliers' => [1.2, 1.5, 2.0, 3.0, 5.0],
        ]);
        [$user, $enrollment] = $this->enrollment();

        $sessionId = $this->actingAs($user)
            ->postJson(route('arcade.bug-sweeper.start'), ['wager' => 10])
            ->assertStatus(201)
            ->json('session_id');

        $session = ArcadeSession::findOrFail($sessionId);
        $session->update([
            'server_state' => [
                'bombs' => [0, 1, 2],
                'clicks' => 0,
                'clicked' => [],
            ],
        ]);

        $this->actingAs($user)
            ->postJson(route('arcade.bug-sweeper.click', ['session' => $sessionId]), [
                'cell_index' => 3,
            ])
            ->assertOk();

        // Settlement must run and commit before the bet limit rejects the new start.
        $this->actingAs($user)
            ->postJson(route('arcade.bug-sweeper.start'), ['wager' => 10])
            ->assertStatus(422)
            ->assertJson(['message' => 'Limite diário de apostas atingido']);

        $session->refresh();
        $this->assertSame('cashout', $session->outcome);
        $this->assertSame(12, $session->payout);

        $this->assertSame(102, $enrollment->fresh()->points); // 100 - 10 + 12
        $this->assertSame(1, ArcadeSession::where('arcade_game_id', $game->id)->count());
    }

    public function test_coin_flip_counts_creator_and_challenger_bets_separately(): void
    {
        $game = $this->game('coin-flip', 1, ['house_edge_pct' => 10]);
        [$userA, $enrollmentA] = $this->enrollment();
        [$userB, $enrollmentB] = $this->enrollment();
        [$userC] = $this->enrollment();

        // Creator A places one bet and is then blocked.
        $sessionA = $this->actingAs($userA)
            ->postJson(route('arcade.coin-flip.start'), ['wager' => 10])
            ->assertStatus(201)
            ->json('session_id');

        $this->actingAs($userA)
            ->postJson(route('arcade.coin-flip.start'), ['wager' => 10])
            ->assertStatus(422)
            ->assertJson(['message' => 'Limite diário de apostas atingido']);

        // Challenger B joins A's session: that is B's one bet.
        $this->actingAs($userB)
            ->postJson(route('arcade.coin-flip.join', ['session' => $sessionA]), ['wager' => 10])
            ->assertOk();

        $pointsAfterFirstJoin = $enrollmentB->fresh()->points;

        // Creator C creates another session, but B cannot join a second one.
        $sessionC = $this->actingAs($userC)
            ->postJson(route('arcade.coin-flip.start'), ['wager' => 10])
            ->assertStatus(201)
            ->json('session_id');

        $this->actingAs($userB)
            ->postJson(route('arcade.coin-flip.join', ['session' => $sessionC]), ['wager' => 10])
            ->assertStatus(422)
            ->assertJson(['message' => 'Limite diário de apostas atingido']);

        $this->assertSame($pointsAfterFirstJoin, $enrollmentB->fresh()->points);
        $this->assertSame(1, ArcadeSession::where('arcade_game_id', $game->id)
            ->where('challenger_enrollment_id', $enrollmentB->id)
            ->count());
    }

    private function game(string $slug, int $dailyBetLimit, array $payoutConfig): ArcadeGame
    {
        return ArcadeGame::create([
            'edition_id' => $this->edition->id,
            'slug' => $slug,
            'name' => ucfirst($slug),
            'is_active' => true,
            'is_pvp' => $slug === 'coin-flip',
            'min_wager' => 5,
            'max_wager' => 100,
            'daily_win_cap' => null,
            'daily_bet_limit' => $dailyBetLimit,
            'payout_config' => $payoutConfig,
        ]);
    }

    /**
     * @return array{0: User, 1: Enrollment}
     */
    private function enrollment(int $points = 100): array
    {
        $participant = Participant::factory()->create();
        $enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
            'points' => $points,
        ]);

        return [$participant->user, $enrollment];
    }
}
