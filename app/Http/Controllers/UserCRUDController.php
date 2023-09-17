<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Company;
use App\Models\SocialMedia;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserCRUDController extends CRUDController
{
    protected string $model = User::class;

    protected string $view = 'User';

    protected array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email',
        'type' => 'required|in:student,company,admin',
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
            'student' => Student::class,
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
        $usertypeProps = ['user_id' => $user->id];
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
            'student' => Student::class,
            'company' => Company::class,
            'admin' => Admin::class,
        };

        if ($old->usertype_type !== $type) {
            // This should never happen
            throw new \Exception('Cannot change user type');
        }

        if ($new['type'] !== 'admin' && isset($new['social_media'])) {
            $socialMedia = $old->social_media;
            if ($socialMedia === null) {
                $socialMedia = SocialMedia::create($new['social_media']);
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
            'social_media_id' => $socialMedia?->id ?? null,
        ];
    }
}
