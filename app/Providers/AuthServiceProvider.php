<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Quest;
use App\Models\Stand;
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

        Gate::define('join', fn (User $user, Event $event) => (
            $user->isParticipant() && // user must be a participant
            $event->enrollments()->where('participant_id', $user->usertype_id)->doesntExist() && // user must not have joined the event
            (
                $event->capacity === null || // event might not have a capacity in which case it's always joinable
                $event->enrollments()->count() < $event->capacity // event must not be full
            )
        ));

        Gate::define('give', fn (User $user, Quest $quest, Enrollment $enrollment) => (
            $enrollment->quests()->where('quest_id', $quest->id)->doesntExist() && // participant must not have the quest
            ( // either
                (
                    $user->isAdmin() && // user is admin and either
                    (
                        $quest->requirement_type === Event::class &&
                        $enrollment->events()->where('event_id', $quest->requirement_id)->exists()
                    ) || // requirement is an event the user has joined
                    $quest->requirement_type === Stand::class // or requirement is a stand
                ) ||
                (
                    $user->isCompany() &&
                    $quest->requirement_type === Stand::class &&
                    $quest->requirement->sponsor->company->is($user->usertype)
                ) // or the user is a company and the requirement is a stand from the same company
            )
        ));

        Gate::define('view_profile', fn (User $user, User $profile_user) => (
            $user->id === $profile_user->id || (
                $user->isAdmin() || (
                    $user->isCompany() && $profile_user->isParticipant() && $user->usertype()->participants()->contains($profile_user)
                )
            )
        ));
        Gate::define('view_CV', fn (User $user, User $cv_user, Edition $edition) => (
            $user->id === $cv_user->id || (
                $user->isAdmin() || (
                    $user->isCompany() && $cv_user->isParticipant() && $user->usertype()->participants()->contains($cv_user) &&
                        $edition->sponsors()->where('company_id', $user->usertype_id)->first()->tier !== 'SILVER'
                )
            )
        ));
    }
}
