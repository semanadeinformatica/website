<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Edition;
use App\Models\SponsorTier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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

        /** @var Edition */
        $edition = $request->edition;

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $value = $this->getUserToShow($request);
        if ($value instanceof RedirectResponse) {
            return $value;
        } elseif ($value instanceof User) {
            $user = $value;
        } else {
            return response('Invalid user', 500);
        }

        $slots = $edition->slots();
        $tickets = $edition->events()->with('event_day');
        $participants = null;
        $canViewCv = $request->user()->can('viewCVOf', [$user, $edition]);

        if ($user->isParticipant()) {
            [$tickets, $slots] = $this->processTicketsAndSlots($user, $edition->id, $tickets, $slots);

            if ($request->user()->isCompany()) {
                // We fall in this category if we are a company and we are viewing the profile of a visitor of our company

                // TODO:
            }

        } elseif ($user->isCompany()) {
            $participants = $this->getParticipants($user, $edition);
        }

        if (! $user->isAdmin()) {
            $user = $user->load('usertype.socialMedia');
        }

        return Inertia::render('Profile/Show', [
            'tickets' => fn () => $tickets->get(),
            'slots' => fn () => $slots->get(),
            'participants' => fn () => $participants?->get() ?? [],
            'user' => fn () => $user,
            'canViewCV' => fn () => $canViewCv,
        ]);
    }

    private function getUserToShow(Request $request): RedirectResponse|User
    {
        /** @var User|null */
        $requestUser = $request->user();

        if ($requestUser === null) {
            // We need to at least be authenticated to view user profiles

            return redirect()->route('profile.show')->dangerBanner('Não tens acesso a esta página.');
        }

        $user = $requestUser;

        /** @var User|null */
        $paramUser = User::find($request->route('user'));

        if ($paramUser !== null) {
            if ($requestUser->cannot('viewProfileOf', $paramUser)) {
                // If the currently logged in user is not an admin, they can only view their own profile (or other users if they are a company and the user is a participant of that company)

                return redirect()->route('profile.show')->dangerBanner('Não tens acesso a esta página.');
            }

            $user = $paramUser;
        }

        return $user;
    }

    private function getParticipants(User $user, Edition $edition)
    {
        /** @var Company $company */
        $company = $user->usertype;

        $participants = $company
            ->participants()
            ->where('sponsors.edition_id', $edition->id)
            ->where('enrollments.edition_id', $edition->id)
            ->with('user');

        /** @var Sponsor|null */
        $sponsor = $company->sponsors()->where('edition_id', $edition->id)->first();

        if ($sponsor !== null) {

            /** @var SponsorTier */
            $sponsorTier = $sponsor->tier;

            $participants = $participants
                ->addSelect([
                    DB::raw('participants.*'),
                    DB::raw(($sponsorTier->canSeeCV ? '1' : '0').' as can_see_cv'),
                    DB::raw(($sponsorTier->canSeeLinkedin ? '1' : '0').' as can_see_linkedin'),
                ]);
        }

        return $participants;
    }

    private function processTicketsAndSlots(User $user, int $editionId, $tickets, $slots)
    {
        /** @var Enrollment|null */
        $currentEnrollment = $user->usertype->enrollments()->where('edition_id', $editionId)->first(); // we can safely get only the first one because there should only be one.

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

        return [$tickets, $slots];
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
