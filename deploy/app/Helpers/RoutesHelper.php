<?php

namespace App\Helpers;

use Closure;
use Illuminate\Support\Facades\Route;

class RoutesHelper
{
    /**
     * Controller Namespace for routes
     * @var string
     */
    private static $controllerNamespace = 'App\Http\Controllers\\';

    /**
     * You can change admin routes prefix, default to admin
     * @var string
     */
    private static $adminPrefix = 'admin';

    /**
     * You can change user routes prefix, default to user
     * @var string
     */
    private static $userPrefix = 'user';

    /**
     * Register routes for users, only authencated. Can be passed extra middlewares
     * @param \Closure $callback
     * @param mixed $middlewares
     * @return void
     */
    public static function registerUserRoutes(Closure $callback, $middlewares = [])
    {
        Route::middleware(array_merge(['web'], $middlewares))
            ->group(function () use ($callback) {
                $callback();
            });
    }

    /**
     * Register routes for admin, only authencated. Can be passed extra middlewares
     * @param \Closure $callback
     * @param mixed $middlewares
     * @return void
     */
    public static function registerAdminRoutes(Closure $callback, $middlewares = [])
    {
        Route::middleware(array_merge(['admin'], $middlewares))
            ->group(function () use ($callback) {
                $callback();
            });
    }

    /**
     * Setup user routes
     * @return void
     */
    private static function setupUserRoutes()
    {
        Route::middleware(['web', 'maintenace'])
            ->prefix(self::$userPrefix)
            ->namespace(self::$controllerNamespace . 'User')
            ->name('user.')
            ->group(base_path('routes/user.php'));
    }

    /**
     * Setup admin routes
     * @return void
     */
    private static function setupAdminRoutes()
    {
        Route::middleware(['web'])
            ->prefix(self::$adminPrefix)
            ->namespace(self::$controllerNamespace . 'Admin')
            ->name('admin.')
            ->group(base_path('routes/admin.php'));
    }


    /**
     * Setup admin routes
     * @return void
     */
    public static function setupLivewireAdminRoutes()
    {
        Route::middleware(['web'])
            ->prefix(self::$adminPrefix)
            // ->namespace(self::$controllerNamespace . 'Admin')
            ->name('admin.')
            ->group(base_path('routes/admin2.php'));
    }

    /**
     * Setup routes
     * @return void
     */
    public static function setupRoutes()
    {
        self::setupUserRoutes();

        self::setupAdminRoutes();

        // self::setupLivewireAdminRoutes();

        Route::middleware(['web', 'maintenace'])->group(base_path('routes/web.php'));
    }
}
