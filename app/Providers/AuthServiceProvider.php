<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Company;
use App\Models\Participant;
use App\Models\VisitHistory;
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
        Gate::define('admin', fn ($user) => $user->isAdmin());
        Gate::define('participant', fn ($user) => $user->isParticipant());
        Gate::define('company', fn ($user) => $user->isCompany());
        Gate::define('view_participant', fn ($user, Participant $participant) => ($user->id == $participant->user_id) || (($user->usertype_type == Company::class) && VisitHistory::where('company_id', $user->usertype_id)->where('participant_id', $participant->id)->exists()));
    }
}
