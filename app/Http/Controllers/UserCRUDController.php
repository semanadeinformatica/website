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
        'company_tier' => 'exclude_unless:type,company|required_if:type,company|in:platinum,gold,silver',
        'social_media.email' => 'sometimes|nullable|string|email',
        'social_media.facebook' => 'sometimes|nullable|string',
        'social_media.github' => 'sometimes|nullable|string',
        'social_media.instagram' => 'sometimes|nullable|string',
        'social_media.linkedin' => 'sometimes|nullable|string',
        'social_media.twitter' => 'sometimes|nullable|string',
        'social_media.website' => 'sometimes|nullable|string|url',
    ];

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
        $usertypeProps = match ($new['type']) {
            'student' => ['user_id' => $user->id],
            'company' => ['user_id' => $user->id, 'tier' => strtoupper($new['company_tier'])],
            'admin' => ['user_id' => $user->id],
        };
        $usertype = $type::create($usertypeProps);
        $user->usertype()->associate($usertype);
        $user->save();

        if ($new['type'] !== 'admin' && isset($new['social_media'])) {
            $socialMedia = SocialMedia::create($new['social_media']);
            $usertype->socialMedia()->associate($socialMedia);
            $usertype->save();
        }
        DB::commit();

        return null;
    }

    protected function updated(array $old, array $new): ?array
    {
        $type = match ($new['type']) {
            'student' => Student::class,
            'company' => Company::class,
            'admin' => Admin::class,
        };

        if ($old['usertype_type'] !== $type) {
            // This should never happen
            throw new \Exception('Cannot change user type');
        }

        if ($new['type'] !== 'admin' && isset($new['social_media'])) {
            $socialMedia = SocialMedia::find($old['social_media_id']);
            if ($socialMedia === null)
                $socialMedia = SocialMedia::create($new['social_media']);
            else
                $socialMedia->update($new['social_media']);
        }

        return [
            'name' => $new['name'],
            'email' => $new['email'],
            'tier' => $type === Company::class ? strtoupper($new['company_tier']) : null,
            'social_media_id' => $socialMedia?->id ?? null,
        ];
    }
}
