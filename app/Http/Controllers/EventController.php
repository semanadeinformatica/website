<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    public function show(Request $request, Event $event)
    {
        $user = $request->user();

        // TODO: inject enrollment instead of edition
        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $isEnrolled = $user->usertype->enrollments()->where('edition_id', $edition->id)->exists(); // we can safely get only the first one because there should only be one.

        $canJoin = $isEnrolled && $user->can('join', $event);

        $hasJoined = $event->enrollments()->where('participant_id', $user->usertype_id)->exists();

        return Inertia::render('Event', [
            'event' => $event->load(['users', 'event_day', 'enrollments']),
            'isEnrolled' => $isEnrolled,
            'hasJoined' => $hasJoined,
            'canJoin' => $canJoin,
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
            return redirect()->back()->dangerBanner('Não podes inscrever-te neste evento');
        }

        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $currentEnrollment = $user->usertype->enrollments()->where('edition_id', $edition->id)->first(); // we can safely get only the first one because there should only be one.

        if ($currentEnrollment === null) {
            return redirect()->route('home')->dangerBanner('Não estás inscrito nesta edição!');
        }

        $currentEnrollment->events()->attach($event);

        return redirect()->route('profile.show')->banner('Inscrição realizada com sucesso!');
    }
}
