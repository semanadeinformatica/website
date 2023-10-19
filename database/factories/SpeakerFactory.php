<?php

namespace Database\Factories;

use App\Models\SocialMedia;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Speaker>
 */
class SpeakerFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (Speaker $speaker) {
            if ($speaker->user_id !== 0) {
                return;
            }

            $speaker->user()->associate(User::factory()->create([
                'usertype_id' => -1,
                'usertype_type' => Speaker::class,
            ]));
        })->afterCreating(function (Speaker $speaker) {
            $speaker->user->usertype_id = $speaker->id;
            $speaker->user->save();
        });
    }

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
            'display_name' => fake()->optional()->name(),
        ];
    }
}
