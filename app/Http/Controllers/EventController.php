<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Inertia\Inertia;

class EventController extends Controller
{
    public function show(Event $event)
    {
        return Inertia::render('Event', [
            'event' => $event->load(['speakers', 'event_day']),
        ]);
    }
}
