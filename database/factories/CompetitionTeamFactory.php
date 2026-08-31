<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\CompetitionTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompetitionTeam>
 */
class CompetitionTeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'points' => fake()->numberBetween(0),
            'competition_id' => Competition::factory(),
        ];
    }
}
