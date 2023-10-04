<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialMedia>
 */
class SocialMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->boolean() ? fake()->email() : null,
            'facebook' => fake()->boolean() ? fake()->url() : null,
            'github' => fake()->boolean() ? fake()->url() : null,
            'instagram' => fake()->boolean() ? fake()->url() : null,
            'linkedin' => fake()->boolean() ? fake()->url() : null,
            'twitter' => fake()->boolean() ? fake()->url() : null,
            'website' => fake()->boolean() ? fake()->url() : null,
        ];
    }
}
