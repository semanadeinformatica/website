<?php

namespace App\Policies;

use App\Models\ArcadeGame;
use App\Models\User;

class ArcadePolicy
{
    /**
     * Determine whether the user can play the arcade game.
     * Must be: authenticated + is a Participant + enrolled in this edition + game is_active
     */
    public function play(User $user, ArcadeGame $game): bool
    {
        if (! $user->isParticipant()) {
            return false;
        }

        $edition = request()->input('edition') ?? $game->edition;
        if (! $edition || $edition->id !== $game->edition_id) {
            return false;
        }

        $enrollment = $user->usertype->enrollments()
            ->where('edition_id', $edition->id)
            ->first();

        return $enrollment !== null && (bool) $game->is_active;
    }
}
