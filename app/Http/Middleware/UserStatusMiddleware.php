<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserStatusMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->guard('donor')->check()) return $next($request);
        
        if (auth()?->user()?->status == 0) { 
            return back()->withError(__('Your account is not active, please wait for admin approval'));
        }

        return $next($request);
    }
}
