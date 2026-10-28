<?php

namespace Database\Factories;

use App\Models\ArcadeGame;
use App\Models\ArcadeSession;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArcadeSession>
 */
class ArcadeSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'arcade_game_id' => ArcadeGame::factory(),
            'challenger_enrollment_id' => null,
            'wager' => 50,
            'outcome' => 'pending',
            'payout' => 0,
            'server_state' => null,
            'started_at' => now(),
            'resolved_at' => null,
        ];
    }
}
