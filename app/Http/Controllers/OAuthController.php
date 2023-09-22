<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Laravel\Socialite\Facades\Socialite;

use App\Models\User;
use Illuminate\Support\Lottery;

class OAuthController extends Controller {

    public function redirectOAuth($provider) {
        return Socialite::driver($provider)->redirect();
    }

    public function handleOAuthCallback($provider) {
        $oAuthUser = Socialite::driver($provider)->stateless()->user();

        $user = User::firstWhere('email', $oAuthUser->getEmail());

        if (!$user) { // OAuth Sign Up
            $user = (new CreateNewUser)->create([
                'name' => $oAuthUser->getName(),
                'email' => $oAuthUser->getEmail(),
                'password' => 'test_password123',
            ]);
        }

        Auth::login($user, true);

        return redirect()->route('home');
    }
}