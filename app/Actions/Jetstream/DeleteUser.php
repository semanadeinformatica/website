<?php

namespace App\Actions\Jetstream;

use App\Models\Student;
use App\Models\User;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        $student = Student::where('user_id', $user->id)->first();
        if ($student) {
            $student->deleteCV();
        }
        $user->deleteProfilePhoto();
        $user->tokens->each->delete();
        $user->delete();
    }
}
