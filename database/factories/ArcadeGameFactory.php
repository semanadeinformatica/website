<?php

namespace Database\Factories;

use App\Models\ArcadeGame;
use App\Models\Edition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArcadeGame>
 */
class ArcadeGameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'edition_id' => Edition::factory(),
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'is_active' => true,
            'is_pvp' => false,
            'min_wager' => 10,
            'max_wager' => 500,
            'daily_win_cap' => null,
            'payout_config' => [],
        ];
    }
}
