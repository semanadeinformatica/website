<?php

namespace Database\Factories;

use App\Models\Edition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Competition>
 */
class CompetitionFactory extends Factory
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
            'date_end' => function ($attributes) {
                return fake()->dateTimeBetween($attributes['date_start']);
            },
            'edition_id' => Edition::factory(),
            'name' => fake()->name(),
            'slug' => fake()->slug(),
            'registration_link' => fake()->url(),
            'regulation' => fake()->realText(2000),
        ];
    }
}
