<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UserController extends Controller
{
    public function getParticipants(Company $company)
    {
        return $company->participants();
    }

    public function show(Request $request, User $user)
    {
        $edition = $request->input('edition');

        return Inertia::render('Profile/Show', [
            'user' => $user->load('usertype.socialMedia')->toArray(),
            'participants' => $user->usertype_type === Company::class ? $this->getParticipants($user->usertype) : null,
            'canCV' => Gate::allows('view_CV', [$user, $edition]),
        ]);
    }
}
