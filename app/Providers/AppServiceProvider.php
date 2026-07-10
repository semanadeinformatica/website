<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Actions\Jetstream\DeleteUser;
use App\Maintenance\EnvMaintenanceMode;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\EnrollmentProduct;
use App\Models\Event;
use App\Models\Product;
use App\Models\Quest;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\MaintenanceModeManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Jetstream;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
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

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureUrls();
        $this->configureFortify();
        $this->configureJetstream();
        $this->configureGates();
    }

    protected function configureUrls(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    protected function configureFortify(): void
    {
        Fortify::ignoreRoutes();

        Route::group([
            'namespace' => 'Laravel\Fortify\Http\Controllers',
            'domain' => config('fortify.domain', null),
            'prefix' => config('fortify.prefix'),
        ], function () {
            $this->loadRoutesFrom(base_path('routes/fortify.php'));
        });

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }

    protected function configureJetstream(): void
    {
        Jetstream::ignoreRoutes();

        Route::group([
            'namespace' => 'Laravel\Jetstream\Http\Controllers',
            'domain' => config('jetstream.domain', null),
            'prefix' => config('jetstream.prefix', config('jetstream.path')),
        ], function () {
            $this->loadRoutesFrom(base_path('routes/jetstream.php'));
        });

        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);

        RedirectResponse::macro('banner', function ($message) {
            /** @var \Illuminate\Http\RedirectResponse $this */
            return $this->with('flash', [
                'bannerStyle' => 'success',
                'banner' => $message,
                'bannerId' => Str::uuid(),
            ]);
        });

        RedirectResponse::macro('dangerBanner', function ($message) {
            /** @var \Illuminate\Http\RedirectResponse $this */
            return $this->with('flash', [
                'bannerStyle' => 'danger',
                'banner' => $message,
                'bannerId' => Str::uuid(),
            ]);
        });

        Jetstream::deleteUsersUsing(DeleteUser::class);
    }

    protected function configureGates(): void
    {
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
                $user->isCompany() &&
                $user->usertype->sponsors()->where('edition_id', $edition->id)->first()->tier->canSeeAll
            )
        ));

        Gate::define('viewCVOf', fn (User $user, User $cv_user, Edition $edition) => (
            (
                $user->isAdmin() &&
                ! $cv_user->isAdmin()
            ) ||
            (
                $user->isParticipant() &&
                $user->is($cv_user)
            ) ||
            (
                $user->isCompany() &&
                $edition->sponsors()->where('company_id', $user->usertype_id)->whereRelation('tier', 'canSeeCV', true)->exists() &&
                $cv_user->isParticipant() &&
                $user->usertype->participants()->exists($cv_user)
            )
        ));

        Gate::define('viewAll', fn (User $user, Edition $edition) => (
            (
                $user->isCompany() &&
                $user->usertype?->sponsors()->where('edition_id', $edition->id)->first()?->tier?->canSeeAll
            )
        ));

        Gate::define('buy', fn (User $user, Product $product) => (
            $user->isParticipant() &&
            ! $user->isStaff($product->edition) &&
            $user->usertype->enrollments()->where('edition_id', $product->edition->id)->exists() &&
            $user->usertype->enrollments()->where('edition_id', $product->edition->id)->first()->products()->where('product_id', $product->id)->doesntExist() &&
            $user->usertype->enrollments()->where('points', '>=', $product->price)->exists() &&
            $product->stock > 0
        ));

        Gate::define('redeem', fn (User $user, Product $product, Enrollment $enrollment) => (
            ($user->isAdmin() || $user->isStaff($product->edition)) &&
            EnrollmentProduct::where('enrollment_id', $enrollment->id)->where('product_id', $product->id)->where('redeemed', false)->exists()
        ));

        Gate::define('downloadCVs', fn (User $user, User $other, Edition $edition) => (
            $user->isAdmin() ||
            $user->isStaff($edition) ||
            $user->is($other)
        ));

        Gate::define('downloadAllCVs', fn (User $user, User $other, Edition $edition) => (
            $user->isAdmin() ||
            $user->isStaff($edition) ||
            (
                $user->isCompany() &&
                $user->usertype->sponsors()->where('edition_id', $edition->id)->first()->tier->canSeeAll
            )
        ));
    }
}
