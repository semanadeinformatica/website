<?php

namespace App\Providers;

use App\Maintenance\EnvMaintenanceMode;
use App\Models\ArcadeGame;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\Quest;
use App\Models\Stand;
use App\Models\User;
use App\Policies\ArcadePolicy;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\MaintenanceModeManager;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend(
            MaintenanceModeManager::class,
            function (MaintenanceModeManager $manager) {
                $manager->extend('env', function (Container $container) {
                    return new EnvMaintenanceMode(
                        $container->make(Application::class)
                    );
                });

                return $manager;
            }
        );
    }

    public function boot(): void
    {
        $this->configureUrls();
        $this->configureGates();
    }

    protected function configureUrls(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://') || ! $this->app->environment(['local', 'testing'])) {
            URL::forceScheme('https');
        }
    }

    protected function configureGates(): void
    {
        Gate::policy(ArcadeGame::class, ArcadePolicy::class);

        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('participant', fn (User $user) => $user->isParticipant());
        Gate::define('company', fn (User $user) => $user->isCompany());
        Gate::define('speaker', fn (User $user) => $user->isSpeaker());
        Gate::define('staff', fn (User $user, Edition $edition) => $user->isStaff($edition));

        Gate::define('enroll', fn (?User $user, Edition $edition) => (
            $user === null || (
                $user->isParticipant() &&
                $user->usertype->enrollments()->where('edition_id', $edition->id)->doesntExist()
            )
        ));

        Gate::define('join', fn (User $user, Event $event) => (
            $user->isParticipant() &&
            $event->enrollments()->where('participant_id', $user->usertype_id)->doesntExist() &&
            (
                $event->capacity === null ||
                $event->enrollments()->count() < $event->capacity
            )
        ));

        Gate::define('leave', fn (User $user, Event $event) => (
            $user->isParticipant() &&
            $event->enrollments()->where('participant_id', $user->usertype_id)->exists()
        ));

        Gate::define('give', fn (User $user, Quest $quest, Enrollment $enrollment) => (
            $enrollment->quests()->where('quest_id', $quest->id)->doesntExist() &&
            (
                (
                    (
                        $user->isAdmin() ||
                        $user->isStaff($enrollment->edition)
                    ) &&
                    (
                        (
                            $quest->requirement_type === Event::class &&
                            $enrollment->events()->where('event_id', $quest->requirement_id)->exists()
                        ) ||
                        $quest->requirement_type === Stand::class ||
                        $quest->requirement_type === null
                    )
                ) ||
                (
                    $user->isCompany() &&
                    $quest->requirement_type === Stand::class &&
                    $quest->requirement->sponsor->company->is($user->usertype)
                )
            )
        ));

        Gate::define('viewProfileOf', fn (User $user, User $profile_user, Edition $edition) => (
            $user->isAdmin() ||
            $user->isStaff($edition) ||
            $user->is($profile_user) ||
            (
                $user->isCompany() &&
                $profile_user->isParticipant() &&
                $user->usertype->participants()->exists($profile_user)
            ) ||
            (
                $profile_user->isParticipant() &&
                $enrollment = $profile_user->usertype->enrollments()->where('edition_id', $edition->id)->first() &&
                $enrollment->quests()->whereHasMorph('requirement', Stand::class, fn ($query) => $query->whereRelation('sponsor.company', 'id', $user->usertype_id))->exists()
            )
        ));
    }
}
