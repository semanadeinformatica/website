<?php

namespace App\Actions\Fortify;

use App\Models\Participant;
use App\Models\User;
use App\Models\SocialMedia;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'cv' => ['nullable', File::types(['pdf'])->max('4mb')],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($user->isParticipant() && isset($input['cv'])) {
            $user->usertype->updateCV($input['cv']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }

        $usertypeProps = match ($input['type']) {
            'company' => [
                'description' => $input['description'],
            ],
            'speaker' => [
                'title' => $input['title'],
                'description' => $input['description'],
                'organization' => $input['organization'],
            ],
            default => [],
        };
        $user->usertype->update($usertypeProps);

        if ($input['type'] !== 'admin' && isset($input['social_media'])) {
            $socialMedia = $user->usertype->social_media;
            if ($socialMedia === null) {
                $socialMedia = SocialMedia::create($input['social_media']);
                $user->usertype->socialMedia()->associate($socialMedia);
                $user->usertype->save();
            } else {
                $socialMedia->update($input['social_media']);
            }
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
