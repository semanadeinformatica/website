<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Company;
use App\Models\Participant;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserCRUDController extends CRUDController
{
    protected string $model = User::class;

    protected string $view = 'User';

    protected array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email',
        'type' => 'required|in:participant,company,admin,speaker',
        'title' => 'sometimes|nullable|string',
        'description' => 'sometimes|nullable|string',
        'organization' => 'sometimes|nullable|string',
        'public_email' => 'sometimes|nullable|string|email',
        'facebook' => 'sometimes|nullable|string|url:https|regex:/^https:\/\/(www.)?facebook\.com\/[a-zA-Z0-9.]{5,}\/?$/',
        'github' => ['sometimes', 'nullable', 'string', 'url:https', 'regex:/^https:\/\/(www.)?github\.com\/[a-z\d](?:[a-z\d]|-(?=[a-z\d])){1,38}\/?$/'],
        'instagram' => 'sometimes|nullable|string|url:https|regex:/^https:\/\/(www.)?instagram\.com\/[a-zA-Z0-9_.]{1,30}\/?$/',
        'linkedin' => ['sometimes', 'nullable', 'string', 'url:https', 'regex:/^https:\/\/(www\.)?linkedin\.com\/(in|company)\/[\p{L}0-9-]{3,100}\/?$/u'],
        'twitter' => ['sometimes', 'nullable', 'string', 'url:https', 'regex:/^https:\/\/(www.)?(twitter|x)\.com\/[a-zA-Z0-9_]{4,15}\/?$/'],
        'website' => 'sometimes|nullable|string|url:https',
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
            'speaker' => Speaker::class,
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
            'company' => [
                'description' => $new['description'],
            ],
            'speaker' => [
                'title' => $new['title'],
                'description' => $new['description'],
                'organization' => $new['organization'],
            ],
            default => [],
        });
        $usertype = $type::create($usertypeProps);
        $user->usertype()->associate($usertype);
        $user->save();

        if ($new['type'] !== 'admin') {
            $user->usertype->socialMedia()->updateOrCreate([], [
                'email' => $new['public_email'],
                'facebook' => $new['facebook'],
                'github' => $new['github'],
                'instagram' => $new['instagram'],
                'linkedin' => $new['linkedin'],
                'twitter' => $new['twitter'],
                'website' => $new['website'],
            ]);
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
            'speaker' => Speaker::class,
            'admin' => Admin::class,
        };

        if ($old->usertype_type !== $type) {
            // This should never happen
            throw new \Exception('Cannot change user type');
        }

        $usertypeProps = match ($new['type']) {
            'company' => [
                'description' => $new['description'],
            ],
            'speaker' => [
                'title' => $new['title'],
                'description' => $new['description'],
                'organization' => $new['organization'],
            ],
            default => [],
        };
        $old->usertype->update($usertypeProps);

        if ($new['type'] !== 'admin') {
            $old->usertype->socialMedia()->updateOrCreate([], [
                'email' => $new['public_email'],
                'facebook' => $new['facebook'],
                'github' => $new['github'],
                'instagram' => $new['instagram'],
                'linkedin' => $new['linkedin'],
                'twitter' => $new['twitter'],
                'website' => $new['website'],
            ]);
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
