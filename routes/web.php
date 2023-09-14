<?php

use App\Http\Controllers\CVController;
use App\Http\Controllers\EditionCRUDController;
use App\Http\Controllers\EventCRUDController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ProductCRUDController;
use App\Http\Controllers\QuestCRUDController;
use App\Http\Controllers\SpeakerCRUDController;
use App\Http\Controllers\UserCRUDController;
use Illuminate\Foundation\Application;
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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
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
                    '/speakers' => SpeakerCRUDController::class,
                    '/users' => UserCRUDController::class,
                ]);
            });

        Route::prefix('user')->group(function () {
            Route::prefix('cv')->group(function () {
                Route::delete('/', [CVController::class, 'destroy'])->name('current-user-cv.destroy');
                Route::get('/download', [FileController::class, 'download'])->name('file.download');
            });
        });
    }
);
