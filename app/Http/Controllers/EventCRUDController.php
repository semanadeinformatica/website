<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Event;

class EventCRUDController extends CRUDController
{
    protected string $model = Event::class;

    protected string $view = 'Event';

    protected array $rules = [
        'name' => 'required|string',
        'date_start' => 'required|date',
        'date_end' => 'required|date|after:date_start',
        'topic' => 'required|string',
        'capacity' => 'nullable|numeric|integer',
        'edition_id' => 'required|exists:editions,id',
    ];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
        ];
    }
}
