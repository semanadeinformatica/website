<?php

use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\CompetitionCRUDController;
use App\Http\Controllers\CompetitionTeamCRUDController;
use App\Http\Controllers\CVController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentCRUDController;
use App\Http\Controllers\EditionCRUDController;
use App\Http\Controllers\EnrollmentCRUDController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventCRUDController;
use App\Http\Controllers\EventDayCRUDController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ProductCRUDController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\QuestCRUDController;
use App\Http\Controllers\SlotCRUDController;
use App\Http\Controllers\SponsorCRUDController;
use App\Http\Controllers\StaffCRUDController;
use App\Http\Controllers\StandCRUDController;
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

Route::get('/competitions', function () {
    return Inertia::render('Competitions');
})->name('competitions');
Route::prefix('/competition')->name('competition')->group(function () {
    Route::get('/{competition:slug}', [CompetitionController::class, 'show'])->name('.show');
});

Route::prefix('/event')->name('event')->group(function () {

    Route::redirect('/', '/program');

    Route::prefix('{event}')->group(function () {
        Route::get('/', [EventController::class, 'show'])->name('.show');
    });
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(
    function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');

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
                ]);

                Route::name('index')->get('/', function () {
                    return Inertia::render('Admin');
                });
            });

        Route::prefix('user')->group(function () {
            Route::prefix('/{participant}')->whereNumber('participant')->controller(ParticipantController::class)->group(function () {
                Route::get('', 'show')->name('participant.profile');
            });
            Route::prefix('cv')->group(function () {
                Route::delete('/', [CVController::class, 'destroy'])->name('current-user-cv.destroy');
                Route::put('/', [CVController::class, 'update'])->name('current-user-cv.update');
                Route::get('/download', [FileController::class, 'download'])->name('file.download');
            });
        });
    }
);
