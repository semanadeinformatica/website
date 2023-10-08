<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Edition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController;

class UserController extends UserProfileController
{
    public function getParticipants(Company $company, Edition $edition)
    {
        return $company->participants()->where('sponsors.edition_id', $edition->id)->where('enrollments.edition_id', $edition->id)->get();
    }

    public function show(Request $request)
    {
        $this->validateTwoFactorAuthenticationState($request);

        /** @var Edition */
        $edition = $request->edition;

        if ($edition === null) {
            return response('No edition found', 500);
        }

        /** @var User|null */
        $user = $request->user();

        /** @var User|null */
        $requestUserId = $request->route('user');
        if ($requestUserId !== null) {

            // If we are trying to view another user's profile, we need to validate that we can do so.

            $user = User::find($requestUserId);
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

        if (! $user->isAdmin()) {
            $user = $user->load('usertype.socialMedia');
        }

        return Inertia::render('Profile/Show', [
            'tickets' => $tickets->get(),
            'slots' => $slots->get(),
            'user' => $user,
            'participants' => $user->isCompany() ? $this->getParticipants($user->usertype, $edition) : [],
            'canCV' => Gate::allows('view_CV', [$user, $edition]),
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
