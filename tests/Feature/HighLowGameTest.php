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

class HighLowGameTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private ArcadeGame $game;

    private User $user;

    private Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->edition = Edition::factory()->create();

        $this->game = ArcadeGame::create([
            'edition_id' => $this->edition->id,
            'slug' => 'high-low',
            'name' => 'High/Low Dice',
            'is_active' => true,
            'is_pvp' => false,
            'min_wager' => 10,
            'max_wager' => 500,
            'daily_win_cap' => null,
            'payout_config' => [
                'under_threshold' => 48,
                'over_threshold' => 52,
                'exact_number' => 50,
                'under_multiplier' => 2.0,
                'over_multiplier' => 2.0,
                'exact_multiplier' => 20.0,
            ],
        ]);

        $participant = Participant::factory()->create();
        $this->user = $participant->user;
        $this->enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
            'points' => 300,
        ]);
    }

    public function test_user_can_play_and_win_under_with_guaranteed_threshold(): void
    {
        // under_threshold 101 ensures random_int(1, 100) is always < 101
        $this->game->update([
            'payout_config' => [
                'under_threshold' => 101,
                'over_threshold' => 52,
                'exact_number' => 50,
                'under_multiplier' => 2.5,
                'over_multiplier' => 2.0,
                'exact_multiplier' => 20.0,
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('arcade.high-low.play', [
                'edition' => $this->edition->id,
                'wager' => 50,
                'bet_type' => 'under',
            ]));

        $response->assertOk()
            ->assertJsonStructure([
                'result',
                'won',
                'payout',
                'multiplier',
                'balance',
            ])
            ->assertJson([
                'won' => true,
                'payout' => 125, // floor(50 * 2.5)
                'multiplier' => 2.5,
                'balance' => 375, // 300 - 50 + 125
            ]);

        $this->assertEquals(375, $this->enrollment->fresh()->points);

        $session = ArcadeSession::latest('id')->first();
        $this->assertNotNull($session);
        $this->assertSame('win', $session->outcome);
        $this->assertSame(50, $session->wager);
        $this->assertSame(125, $session->payout);
        $this->assertSame('under', $session->server_state['bet_type']);
        $this->assertIsInt($session->server_state['rolled']);
    }

    public function test_user_can_play_and_win_over_with_guaranteed_threshold(): void
    {
        // over_threshold 0 ensures random_int(1, 100) is always > 0
        $this->game->update([
            'payout_config' => [
                'under_threshold' => 48,
                'over_threshold' => 0,
                'exact_number' => 50,
                'under_multiplier' => 2.0,
                'over_multiplier' => 3.0,
                'exact_multiplier' => 20.0,
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('arcade.high-low.play', [
                'edition' => $this->edition->id,
                'wager' => 20,
                'bet_type' => 'over',
            ]));

        $response->assertOk()
            ->assertJson([
                'won' => true,
                'payout' => 60, // 20 * 3.0
                'multiplier' => 3.0,
                'balance' => 340, // 300 - 20 + 60
            ]);

        $session = ArcadeSession::latest('id')->first();
        $this->assertSame('win', $session->outcome);
        $this->assertSame('over', $session->server_state['bet_type']);
    }

    public function test_user_loses_when_roll_misses_threshold(): void
    {
        // under_threshold 1 ensures random_int(1, 100) is never < 1
        $this->game->update([
            'payout_config' => [
                'under_threshold' => 1,
                'over_threshold' => 52,
                'exact_number' => 50,
                'under_multiplier' => 2.0,
                'over_multiplier' => 2.0,
                'exact_multiplier' => 20.0,
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('arcade.high-low.play', [
                'edition' => $this->edition->id,
                'wager' => 40,
                'bet_type' => 'under',
            ]));

        $response->assertOk()
            ->assertJson([
                'won' => false,
                'payout' => 0,
                'multiplier' => 2.0,
                'balance' => 260, // 300 - 40
            ]);

        $this->assertEquals(260, $this->enrollment->fresh()->points);

        $session = ArcadeSession::latest('id')->first();
        $this->assertSame('loss', $session->outcome);
        $this->assertSame(0, $session->payout);
        $this->assertSame(40, $session->wager);
    }

    public function test_user_cannot_play_with_insufficient_points(): void
    {
        $this->enrollment->update(['points' => 15]);

        $response = $this->actingAs($this->user)
            ->postJson(route('arcade.high-low.play', [
                'edition' => $this->edition->id,
                'wager' => 50,
                'bet_type' => 'under',
            ]));

        $response->assertStatus(422);
        $this->assertEquals(15, $this->enrollment->fresh()->points);
    }

    public function test_user_cannot_wager_below_minimum_or_above_maximum(): void
    {
        // min is 10, max is 500
        $responseLow = $this->actingAs($this->user)
            ->postJson(route('arcade.high-low.play', [
                'edition' => $this->edition->id,
                'wager' => 5,
                'bet_type' => 'under',
            ]));
        $responseLow->assertStatus(422);

        $responseHigh = $this->actingAs($this->user)
            ->postJson(route('arcade.high-low.play', [
                'edition' => $this->edition->id,
                'wager' => 600,
                'bet_type' => 'under',
            ]));
        $responseHigh->assertStatus(422);
    }

    public function test_invalid_bet_type_fails_validation(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('arcade.high-low.play', [
                'edition' => $this->edition->id,
                'wager' => 20,
                'bet_type' => 'invalid_type',
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bet_type']);
    }

    public function test_guest_cannot_play(): void
    {
        $response = $this->postJson(route('arcade.high-low.play', [
            'edition' => $this->edition->id,
            'wager' => 20,
            'bet_type' => 'under',
        ]));

        $response->assertStatus(401);
    }

    public function test_user_cannot_play_when_game_is_inactive(): void
    {
        $this->game->update(['is_active' => false]);

        $response = $this->actingAs($this->user)
            ->postJson(route('arcade.high-low.play', [
                'edition' => $this->edition->id,
                'wager' => 20,
                'bet_type' => 'under',
            ]));

        $response->assertStatus(403);
    }

    public function test_winning_over_daily_cap_is_rejected_without_charging(): void
    {
        $this->game->update([
            'daily_win_cap' => 10,
            'payout_config' => [
                'under_threshold' => 101,
                'over_threshold' => 52,
                'exact_number' => 50,
                'under_multiplier' => 2.5,
                'over_multiplier' => 2.0,
                'exact_multiplier' => 20.0,
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('arcade.high-low.play', [
                'edition' => $this->edition->id,
                'wager' => 50,
                'bet_type' => 'under',
            ]));

        $response->assertStatus(422);
        $this->assertSame(300, $this->enrollment->fresh()->points);
        $this->assertSame(0, ArcadeSession::count());
    }

    public function test_session_state_records_audit_trail(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('arcade.high-low.play', [
                'edition' => $this->edition->id,
                'wager' => 25,
                'bet_type' => 'exact',
            ]));

        $response->assertOk();

        $session = ArcadeSession::latest('id')->first();
        $this->assertNotNull($session);
        $this->assertArrayHasKey('rolled', $session->server_state);
        $this->assertArrayHasKey('bet_type', $session->server_state);
        $this->assertGreaterThanOrEqual(1, $session->server_state['rolled']);
        $this->assertLessThanOrEqual(100, $session->server_state['rolled']);
        $this->assertSame('exact', $session->server_state['bet_type']);
        $this->assertNotNull($session->resolved_at);
    }
}
