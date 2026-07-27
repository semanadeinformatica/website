<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\InjectEditionIntoRequest;
use App\Http\Middleware\LogRequestIPs;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend([
            InjectEditionIntoRequest::class,
            LogRequestIPs::class,
        ]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo('/');
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $DEVELOPMENT = ['maintenance', 'local', 'testing'];
        $ERROR_PAGES = [500, 404, 403];

        $exceptions->renderable(function (Throwable $e, Request $request) use ($DEVELOPMENT, $ERROR_PAGES) {
            if (app()->environment($DEVELOPMENT)) {
                return null;
            }

            $status = $e instanceof HttpException
                ? $e->getStatusCode()
                : ($e instanceof TokenMismatchException ? 419 : 500);

            if (in_array($status, $ERROR_PAGES)) {
                return Inertia::render('Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            if ($status === 503) {
                Log::info('Rendering "Coming soon..." page');

                return Inertia::render('Maintenance')
                    ->toResponse($request)
                    ->setStatusCode(503);
            }

            if ($status === 419) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }
        });
    })->create();
