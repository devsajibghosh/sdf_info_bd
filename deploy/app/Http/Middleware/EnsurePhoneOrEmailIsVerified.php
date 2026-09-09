<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneOrEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $redirectToRoute
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $redirectToRoute = null): Response
    {
        $user = $request->user();

        $method = generalSetting('verification_method');

        if ($method === 'phone' && ! $user->phone_verified_at) {
            return $request->expectsJson()
                ? abort(403, 'Your phone number is not verified.')
                : Redirect::guest(URL::route($redirectToRoute ?: 'user.phone.verify.notice'));
        }

        if (
            $method === 'email' && !$user->email_verified_at
        ) {
            return $request->expectsJson()
                ? abort(403, 'Your email address is not verified.')
                : Redirect::guest(URL::route($redirectToRoute ?: 'verification.notice'));
        }

        return $next($request);
    }

    /**
     * Specify the redirect route for the middleware.
     *
     * @param  string  $route
     * @return string
     */
    public static function redirectTo($route): string
    {
        return static::class . ':' . $route;
    }
}
