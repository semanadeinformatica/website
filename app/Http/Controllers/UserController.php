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
        $tickets = Event::all();
        $slots = Slot::all();

        return Inertia::render('Profile/Show', [
            'confirmsTwoFactorAuthentication' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
            'tickets' => $tickets,
            'slots' => $slots,
        ]);
    }
}
