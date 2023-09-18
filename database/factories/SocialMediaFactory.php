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
            'facebook' => fake()->boolean() ? fake()->userName() : null,
            'github' => fake()->boolean() ? fake()->userName() : null,
            'instagram' => fake()->boolean() ? fake()->userName() : null,
            'linkedin' => fake()->boolean() ? fake()->userName() : null,
            'twitter' => fake()->boolean() ? fake()->userName() : null,
            'website' => fake()->boolean() ? fake()->url() : null,
        ];
    }
}
