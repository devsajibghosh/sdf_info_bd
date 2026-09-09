<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProfileCompleteMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->guard('donor')->check()) return $next($request);
        
        if (auth()?->user()?->pc == 0 && !auth()?->user()?->donator) {
            return to_route('user.profile_data')->withError(__('Please complete your profile'));
        }

        return $next($request);
    }
}
