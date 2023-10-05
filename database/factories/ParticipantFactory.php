<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\SocialMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (Participant $participant) {
            if ($participant->user_id !== 0) {
                return;
            }

            $participant->user()->associate(User::factory()->create([
                'usertype_id' => -1,
                'usertype_type' => Participant::class,
            ]));
        })->afterCreating(function (Participant $participant) {
            $participant->user->usertype_id = $participant->id;
            $participant->user->save();
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
            'user_id' => 0,
            'social_media_id' => SocialMedia::factory(),
        ];
    }
}
