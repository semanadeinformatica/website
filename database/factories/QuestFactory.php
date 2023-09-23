<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Edition;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quest>
 */
class QuestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement(['COMPANY', 'TALK', 'WORKSHOP', 'MILESTONE', 'TEAMBUILDING']),
            'edition_id' => Edition::factory(),
            'requirement_type' => fake()->randomElement([Company::class, Event::class]),
            'requirement_id' => fn (array $attributes) => $attributes['requirement_type']::factory(),
        ];
    }
}
