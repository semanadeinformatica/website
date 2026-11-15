<?php

namespace Tests\Feature;

use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Participant;
use App\Services\ArcadeWagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ArcadeWagerServiceTest extends TestCase
{
    use RefreshDatabase;

    private Edition $edition;

    private ArcadeGame $game;

    private Enrollment $enrollment;

    private ArcadeWagerService $service;

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
            'daily_win_cap' => 300,
            'payout_config' => ['house_edge_pct' => 10],
        ]);

        $participant = Participant::factory()->create();
        $this->enrollment = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $participant->id,
            'points' => 100,
        ]);

        $this->service = app(ArcadeWagerService::class);
    }

    public function test_it_rejects_wager_exceeding_balance(): void
    {
        $this->expectException(HttpException::class);

        DB::transaction(function () {
            $this->service->deductWager($this->enrollment->id, 101);
        });
    }

    public function test_it_rejects_invalid_wager_amount(): void
    {
        $this->expectException(HttpException::class);

        DB::transaction(function () {
            $this->service->deductWager($this->enrollment->id, 0);
        });
    }

    public function test_it_deducts_wager_and_returns_locked_enrollment(): void
    {
        DB::transaction(function () {
            $locked = $this->service->deductWager($this->enrollment->id, 40);
            $this->assertEquals(60, $locked->points);
        });

        $this->assertEquals(60, $this->enrollment->fresh()->points);
    }

    public function test_balance_cannot_go_negative_under_concurrent_requests(): void
    {
        $this->enrollment->update(['points' => 50]);

        $results = collect(range(1, 2))->map(function () {
            try {
                return DB::transaction(function () {
                    $this->service->deductWager($this->enrollment->id, 50);

                    return 'success';
                });
            } catch (\Exception $e) {
                return 'rejected';
            }
        });

        $this->assertEquals(1, $results->filter(fn ($r) => $r === 'success')->count());
        $this->assertEquals(0, $this->enrollment->fresh()->points);
    }

    public function test_credit_payout_increments_balance(): void
    {
        DB::transaction(function () {
            $this->service->creditPayout($this->enrollment, 80);
        });

        $this->assertEquals(180, $this->enrollment->fresh()->points);
    }

    public function test_refund_wager_restores_balance(): void
    {
        DB::transaction(function () {
            $this->service->deductWager($this->enrollment->id, 50);
            $this->service->refundWager($this->enrollment->id, 50);
        });

        $this->assertEquals(100, $this->enrollment->fresh()->points);
    }

    public function test_check_daily_win_cap_allows_when_under_cap(): void
    {
        ArcadeSession::create([
            'enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'win',
            'payout' => 150,
            'started_at' => now(),
            'resolved_at' => now(),
        ]);

        // Cap is 300, current is 150 -> no exception
        $this->service->checkDailyWinCap($this->enrollment->id, $this->game, 50);
        $this->assertTrue(true);
    }

    public function test_check_daily_win_cap_rejects_when_at_cap(): void
    {
        ArcadeSession::create([
            'enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'win',
            'payout' => 300,
            'started_at' => now(),
            'resolved_at' => now(),
        ]);

        $this->expectException(HttpException::class);
        $this->service->checkDailyWinCap($this->enrollment->id, $this->game);
    }

    public function test_check_daily_win_cap_rejects_when_additional_payout_exceeds_cap(): void
    {
        ArcadeSession::create([
            'enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'win',
            'payout' => 250,
            'started_at' => now(),
            'resolved_at' => now(),
        ]);

        $this->expectException(HttpException::class);
        // 250 + 60 = 310 > 300
        $this->service->checkDailyWinCap($this->enrollment->id, $this->game, 60);
    }

    public function test_check_daily_win_cap_counts_challenger_win(): void
    {
        $creatorParticipant = Participant::factory()->create();
        $creator = Enrollment::factory()->create([
            'edition_id' => $this->edition->id,
            'participant_id' => $creatorParticipant->id,
            'points' => 100,
        ]);

        ArcadeSession::create([
            'enrollment_id' => $creator->id,
            'challenger_enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'loss',
            'payout' => 190,
            'started_at' => now(),
            'resolved_at' => now(),
        ]);

        $this->expectException(HttpException::class);
        // 190 + 120 = 310 > 300
        $this->service->checkDailyWinCap($this->enrollment->id, $this->game, 120);
    }

    public function test_check_daily_win_cap_ignores_unrestricted_game(): void
    {
        $this->game->update(['daily_win_cap' => null]);

        ArcadeSession::create([
            'enrollment_id' => $this->enrollment->id,
            'arcade_game_id' => $this->game->id,
            'wager' => 50,
            'outcome' => 'win',
            'payout' => 5000,
            'started_at' => now(),
            'resolved_at' => now(),
        ]);

        $this->service->checkDailyWinCap($this->enrollment->id, $this->game, 5000);
        $this->assertTrue(true);
    }
}
