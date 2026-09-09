<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GuestAnyGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    protected $guards = ['web', 'donor']; // set your guards

    public function handle(Request $request, Closure $next)
    {
        foreach ($this->guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect('/'); // or wherever you want
            }
        }

        return $next($request);
    }
}
