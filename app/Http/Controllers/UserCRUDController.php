<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Company;
use App\Models\SocialMedia;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserCRUDController extends CRUDController
{
    protected string $model = User::class;

    protected string $view = 'User';

    protected array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email',
        'type' => 'required|in:participant,company,admin',
        'description' => 'sometimes|nullable|string',
        'social_media.email' => 'sometimes|nullable|string|email',
        'social_media.facebook' => 'sometimes|nullable|string',
        'social_media.github' => 'sometimes|nullable|string',
        'social_media.instagram' => 'sometimes|nullable|string',
        'social_media.linkedin' => 'sometimes|nullable|string',
        'social_media.twitter' => 'sometimes|nullable|string',
        'social_media.website' => 'sometimes|nullable|string|url',
        'photo' => 'nullable|mimes:jpg,jpeg,png|max:1024',
    ];

    protected array $search = ['name', 'email'];

    protected function updateRules($old): array
    {
        return array_merge($this->rules, [
            'email' => 'required|string|email|max:255|unique:users,email,'.$old->id,
        ]);
    }

    protected function created(array $new): ?array
    {
        $type = match ($new['type']) {
            'participant' => Participant::class,
            'company' => Company::class,
            'admin' => Admin::class,
        };

        DB::beginTransaction();
        $user = User::create([
            'name' => $new['name'],
            'email' => $new['email'],
            'password' => '',
            'usertype_id' => '0',
            'usertype_type' => $type,
        ]);
        $usertypeProps = array_merge(['user_id' => $user->id], match ($new['type']) {
            'company' => ['description' => $new['description']],
            default => [],
        });
        $usertype = $type::create($usertypeProps);
        $user->usertype()->associate($usertype);
        $user->save();

        if ($new['type'] !== 'admin' && isset($new['social_media'])) {
            $socialMedia = SocialMedia::create($new['social_media']);
            $usertype->socialMedia()->associate($socialMedia);
            $usertype->save();
        }
        DB::commit();

        if (isset($new['photo'])) {
            $user->updateProfilePhoto($new['photo']);
        }

        return null;
    }

    protected function updated($old, array $new): ?array
    {
        $type = match ($new['type']) {
            'participant' => Participant::class,
            'company' => Company::class,
            'admin' => Admin::class,
        };

        if ($old->usertype_type !== $type) {
            // This should never happen
            throw new \Exception('Cannot change user type');
        }

        $usertypeProps = match ($new['type']) {
            'company' => ['description' => $new['description']],
            default => [],
        };
        $old->usertype->update($usertypeProps);

        if ($new['type'] !== 'admin' && isset($new['social_media'])) {
            $socialMedia = $old->usertype->social_media;
            if ($socialMedia === null) {
                $socialMedia = SocialMedia::create($new['social_media']);
                $old->usertype->socialMedia()->associate($socialMedia);
                $old->usertype->save();
            } else {
                $socialMedia->update($new['social_media']);
            }
        }

        if (isset($new['photo'])) {
            $old->updateProfilePhoto($new['photo']);
        }

        return [
            'name' => $new['name'],
            'email' => $new['email'],
        ];
    }
}
