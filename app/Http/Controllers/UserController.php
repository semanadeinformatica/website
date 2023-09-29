<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Slot;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController;

class UserController extends UserProfileController
{
    /**
     * Show the general profile settings screen.
     *
     * @return \Inertia\Response
     */
    public function show(Request $request)
    {
        $this->validateTwoFactorAuthenticationState($request);

        $user = $request->user();

        $edition = $request->edition;

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $tickets = Event::all();
        $slots = Slot::all();

        if ($user->isParticipant()) {
            $currentEnrollment = $user->usertype->enrollments()->where('edition_id', $edition->id)->first(); // we can safely get only the first one because there should only be one.

            if ($currentEnrollment !== null) {
                $joinedEvents = $currentEnrollment->events()->get();

                $tickets = $tickets->map(function ($event) use ($joinedEvents) {
                    $event->joined = $joinedEvents->contains($event);

                    return $event;
                });

                $slots = Slot::all();
            }
        }

        return Inertia::render('Profile/Show', [
            'confirmsTwoFactorAuthentication' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
            'tickets' => $tickets,
            'slots' => $slots,
        ]);
    }
}
