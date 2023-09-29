<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    public function show(Event $event)
    {
        return Inertia::render('Event', [
            'event' => $event->load(['users', 'event_day', 'enrollments']),
        ]);
    }

    /**
     * The current user wants to join the given event.
     */
    public function join(Request $request, Event $event)
    {
        $user = $request->user();

        // FIXME: when attempting to join multiple times in a row, only the first attempt triggers the banner.
        if ($user->cannot('join', $event)) {
            return redirect()->back()->dangerBanner('Não pode inscrever-se neste evento.');
        }

        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $currentEnrollment = $user->usertype->enrollments()->where('edition_id', $edition->id)->first(); // we can safely get only the first one because there should only be one.

        if ($currentEnrollment === null) {
            return redirect()->route('home')->dangerBanner('Não está atualmente inscrito em nenhuma edição. Deve fazê-lo antes de tentar inscrever-se num evento.');
        }

        $currentEnrollment->events()->attach($event);

        return redirect()->back()->banner("Inscrição no evento '$event->name' realizada com sucesso!");
    }
}
