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

        $isStaff = true;
        if (Gate::denies('admin') && Gate::denies('staff', [$edition])) {
            $isStaff = false; // this works because the check for staff has the same logic as the check for admin on the frontend
            $enrollments = null;
        }

        return Inertia::render('Event', [
            'event' => $event->load(['users', 'event_day', 'type']),
            'enrollments' => $enrollments,
            'enrollmentCount' => $enrollmentCount,
            'isParticipant' => $isParticipant,
            'isEnrolled' => $isEnrolled,
            'hasJoined' => $hasJoined,
            'canJoin' => $canJoin,
            'isStaff' => $isStaff,
        ]);
    }

    /**
     * The current user wants to join the given event.
     */
    public function join(Request $request, Event $event)
    {
        /** @var User */
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->can('join', $event)) {
            $editionId = $request->input('edition')?->id ?? $event->event_day?->edition_id;
            $enrollment = $editionId
                ? $user->usertype->enrollments()->where('edition_id', $editionId)->first()
                : $user->usertype->enrollments()->latest()->first();

            if ($enrollment) {
                $enrollment->events()->syncWithoutDetaching([$event->id]);
            }
        }

        return redirect()->back();
    }

    /**
     * The current user wants to leave the given event.
     */
    public function leave(Request $request, Event $event)
    {
        /** @var User */
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->can('leave', $event)) {
            $editionId = $request->input('edition')?->id ?? $event->event_day?->edition_id;
            $enrollment = $editionId
                ? $user->usertype->enrollments()->where('edition_id', $editionId)->first()
                : $user->usertype->enrollments()->latest()->first();

            if ($enrollment) {
                $enrollment->events()->detach($event->id);
            }
        }

        return redirect()->back();
    }
}
