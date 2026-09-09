<?php

use App\Helpers\RoutesHelper;
use App\Http\Middleware\AuthBoth;
use App\Http\Middleware\MaintenanceMode;
use App\Http\Middleware\ProfileCompleteMiddleware;
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
            'guest.any' => \App\Http\Middleware\GuestAnyGuard::class,
            'auth.both'        => AuthBoth::class,
            'auth'             => Authenticate::class,
            'admin.guest'      => RedirectIfAdmin::class,
            'admin'            => RedirectIfNotAdmin::class,
            'maintenace'       => MaintenanceMode::class,
            'profile.complete' => ProfileCompleteMiddleware::class,
            'count_site_visit' => SiteVisitMiddleware::class,
            'verified.custom'  => \App\Http\Middleware\EnsurePhoneOrEmailIsVerified::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'payment.notify',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
