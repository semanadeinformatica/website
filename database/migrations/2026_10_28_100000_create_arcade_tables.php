<?php

use App\Models\ArcadeGame;
use App\Models\Edition;
use App\Models\Enrollment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('arcade_games', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Edition::class)->constrained()->cascadeOnDelete();
            $table->string('slug', 64);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_pvp')->default(false);
            $table->unsignedInteger('min_wager')->default(10);
            $table->unsignedInteger('max_wager')->default(500);
            $table->unsignedInteger('daily_win_cap')->nullable();
            // Expected payout_config shapes:
            // coin-flip: {"house_edge_pct": 10}
            // high-low: {"under_threshold": 4, "over_threshold": 10, "under_multiplier": 2.0, "over_multiplier": 2.0, "exact_multiplier": 5.0}
            // bug-sweeper: {"click_multipliers": [1.2, 1.5, 2.0, 3.0, 5.0], "bomb_count": 3}
            // mastermind: {"max_attempts": 5, "attempt_multipliers": {"1": 6.0, "2": 4.0, "3": 2.5, "4": 1.8, "5": 1.5}}
            // binary-blitz: {"question_count": 10, "time_limit_seconds": 60, "win_multiplier": 2.5, "partial_multiplier": 1.2, "daily_win_cap": 500}
            $table->json('payout_config')->default('{}');
            $table->timestamps();
            $table->unique(['edition_id', 'slug']);
        });

        Schema::create('arcade_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Enrollment::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ArcadeGame::class)->constrained()->cascadeOnDelete();
            $table->foreignId('challenger_enrollment_id')
                ->nullable()
                ->constrained('enrollments')
                ->nullOnDelete();
            $table->unsignedInteger('wager');
            // outcome: pending | matched | win | loss | cashout | expired
            $table->string('outcome', 16)->default('pending');
            $table->unsignedInteger('payout')->default(0);
            $table->json('server_state')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arcade_sessions');
        Schema::dropIfExists('arcade_games');
    }
};
