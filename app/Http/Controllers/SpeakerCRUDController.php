<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\SocialMedia;
use App\Models\Speaker;
use Illuminate\Support\Facades\DB;

class SpeakerCRUDController extends CRUDController
{
    protected array $rules = [
        'name' => 'required|string',
        'title' => 'nullable|string',
        'description' => 'nullable|string',
        'organization' => 'nullable|string',
        'social_media.email' => 'sometimes|nullable|string|email',
        'social_media.facebook' => 'sometimes|nullable|string',
        'social_media.github' => 'sometimes|nullable|string',
        'social_media.instagram' => 'sometimes|nullable|string',
        'social_media.linkedin' => 'sometimes|nullable|string',
        'social_media.twitter' => 'sometimes|nullable|string',
        'social_media.website' => 'sometimes|nullable|string|url',
        'event_id' => 'required|exists:events,id',
        'photo' => 'nullable|mimes:jpg,jpeg,png|max:1024',
    ];

    protected array $search = ['name', 'title', 'description', 'organization'];

    protected function with(): array
    {
        return [
            'events' => Event::all(),
        ];
    }

    protected function created(array $new): ?array
    {
        DB::beginTransaction();
        $speaker = Speaker::create([
            'name' => $new['name'],
            'title' => $new['title'],
            'description' => $new['description'],
            'organization' => $new['organization'],
            'event_id' => $new['event_id'],
        ]);

        if (isset($new['social_media'])) {
            $socialMedia = SocialMedia::create($new['social_media']);
            $speaker->socialMedia()->associate($socialMedia);
            $speaker->save();
        }
        DB::commit();

        if (isset($new['photo'])) {
            $speaker->updateProfilePhoto($new['photo']);
        }

        return null;
    }

    protected function updated($old, array $new): ?array
    {
        if (isset($new['social_media'])) {
            $socialMedia = $old->socialMedia;
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
            'title' => $new['title'],
            'description' => $new['description'],
            'organization' => $new['organization'],
            'event_id' => $new['event_id'],
            'social_media_id' => $socialMedia?->id ?? null,
        ];
    }
}
