<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Inertia\Inertia;

class EventController extends Controller
{
    public function show()
    {
        $event = Event::with([
            'speakers' => [
                'socialMedia',
            ],
        ])->first();

        return Inertia::render('Event', [
            'event' => $event,
        ]);
    }
}
