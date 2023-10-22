<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Edition;
use App\Models\Event;
use App\Models\Quest;
use App\Models\Stand;

class QuestCRUDController extends CRUDController
{
    protected string $model = Quest::class;

    protected string $view = 'Quest';

    protected array $rules = [
        'name' => 'required|string',
        'category' => 'required|string|in:company,talk,workshop,milestone,teambuiling',
        'requirement' => ['sometimes', 'regex:/^((stand|event|general);[0-9]+)$/'],
        'edition_id' => 'required|exists:editions,id',
    ];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
            'stands' => Stand::all(),
            'events' => Event::all(),
        ];
    }

    protected function created(array $new): ?array
    {
        $requirement = explode(';', $new['requirement']);

        $requirement_type = match ($requirement[0]) {
            'stand' => Stand::class,
            'event' => Event::class,
            default => null,
        };

        if ($requirement_type !== null) {
            $requirement_id = $requirement[1];
        } else {
            $requirement_id = null;
        }

        return [
            'name' => $new['name'],
            'category' => strtoupper($new['category']),
            'requirement_type' => $requirement_type,
            'requirement_id' => $requirement_id,
            'edition_id' => $new['edition_id'],
        ];
    }

    protected function updated($old, array $new): ?array
    {
        $requirement = explode(';', $new['requirement']);

        $requirement_type = match ($requirement[0]) {
            'company' => Company::class,
            default => null,
        };

        if ($requirement_type !== null) {
            $requirement_id = $requirement[1];
        } else {
            $requirement_id = null;
        }

        return [
            'name' => $new['name'],
            'category' => strtoupper($new['category']),
            'requirement_type' => $requirement_type,
            'requirement_id' => $requirement_id,
            'edition_id' => $new['edition_id'],
        ];
    }
}
