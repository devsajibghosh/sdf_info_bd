<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteVisitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = request()->ip();
        $today = now()->toDateString();

        if (!\App\Models\SiteVisit::where('ip', $ip)->where('visit_date', $today)->exists()) {
            \App\Models\SiteVisit::create(['ip' => $ip, 'visit_date' => $today]);
        }

        return $next($request);
    }
}
