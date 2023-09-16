<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Inertia\Inertia;

class EventController extends Controller
{
    public function show()
    {
        $event = Event::with([
            'speakers',
        ])->first();

        return Inertia::render('Event', [
            'event' => $event,
        ]);
    }
}
