<?php

namespace App\Actions\Jetstream;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        $student = $user->whereHasMorph(
            'usertype',
            Student::class,
            function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })->get()->first();
        if ($student) {
            $student->deleteCV();
        }
        $user->deleteProfilePhoto();
        $user->tokens->each->delete();
        $user->delete();
    }
}
