<?php

namespace App\Actions\Jetstream;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {

        Log::alert('Deleting account for {user_name}', ['user_name' => $user->name]);

        if ($user->isParticipant()) {
            $user->usertype->deleteCv();
        }

        $user->deleteProfilePhoto();
        $user->tokens->each->delete();
        $user->delete();
    }
}
