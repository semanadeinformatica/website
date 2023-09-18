<?php

namespace Database\Factories;

use App\Models\CompetitionTeam;
use App\Models\Edition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompetitionInfo>
 */
class CompetitionInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'theme' => fake()->realText(),
            'date_start' => fake()->date(),
            'date_end' => fake()->date(),
            'edition_id' => Edition::factory(),
            'first_place' => CompetitionTeam::factory(),
            'second_place' => CompetitionTeam::factory(),
            'third_place' => CompetitionTeam::factory(),
        ];
    }
}
