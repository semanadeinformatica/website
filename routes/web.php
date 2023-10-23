<?php

use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\CompetitionCRUDController;
use App\Http\Controllers\CompetitionTeamCRUDController;
use App\Http\Controllers\CVController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentCRUDController;
use App\Http\Controllers\EditionCRUDController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\EnrollmentCRUDController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventCRUDController;
use App\Http\Controllers\EventDayCRUDController;
use App\Http\Controllers\EventTypeCRUDController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductCRUDController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\QuestController;
use App\Http\Controllers\QuestCRUDController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SlotCRUDController;
use App\Http\Controllers\SponsorCRUDController;
use App\Http\Controllers\SponsorTierCRUDController;
use App\Http\Controllers\StaffCRUDController;
use App\Http\Controllers\StandCRUDController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserCRUDController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'show'])->name('home');

Route::get('/team', [DepartmentController::class, 'show'])->name('team');

Route::get('/program', [ProgramController::class, 'show'])->name('program');

Route::prefix('/shop')->name('shop')->group(function () {
    Route::get('/', [ShopController::class, 'show'])->name('.show');
    Route::post('/{product}/buy', [ShopController::class, 'buyProduct'])->name('.product.buy');
});

Route::prefix('/competition')->name('competition')->group(function () {
    Route::get('/{competition:slug}', [CompetitionController::class, 'show'])->name('.show');
});

Route::prefix('/event')->name('event')->group(function () {

    Route::redirect('/', '/program');

    Route::prefix('/{event}')->group(function () {
        Route::get('/', [EventController::class, 'show'])->name('.show');
        Route::put('/join', [EventController::class, 'join'])->name('.join');
        Route::put('/leave', [EventController::class, 'leave'])->name('.leave');
    });
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(
    function () {
        Route::put('/enroll', [EnrollmentController::class, 'store'])
            ->name('enroll');

        Route::prefix('quest')->group(function () {
            Route::post('/{quest}/give', [QuestController::class, 'give'])
                ->name('quest.give');
        });

        Route::prefix('admin')
            ->middleware('can:admin')
            ->name('admin.')
            ->group(function () {
                Route::resources([
                    '/editions' => EditionCRUDController::class,
                    '/events' => EventCRUDController::class,
                    '/products' => ProductCRUDController::class,
                    '/quests' => QuestCRUDController::class,
                    '/users' => UserCRUDController::class,
                    '/sponsors' => SponsorCRUDController::class,
                    '/staff' => StaffCRUDController::class,
                    '/departments' => DepartmentCRUDController::class,
                    '/stands' => StandCRUDController::class,
                    '/eventDays' => EventDayCRUDController::class,
                    '/competitions' => CompetitionCRUDController::class,
                    '/competitionTeams' => CompetitionTeamCRUDController::class,
                    '/slots' => SlotCRUDController::class,
                    '/enrollments' => EnrollmentCRUDController::class,
                    '/eventTypes' => EventTypeCRUDController::class,
                    '/sponsorTiers' => SponsorTierCRUDController::class,
                ]);

                Route::name('index')->get('/', function () {
                    return Inertia::render('Admin');
                });
            });

        Route::prefix('user')->group(function () {
            Route::get('/profile', [UserController::class, 'show'])
                ->name('profile.show');

            Route::prefix('/{user}')->whereNumber('user')->controller(UserController::class)->group(function () {
                Route::get('/profile', 'show')->name('user.profile');
            });

            Route::get('/scan-code', [UserController::class, 'scanQuestCode'])
                ->name('user.scan-code');

            Route::prefix('cv')->group(function () {
                Route::delete('/', [CVController::class, 'destroy'])->name('current-user-cv.destroy');
                Route::put('/', [CVController::class, 'update'])->name('current-user-cv.update');
                Route::get('/download', [FileController::class, 'download'])->name('file.download');
            });

            Route::get('/profile/edit', [UserController::class, 'edit'])
                ->name('profile.edit');

            Route::post('/generate-quest-code', [UserController::class, 'generateQuestCode'])
                ->name('generate-quest-code');
        });
    }
);
