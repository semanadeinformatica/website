<?php

namespace Database\Factories;

use App\Models\SocialMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Speaker>
 */
class SpeakerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization' => fake()->company(),
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraph(),
            'user_id' => 0,
            'social_media_id' => SocialMedia::factory(),
        ];
    }
}
