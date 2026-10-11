<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Member-only pages (dashboard, notices, payment...) sit behind `verified`,
 * which bounces an OTP-logged-in donor to /email/verify -> /login -> /.
 * Send donors to their own profile page instead.
 */
class RedirectDonorToProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('web')->check() && Auth::guard('donor')->check()) {
            return to_route('user.donor.profile');
        }

        return $next($request);
    }
}
