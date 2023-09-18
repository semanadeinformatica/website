<?php

namespace Database\Factories;

use App\Models\CompetitionInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompetitionTeam>
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
            'competition_id' => CompetitionInfo::factory(),
        ];
    }
}
