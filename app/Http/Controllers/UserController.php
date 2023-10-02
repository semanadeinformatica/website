<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController;

class UserController extends UserProfileController
{
    public function show(Request $request)
    {
        $this->validateTwoFactorAuthenticationState($request);

        $user = $request->user();

        $edition = $request->edition;

        if ($edition === null) {
            return response('No edition found', 500);
        }

        if ($user->isParticipant()) {
            $currentEnrollment = $user->usertype->enrollments()->where('edition_id', $edition->id)->first(); // we can safely get only the first one because there should only be one.

            $slots = $edition
                ->slots()
                ->withCount(['quests as completed_count' => function ($query) {
                    $query->whereRelation('slots', 'id', DB::raw('slots.id'));
                }])
                ->get();
            $tickets = $edition->events()->get();
        }

        return Inertia::render('Profile/Show', [
            'confirmsTwoFactorAuthentication' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
            'tickets' => $tickets,
            'slots' => $slots,
        ]);
    }

    public function edit(Request $request)
    {
        $this->validateTwoFactorAuthenticationState($request);

        return Inertia::render('Profile/Edit', [
            'confirmsTwoFactorAuthentication' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
            'sessions' => $this->sessions($request)->all(),
        ]);
    }
}
