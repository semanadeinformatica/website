<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Edition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class CompetitionController extends Controller
{
    /**
     * Renders the given competition.
     *
     * @return \Illuminate\Http\RedirectResponse
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

        return Inertia::render('Competition', [
            'competition' => $competition,
            'isParticipant' => $isParticipant,
            'isEnrolled' => $isEnrolled,
        ]);
    }
}
