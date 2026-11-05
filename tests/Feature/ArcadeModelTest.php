<?php

namespace Tests\Feature;

use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
use App\Models\Edition;
use App\Models\Enrollment;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArcadeModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_arcade_game_can_be_created_and_accessed(): void
    {
        $edition = Edition::factory()->create();

        $game = ArcadeGame::create([
            'edition_id' => $edition->id,
            'slug' => 'coin-flip',
            'name' => 'Coin Flip',
            'is_active' => true,
            'is_pvp' => true,
            'min_wager' => 10,
            'max_wager' => 500,
            'daily_win_cap' => 1000,
            'payout_config' => ['house_edge_pct' => 10],
        ]);

        $this->assertDatabaseHas('arcade_games', [
            'id' => $game->id,
            'slug' => 'coin-flip',
            'is_active' => true,
            'is_pvp' => true,
        ]);

        $this->assertIsArray($game->payout_config);
        $this->assertEquals(10, $game->payout_config['house_edge_pct']);
        $this->assertTrue($game->edition->is($edition));
        $this->assertEquals(['name' => 'Coin Flip', 'slug' => 'coin-flip'], $game->toSearchableArray());
    }

    public function test_arcade_game_enforces_unique_edition_and_slug(): void
    {
        $edition = Edition::factory()->create();

        ArcadeGame::factory()->create([
            'edition_id' => $edition->id,
            'slug' => 'coin-flip',
        ]);

        $this->expectException(QueryException::class);

        ArcadeGame::factory()->create([
            'edition_id' => $edition->id,
            'slug' => 'coin-flip',
        ]);
    }

    public function test_arcade_sessions_relationships_and_scopes(): void
    {
        $edition = Edition::factory()->create();
        $enrollment = Enrollment::factory()->create(['edition_id' => $edition->id]);
        $challenger = Enrollment::factory()->create(['edition_id' => $edition->id]);

        $game = ArcadeGame::factory()->create([
            'edition_id' => $edition->id,
            'slug' => 'coin-flip',
            'is_pvp' => true,
        ]);

        $session1 = ArcadeSession::create([
            'enrollment_id' => $enrollment->id,
            'arcade_game_id' => $game->id,
            'challenger_enrollment_id' => $challenger->id,
            'wager' => 50,
            'outcome' => 'pending',
            'payout' => 0,
            'server_state' => ['secret' => 'hashed_value'],
            'started_at' => now(),
        ]);

        $session2 = ArcadeSession::create([
            'enrollment_id' => $enrollment->id,
            'arcade_game_id' => $game->id,
            'wager' => 100,
            'outcome' => 'win',
            'payout' => 190,
            'started_at' => now(),
            'resolved_at' => now(),
        ]);

        // Relationships
        $this->assertTrue($session1->enrollment->is($enrollment));
        $this->assertTrue($session1->challenger->is($challenger));
        $this->assertTrue($session1->game->is($game));
        $this->assertIsArray($session1->server_state);
        $this->assertEquals('hashed_value', $session1->server_state['secret']);

        // Reverse relationships
        $this->assertCount(2, $game->sessions);
        $this->assertCount(2, $enrollment->arcadeSessions);
        $this->assertCount(1, $edition->arcadeGames);

        // Scopes
        $pending = ArcadeSession::pending()->get();
        $this->assertCount(1, $pending);
        $this->assertEquals($session1->id, $pending->first()->id);

        $forEnrollment = ArcadeSession::forEnrollment($enrollment->id)->get();
        $this->assertCount(2, $forEnrollment);

        $wonTodayPayout = ArcadeSession::wonToday($enrollment->id, $game->id)->sum('payout');
        $this->assertEquals(190, $wonTodayPayout);
    }

    public function test_challenger_enrollment_nulls_on_delete(): void
    {
        $edition = Edition::factory()->create();
        $enrollment = Enrollment::factory()->create(['edition_id' => $edition->id]);
        $challenger = Enrollment::factory()->create(['edition_id' => $edition->id]);
        $game = ArcadeGame::factory()->create(['edition_id' => $edition->id]);

        $session = ArcadeSession::create([
            'enrollment_id' => $enrollment->id,
            'arcade_game_id' => $game->id,
            'challenger_enrollment_id' => $challenger->id,
            'wager' => 50,
            'outcome' => 'pending',
        ]);

        $challenger->delete();

        $session->refresh();
        $this->assertNull($session->challenger_enrollment_id);
    }
}
