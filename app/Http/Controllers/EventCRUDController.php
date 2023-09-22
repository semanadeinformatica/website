<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventDay;

class EventCRUDController extends CRUDController
{
    protected string $model = Event::class;

    protected string $view = 'Event';

    protected array $rules = [
        'name' => 'required|string',
        'time_start' => 'required|date_format:"H:i"',
        'time_end' => 'required|date_format:"H:i"|after:time_start',
        'description' => 'required|string',
        'room' => 'required|string',
        'topic' => 'required|string',
        'capacity' => 'nullable|numeric|integer',
        'event_day_id' => 'required|exists:event_days,id',
    ];

    protected array $search = ['name', 'topic', 'capacity', 'room'];

    protected function with(): array
    {
        return [
            'event_days' => EventDay::all(),
        ];
    }
}
