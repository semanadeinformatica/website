<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    public function show(User $user)
    {
        return Inertia::render('Profile/Show', [
            'user' => $user->load('usertype.socialMedia')->toArray(),
        ]);
    }
}
