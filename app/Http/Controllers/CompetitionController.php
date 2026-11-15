<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Edition;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class CompetitionController extends Controller
{
    /**
     * Renders the given competition.
     *
     * @return RedirectResponse
     */
    public function show(Request $request, Competition $competition)
    {

        /** @var User|null $user */
        $user = $request->user();
        $isParticipant = false;
        $isEnrolled = false;

        if ($user !== null) {
            $isParticipant = $user->isParticipant();

            /** @var Edition|null */
            $edition = $request->edition;

            if ($edition === null) {
                return response('No edition found', 500);
            }

            if ($isParticipant) {
                $isEnrolled = $user->usertype->enrollments()->where('edition_id', $edition->id)->exists();
            }
        }

        $isOver = now()->greaterThan($competition->date_end);

        $competition->load([
            'teams' => fn ($query) => $query->orderBy('points', 'desc')->with([
                'members' => [
                    'user',
                ],
            ]),
        ]);

        $competition->teams->each(function ($team) {
            $team->members->each(function ($member) {
                $member->makeHidden('cv_url');
                $member->user?->makeHidden('email');
                $member->user?->usertype?->makeHidden('cv_url');
            });
        });

        return Inertia::render('Competition', [
            'competition' => $competition,
            'isOver' => $isOver,
            'isParticipant' => $isParticipant,
            'isEnrolled' => $isEnrolled,
            'prizes' => $competition->prizes,
        ]);
    }
}
