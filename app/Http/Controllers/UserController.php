<?php

namespace App\Http\Controllers;

use App\Jobs\RemoveQuestCode;
use App\Models\Company;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Sponsor;
use App\Models\SponsorTier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController;
use League\Flysystem\PathPrefixer;
use ZipArchive;

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
        $tickets = $edition->events()->with('event_day')->whereNull('external_url');
        $participants = null;
        $canViewCv = $request->user()->can('viewCVOf', [$user, $edition]);
        $canViewAll = $request->user()->can('viewAll', [$edition]);

        if ($user->isParticipant()) {
            [$tickets, $slots] = $this->processTicketsAndSlots($user, $edition->id, $tickets, $slots);

            $enrollment = $user->usertype->enrollments()->where('edition_id', $edition->id);
            if ($enrollment->exists()) {
                $points = $enrollment->first()->points;
            }

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
            'allParticipants' => fn () => $this->getAllParticipants($user, $edition),
            'user' => fn () => $user,
            'isStaff' => fn () => $user->isStaff($edition),
            'canViewCV' => fn () => $canViewCv,
            'canViewAll' => fn () => $canViewAll,
            'points' => $points ?? null,
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
            if ($requestUser->cannot('viewProfileOf', [$paramUser, $request->edition])) {
                // If the currently logged in user is not an admin, they can only view their own profile (or other users if they are a company and the user is a participant of that company)

                return redirect()->route('profile.show')->dangerBanner('Não tens acesso a esta página.');
            }

            $user = $paramUser;
        }

        return $user;
    }

    private function getAllParticipants(User $user, Edition $edition) {
        $company = $user->usertype;
        
        if ($company instanceof Company) {
            $sponsor = $company->sponsors()->where('edition_id', $edition->id)->first();

            if ($sponsor !== null && $sponsor->tier->canSeeAll) {
                $participants = Participant::with("user")->whereHas('enrollments', function ($query) use ($edition) {
                    $query->where('edition_id', $edition->id);
                })->get();

                return $participants;
            }
        }

        return [];
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
                ->withCount(['quests as completed_count' => function ($query) use ($currentEnrollment) {
                    $query->whereRelation('enrollments', 'enrollment_id', $currentEnrollment->id);
                }]);

            $tickets = $tickets
                ->addSelect([
                    DB::raw('exists(select * from "enrollment_event" where "enrollment_event"."event_id" = "events"."id" and "enrollment_event"."enrollment_id" = '.$currentEnrollment->id.') as "joined"'),
                    DB::raw('exists(select * from "enrollment_quest" inner join "quests" on "quests"."id" = "enrollment_quest"."quest_id" where "quests"."requirement_type" = \''.Event::class.'\' and "quests"."requirement_id" = "events"."id" and "enrollment_quest"."enrollment_id" = '.$currentEnrollment->id.') as "used"'),
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

    public function generateQuestCode(Request $request)
    {
        // This checks if the user is a participant
        Gate::authorize('participant');

        /** @var Participant */
        $participant = $request->user()->usertype;

        // Generate a random code (100 tries)
        for ($i = 0; $i < 100; $i++) {
            $code = Str::random(10);

            // Check if the code already exists
            if (Participant::where('quest_code', $code)->exists()) {
                continue;
            }

            $participant->quest_code = $code;
            $participant->save();

            RemoveQuestCode::dispatch($participant)->delay(now()->addMinutes(10));

            return redirect()->back();
        }

        return abort(500);
    }

    public function scanQuestCode(Request $request)
    {
        /** @var Edition */
        $edition = $request->edition;

        if ($edition === null) {
            return response('No edition found', 500);
        }

        Gate::allowIf(fn (User $user) => $user->isAdmin() || $user->isStaff($edition) || $user->isCompany());

        /** @var User */
        $user = $request->user();

        if ($user->isAdmin() || $user->isStaff($edition)) {
            if (($eventId = $request->input('event')) !== null) {
                $event = Event::find($eventId);

                if ($event === null) {
                    $quests = $edition->quests()->get();
                } else {
                    $quests = $event->quests()->get();
                }
            } else {
                $quests = $edition->quests()->get();
            }
        } else {
            /** @var Company */
            $company = $user->usertype;

            /** @var Sponsor */
            $sponsor = $company->sponsors()->where('edition_id', $edition->id)->first();

            if ($sponsor === null) {
                return response('No sponsor found', 500);
            }

            $quests = $sponsor->through('stands')->has('quests')->get();
        }

        return Inertia::render('Profile/ScanCode', [
            'quests' => $quests,
        ]);
    }

    public function downloadParticipantCVs(Request $request, User $user)
    {

        /** @var Edition|null */
        $edition = $request->edition;

        if ($edition === null) {
            return response('No edition found', 500);
        }

        if (Gate::allows('downloadCVs', [$user, $edition])) {

            /** @var Company */
            $company = $user->usertype;
        }

        $participant_cv_paths = $company->participants()->get()->map((fn ($participant) => $participant->toArray()))->pluck(['cv_path']);

        $zip = new ZipArchive;

        $fileName = Str::slug($user->name, '_').'-participants-cvs.zip';

        $prefixer = new PathPrefixer(public_path('storage'));
        if ($zip->open(public_path($fileName), ZipArchive::CREATE) === true) {

            // loop the files result
            foreach ($participant_cv_paths as $key => $file_path) {

                if ($file_path === null) {
                    continue;
                }

                $path = $prefixer->prefixPath($file_path);

                if (! file_exists($path)) {
                    continue;
                }

                $relativeNameInZipFile = basename($path);
                if (! $zip->addFile($path, $relativeNameInZipFile)) {
                    return response()->bannerDanger('Error a gerar o zip.');
                }
            }

            $zipPath = $zip->filename;

            if ($zip->close()) {
                return response()->download($zipPath)->deleteFileAfterSend();
            }
        }

        // Download the generated zip
        return response()->bannerDanger('Error a gerar o zip.');
    }
}
