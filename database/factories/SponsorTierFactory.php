<?php

namespace Database\Factories;

use App\Models\Edition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SponsorTier>
 */
class SponsorTierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'color' => fake()->safeHexColor(),
            'rank' => fake()->randomNumber(),
            'canSeeCV' => fake()->boolean(),
            'canSeeLinkedin' => fake()->boolean(),
            'edition_id' => Edition::factory(),
        ];
    }
}
