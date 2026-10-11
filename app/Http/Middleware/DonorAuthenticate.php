<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Donors log in with phone + OTP on the separate `donor` guard, so the
 * default `auth`/`verified` middleware (which only look at the `web` guard)
 * never recognise them. This guards the donor-only area instead.
 */
class DonorAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('donor')->check()) {
            return $request->expectsJson()
                ? response()->json(['message' => __('Please login with your phone number first.')], 401)
                : to_route('login')->withErrors(__('Please login with your phone number first.'));
        }

        return $next($request);
    }
}
