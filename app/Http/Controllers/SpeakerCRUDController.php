<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\SocialMedia;
use App\Models\Speaker;
use Illuminate\Support\Facades\DB;

class SpeakerCRUDController extends CRUDController
{
    protected string $model = Speaker::class;

    protected string $view = 'Speaker';

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
    ];

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

        return null;
    }

    protected function updated(array $old, array $new): ?array
    {
        if (isset($new['social_media'])) {
            $socialMedia = SocialMedia::find($old['social_media_id']);
            if ($socialMedia === null) {
                $socialMedia = SocialMedia::create($new['social_media']);
            } else {
                $socialMedia->update($new['social_media']);
            }
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