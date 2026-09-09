<?php

namespace App\Providers;

use App\Helpers\SystemHelper;
use App\Models\Admin;
use App\Models\User;
use App\Models\GeneralSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Silber\Bouncer\BouncerFacade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('system', function () {
            return new SystemHelper();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        BouncerFacade::useUserModel(Admin::class);
        
        Config::set('app.url', generalSetting('app_url'));
        Config::set('app.asset_url', generalSetting('app_url'));
        // Config::set('app.debug', (bool) generalSetting('debug_mode'));
        
        
        $settings = GeneralSetting::first();

        View::share('site_title', $settings?->site_title ?? config('app.name'));
        View::share('site_description', $settings?->site_description ?? '');

        
        $this->app->booted(function () {
            // Bangladesh time is the application's authoritative timezone. The
            // admin-configurable general setting wins when present and valid;
            // otherwise fall back to config/app.php's Asia/Dhaka default rather
            // than UTC, so a missing/blank setting never silently shifts every
            // displayed date by 6 hours.
            $timezone = generalSetting('timezone') ?: config('app.timezone', 'Asia/Dhaka');

            if (!in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
                $timezone = 'Asia/Dhaka';
            }

            date_default_timezone_set($timezone);
            config(['app.timezone' => $timezone]);

            Config::set('mail.default', 'smtp');
            
            Config::set('mail.mailers.smtp', array_merge(
                config('mail.mailers.smtp'), 
                [
                    'host'       => generalSetting('mail_host'),
                    'port'       => generalSetting('mail_port'),
                    'username'   => generalSetting('mail_username'),
                    'password'   => generalSetting('mail_password'),
                    'encryption' => generalSetting('mail_encryption'),
                ]
            ));

            Config::set('mail.from.address', generalSetting('mail_from_address'));
            Config::set('mail.from.name', generalSetting('mail_from_name'));
        });

        View::composer('admin.partials.sidebar', function ($view) {
            $view->with('emailUnverifiedUsers', User::emailUnverified()->count());
            $view->with('newUsers', User::new()->count());
        });

        Paginator::useBootstrapFive();
    }
}
