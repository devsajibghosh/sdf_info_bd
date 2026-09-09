<?php

use App\Helpers\RoutesHelper;
use App\Http\Middleware\AuthBoth;
use App\Http\Middleware\MaintenanceMode;
use App\Http\Middleware\ProfileCompleteMiddleware;
use App\Http\Middleware\UserStatusMiddleware;
use App\Http\Middleware\RedirectIfAdmin;
use App\Http\Middleware\RedirectIfNotAdmin;
use App\Http\Middleware\SiteVisitMiddleware;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: fn() => RoutesHelper::setupRoutes()
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            'donor.api' => \App\Http\Middleware\CheckDonorApiKey::class,
            'guest.any' => \App\Http\Middleware\GuestAnyGuard::class,
            'auth.both'        => AuthBoth::class,
            'auth'             => Authenticate::class,
            'admin.guest'      => RedirectIfAdmin::class,
            'admin'            => RedirectIfNotAdmin::class,
            'maintenace'       => MaintenanceMode::class,
            'profile.complete' => ProfileCompleteMiddleware::class,
            'user.status'      => UserStatusMiddleware::class,
            'count_site_visit' => SiteVisitMiddleware::class,
            'verified.custom'  => \App\Http\Middleware\EnsurePhoneOrEmailIsVerified::class,
                
        ]);

        // validateCsrfTokens() matches URI patterns (Str::is), not route names.
        // Both routes below already carry their own ->withoutMiddleware(VerifyCsrfToken::class)
        // or (for receive-sms-automation) happen to share their URI with the route
        // name; these entries are kept as an explicit, URI-pattern-correct backstop
        // in case that per-route exemption is ever removed.
        $middleware->validateCsrfTokens(except: [
            'payment/notify/*',
            'receive-sms-automation',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // PHP's own post_max_size check runs before Laravel's router/controllers
        // even see the request (Illuminate\Http\Middleware\ValidatePostSize, part
        // of the default global middleware stack), so an over-limit upload never
        // reaches our validation rules. Without this, it would surface as a raw,
        // untranslated, unbranded error page instead of the app's normal
        // success/error flash (session('error'), see admin.partials.alerts) or
        // JSON error shape used everywhere else.
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            $message = __('The uploaded file is too large for the server to accept. Please upload a smaller file and try again.');

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return back()->withError($message)->withInput($request->except(['image', 'password', 'password_confirmation']));
        });
    })->create();
