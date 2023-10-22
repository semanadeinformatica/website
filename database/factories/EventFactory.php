<?php

namespace Database\Factories;

use App\Models\EventDay;
use App\Models\EventType;
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
            'time_start' => fake()->time(),
            'time_end' => fake()->time(),
            'description' => fake()->text($maxNbChars = 500),
            'location' => fake()->words(1, true),
            'external_url' => fake()->boolean(10) ? 'https://'.fake()->domainName() : null,
            'capacity' => fake()->optional()->numberBetween(10, 100),
            'event_day_id' => EventDay::factory(),
            'event_type_id' => EventType::factory(),
        ];
    }
}
