<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('enroll', $request->edition);

        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $user = $request->user();

        $user->usertype->enrollments()->create([
            'edition_id' => $edition->id,
        ]);
        Log::info('User {user} enrolled in edition {edition}', [
            'user' => $user->name,
            'edition' => $edition->name,
        ]);

        return redirect()->route('profile.show')->banner('Inscrição realizada com sucesso!');
    }
}
