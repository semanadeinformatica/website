<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EventController extends Controller
{
    public function show(Request $request, Event $event)
    {
        /** @var User */
        $user = $request->user();

        // TODO: inject enrollment instead of edition
        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $isParticipant = $user && $user->isParticipant();

        $isEnrolled = $isParticipant && $user->usertype->enrollments()->where('edition_id', $edition->id)->exists();

        $hasJoined = $isParticipant && $event->enrollments()->where('participant_id', $user->usertype_id)->exists();
        $canJoin = $isEnrolled && ! $hasJoined && $user->can('join', $event);

        $enrollments = $event->enrollments()->with(['participant' => ['user']])->get();
        $enrollmentCount = $enrollments->count();

        if (Gate::denies('admin') && Gate::denies('staff', [$edition])) {
            $enrollments = null;
        }

        return Inertia::render('Event', [
            'event' => $event->load(['users', 'event_day']),
            'enrollments' => $enrollments,
            'enrollmentCount' => $enrollmentCount,
            'isParticipant' => $isParticipant,
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

    /**
     * The current user wants to leave the given event if enrolled.
     */
    public function leave(Request $request, Event $event)
    {
        $user = $request->user();

        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $currentEnrollment = $user->usertype->enrollments()->where('edition_id', $edition->id)->first(); // we can safely get only the first one because there should only be one.

        if ($currentEnrollment === null) {
            return redirect()->route('home')->dangerBanner('Não estás inscrito nesta edição!');
        }

        $currentEnrollment->events()->detach($event);

        return redirect()->route('profile.show')->banner('Inscrição cancelada com sucesso!');
    }
}
