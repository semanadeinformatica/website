<?php

namespace App\Http\Middleware;

use App\Models\Edition;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectEditionIntoRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentEdition = Edition::where('year', '=', now()->year)->get()->first();

        if ($currentEdition === null) {
            // There still isn't an edition corresponding to this year, fetch the latest one.
            $currentEdition = Edition::orderByDesc('year')->get()->first();
        }

        $request->merge(['edition' => $currentEdition]);

        return $next($request);
    }
}
