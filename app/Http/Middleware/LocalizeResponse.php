<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalizeResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $acceptLanguage = $request->header('Accept-Language');

        foreach (explode(',', $acceptLanguage) as $language) {
            [$language] = explode(';', $language);

            if (in_array($language, ['en-US', 'pt-PT'])) {
                app()->setLocale($language);
                break;
            }
        }

        return $next($request);
    }
}
