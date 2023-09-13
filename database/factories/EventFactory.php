<?php

namespace Database\Factories;

use App\Models\Edition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
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
            'topic' => fake()->sentence(),
            'date_start' => fake()->dateTimeBetween('now', '+1 year'),
            'date_end' => fn(array $attributes) => fake()->dateTimeBetween($attributes['date_start'], '+1 year'),
            'capacity' => fake()->optional()->numberBetween(10, 100),
            'edition_id' => Edition::factory(),
        ];
    }
}
