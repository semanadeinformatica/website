<?php

namespace App\Actions\Fortify;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Participant;
use App\Models\SocialMedia;
use App\Models\Speaker;
use App\Models\User;
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

        // TODO: split this in functions
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'cv' => ['nullable', File::types(['pdf'])->max('4mb')],
            'type' => 'sometimes|nullable|in:participant,company,admin,speaker',
            'title' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'organization' => 'sometimes|nullable|string',
            'social_media.email' => 'sometimes|nullable|string|email',
            'social_media.facebook' => 'sometimes|nullable|string|url:https|regex:/^https:\/\/facebook.com\/\w+$/',
            'social_media.github' => 'sometimes|nullable|string|url:https|regex:/^https:\/\/github.com\/\w+$/',
            'social_media.instagram' => 'sometimes|nullable|string|url:https|regex:/^https:\/\/instagram.com\/\w+$/',
            'social_media.linkedin' => ['bail', 'sometimes', 'nullable', Rule::excludeIf(fn () => $user->isCompany()), 'string', 'url:https', 'regex:/^https:\/\/linkedin.com\/in/\w+$/'],
            'social_media.linkedin' => ['bail', 'sometimes', 'nullable', Rule::excludeIf(fn () => ! $user->isCompany()), 'string', 'url:https', 'regex:/^https:\/\/linkedin.com\/company/\w+$/'],
            'social_media.twitter' => 'sometimes|nullable|string|url:https|regex:/^https:\/\/twitter.com\/\w+$/',
            'social_media.website' => 'sometimes|nullable|string|url:https',
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($user->isParticipant() && isset($input['cv'])) {
            $user->usertype->updateCV($input['cv']);
        }

        // TODO: make email and name optional on updates
        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }

        if (isset($input['type'])) {

            $type = match ($input['type']) {
                'participant' => Participant::class,
                'company' => Company::class,
                'speaker' => Speaker::class,
                'admin' => Admin::class,
            };

            if ($user->usertype_type !== $type) {
                // This should never happen
                throw new \Exception('Cannot change user type');
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

            if ($input['type'] === 'admin') {
                return;
            }

            if (isset($input['social_media'])) {
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
