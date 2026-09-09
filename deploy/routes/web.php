<?php

use App\Http\Controllers\SiteController;
use App\Http\Controllers\User\ForgotPasswordController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\User\ResetPasswordController;
use App\Http\Middleware\MaintenanceMode;
use App\Models\Admin;
use App\Services\FileService;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Artisan;

// Route::get('roles', function () {
//     $admin  = Admin::first();

//     // Create a 'super-admin' role if it doesn't exist
//     Bouncer::allow('super-admin')->everything();

//     // Assign the 'super-admin' role to this admin
//     Bouncer::assign('super-admin')->to($admin);

//     // Optionally, create specific abilities
//     Bouncer::allow($admin)->to('manage-users');
//     Bouncer::allow($admin)->to('view-dashboard');
// });

Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('storage:link');

    return '<h3>✅ Cache cleared, config/routes/views reset, and storage linked successfully.</h3>';
})->name('system.clear')->withoutMiddleware(MaintenanceMode::class);

Route::get('/download-receipt/{id}', [SiteController::class, 'downloadPdf'])->name('download.receipt');

Route::middleware('count_site_visit')->group(function () {
    Route::get('/email/verify', function () {
        return view('user.auth.verify-email');
    })->middleware('auth')->name('verification.notice');

    Route::get('/phone/verify', function () {
        return view('user.auth.verify-phone');
    })->middleware('auth')->name('user.phone.verify.notice');

    Route::post('/phone/verify', [\App\Http\Controllers\User\Auth\PhoneVerificationController::class, 'verify'])
        ->middleware('auth')
        ->name('user.phone.verify.submit');

    Route::post('/phone/resend', [\App\Http\Controllers\User\Auth\PhoneVerificationController::class, 'resend'])
        ->middleware('auth')
        ->name('user.phone.verify.resend');


    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return to_route('user.dashboard')->withSuccess(__('Email verified successfully'));
    })->middleware(['auth', 'signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    Route::controller(\App\Http\Controllers\User\Auth\RegisterController::class)->middleware('guest')->group(function () {
        Route::get('/register', 'register')->name('register');
        Route::post('/register', 'registerStore')->name('register.store');
    });

    Route::controller(\App\Http\Controllers\User\LoginController::class)->group(function () {
        Route::middleware('guest.any')->group(function () {
            Route::get('/login', 'login')->name('login');
            Route::post('/login', 'loginStore')->name('login.store');
            Route::post('/otp-login', 'otpLoginStore')->name('login.otp.store');
            Route::post('/otp-login-check', 'validateOtpAndLogin')->name('login.otp.validate');
        });

        Route::middleware('auth')->group(function () {
            Route::post('/logout', 'logout')->name('logout');
        });
    });


    Route::middleware('guest')->group(function () {
        // Step 1: Show forgot password form (phone input)
        Route::get('/forgot-password', [ForgotPasswordController::class, 'requestForm'])->name('password.request');

        // Step 2: Send OTP to user's phone
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendLink'])->name('password.sendOtp');

        // Step 3: Show OTP input form
        Route::get('/verify-otp', [ForgotPasswordController::class, 'verifyOtpForm'])->name('password.otp.form');

        // Step 4: Verify OTP
        Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verifyOtp');

        // Step 5: Show reset password form (after OTP is verified)
        Route::get('/reset-password', [ResetPasswordController::class, 'showResetForm'])->name('password.reset.form');

        // Step 6: Submit new password
        Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])->name('password.update');
    });

    Route::get('donation-success/{donationId}/{manual?}', [SiteController::class, 'donationSuccess'])->name('donation.success');
    Route::get('/', [SiteController::class, 'home'])->name('home');
    Route::post('/guest-donation', [SiteController::class, 'guestDonate'])->name('guest.donate');
    Route::get('/protected-files/{filename}', function ($filename, FileService $fileService) {
        abort_unless(auth()->check(), 403);
        return $fileService->download($filename);
    })->name('files.protected');

    Route::any('payment/notify/{key}', [PaymentController::class, 'notify'])->name('payment.notify')->withoutMiddleware(VerifyCsrfToken::class);

    Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->name('payment.success');

    Route::get('/change-lang/{lang}', [SiteController::class, 'changeLang'])->name('lang.switch')->withoutMiddleware(MaintenanceMode::class);

    Route::controller(SiteController::class)->name('site.')->group(function () {
        Route::get('/projects', 'projects')->name('projects');
        Route::get('/videos', 'videos')->name('videos');
        Route::get('/blog', 'blogs')->name('blogs');
        Route::get('/blog/{slug}', 'blogDetails')->name('blog.details');
        Route::get('/contact', 'contact')->name('contact');
        Route::get('/gallery', 'gallery')->name('gallery');
        Route::post('/contact-submit', 'contactSubmit')->name('contact.submit');
        Route::get('/{pageSlug}', 'renderPageBySlug')->name('page');
    });
});
