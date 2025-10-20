<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\EnrollmentProduct;
use App\Models\Event;
use App\Models\Product;
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
        Gate::define('speaker', fn (User $user) => $user->isSpeaker());
        Gate::define('staff', fn (User $user, Edition $edition) => $user->isStaff($edition));

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
                    (
                        $user->isAdmin() || // user is admin
                        $user->isStaff($enrollment->edition) // or user is staff
                    ) && // and either
                    (
                        (
                            $quest->requirement_type === Event::class && // requirement is an event
                            $enrollment->events()->where('event_id', $quest->requirement_id)->exists() // and the user has joined the event
                        ) ||
                        $quest->requirement_type === Stand::class || // or requirement is a stand
                        $quest->requirement_type === null // or there is no requirement
                    )
                ) ||
                (
                    $user->isCompany() &&
                    $quest->requirement_type === Stand::class &&
                    $quest->requirement->sponsor->company->is($user->usertype)
                ) // or the user is a company and the requirement is a stand from the same company
            )
        ));

        Gate::define('viewProfileOf', fn (User $user, User $profile_user, Edition $edition) => (
            $user->isAdmin() || // admins have access to all profiles
            $user->isStaff($edition) || // staff have access to all profiles during the edition they are staff of
            $user->is($profile_user) || // users can view their own profile
            (
                // companies can view profiles of participants who have paid them a visit
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
            ( // admins have access to all CVs except if the other user is also an admin. Admins do not have CVs so this is just a safety measure of sorts
                $user->isAdmin() &&
                ! $cv_user->isAdmin()
            ) ||
            (
                // participants can view their own CV
                $user->isParticipant() && // Do this so that companies do not get the CV area on their profile page
                $user->is($cv_user)
            ) ||
            (
                // companies can view CVs of participants who have paid them a visit if they have they right access
                $user->isCompany() &&
                $edition->sponsors()->where('company_id', $user->usertype_id)->whereRelation('tier', 'canSeeCV', true)->exists() && // If the company does not have the right access, just shortcut the check
                $cv_user->isParticipant() &&
                $user->usertype->participants()->exists($cv_user)
            )
        ));

        Gate::define('viewAll', fn (User $user, Edition $edition) => (
            (
                $user->isCompany() &&
                $user->usertype->sponsors()->where('edition_id', $edition->id)->first()->tier->canSeeAll
            )
        ));

        Gate::define('buy', fn (User $user, Product $product) => (
            $user->isParticipant() && // user must be a participant
            ! $user->isStaff($product->edition) && // user must not be staff
            $user->usertype->enrollments()->where('edition_id', $product->edition->id)->exists() && // user must be enrolled in the current edition
            $user->usertype->enrollments()->where('edition_id', $product->edition->id)->first()->products()->where('product_id', $product->id)->doesntExist() && // user must not have the product
            $user->usertype->enrollments()->where('edition_id', $edition)->where('points', '>=', $product->price)->exists() && // user must have enough points
            $product->stock > 0 // product must be in stock
        ));

        Gate::define('redeem', fn (User $user, Product $product, Enrollment $enrollment) => (
            ($user->isAdmin() || $user->isStaff($product->edition)) && // user must be admin or staff
            EnrollmentProduct::where('enrollment_id', $enrollment->id)->where('product_id', $product->id)->where('redeemed', false)->exists() // product must not have been redeemed
        ));

        Gate::define('downloadCVs', fn (User $user, User $other, Edition $edition) => (
            $user->isAdmin() || // admins have access to all CVs
            $user->isStaff($edition) ||
            $user->is($other)
        ));

        Gate::define('downloadAllCVs', fn (User $user, User $other, Edition $edition) => (
            $user->isAdmin() || // admins have access to all CVs
            $user->isStaff($edition) ||
            (
                $user->isCompany() && 
                $user->usertype->sponsors()->where('edition_id', $edition->id)->first()->tier->canSeeAll
            )
        ));
    }
}
