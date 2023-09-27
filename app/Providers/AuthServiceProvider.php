<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Edition;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('participant', fn (User $user) => $user->isParticipant());
        Gate::define('company', fn (User $user) => $user->isCompany());

        Gate::define('enroll', fn (?User $user, Edition $edition) => (
            $user === null || (
                $user->isParticipant() &&
                $user->usertype->enrollments()->where('edition_id', $edition->id)->doesntExist()
            )
        ));

        Gate::define('view_profile', fn (User $user, User $profile_user) => (
            $user->id === $profile_user->id || (
                $user->isAdmin() || (
                    $user->isCompany() && $profile_user->isParticipant() && $user->participants()->contains($profile_user)
                )
            )
        ));
    }
}
