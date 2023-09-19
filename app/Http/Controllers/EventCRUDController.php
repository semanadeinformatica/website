<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventDay;

class EventCRUDController extends CRUDController
{
    protected string $model = Event::class;

    protected string $view = 'Event';

    protected array $validationRules = [
        'name' => 'required|string',
        'time_start' => 'required|date_format:G\:i',
        'time_end' => 'required|date_format:G\:i|after:time_start',
        'topic' => 'required|string',
        'capacity' => 'nullable|numeric|integer',
        'event_day_id' => 'required|exists:event_days,id',
    ];

    protected array $search = ['name', 'topic', 'capacity'];

    protected function with(): array
    {
        return [
            'event_days' => EventDay::all(),
        ];
    }
}
