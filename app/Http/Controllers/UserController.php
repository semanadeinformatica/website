<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Edition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController;

class UserController extends UserProfileController
{
    public function getParticipants(Company $company)
    {
        return $company->participants();
    }

    public function show(Request $request)
    {
        $this->validateTwoFactorAuthenticationState($request);

        $user = $request->user();

        /** @var Edition */
        $edition = $request->edition;

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $slots = $edition->slots();
        $tickets = $edition->events();

        if ($user->isParticipant()) {

            /** @var Enrollment|null */
            $currentEnrollment = $user->usertype->enrollments()->where('edition_id', $edition->id)->first(); // we can safely get only the first one because there should only be one.

            if ($currentEnrollment) {
                $slots = $slots
                    ->withCount(['quests as completed_count' => function ($query) {
                        $query->whereRelation('slots', 'id', DB::raw('slots.id'));
                    }]);

                $tickets = $tickets
                    ->addSelect([
                        DB::raw('exists(select * from "enrollment_event" where "enrollment_event"."event_id" = "events"."id" and "enrollment_event"."enrollment_id" = '.$currentEnrollment->id.') as "joined"'),
                        DB::raw('"events".*'),
                    ]);
            }
        }

        return Inertia::render('Profile/Show', [
            'tickets' => $tickets->get(),
            'slots' => $slots->get(),
            'user' => $user->load('usertype.socialMedia')->toArray(),
            'participants' => $user->usertype_type === Company::class ? $this->getParticipants($user->usertype) : null,
            'canCV' => Gate::allows('view_CV', $user),
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
