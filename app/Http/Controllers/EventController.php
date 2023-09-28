<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EventController extends Controller
{
    public function show(Event $event)
    {
        return Inertia::render('Event', [
            'event' => $event->load(['users', 'event_day']),
        ]);
    }

    /**
     * The current user wants to join the given event.
     */
    public function join(Request $request, Event $event)
    {
        Gate::authorize('join', $event);

        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $user = $request->user();

        $currentEnrollment = $user->usertype->enrollments()->where('edition_id', $edition->id)->first(); // we can safely get only the first one because there should only be one.

        if ($currentEnrollment === null) {
            return response('No enrollment found', 500);
        }

        $currentEnrollment->events()->attach($event);

        return redirect()->route('profile.show')->banner("Inscrição no evento '$event->name' realizada com sucesso!");
    }
}
