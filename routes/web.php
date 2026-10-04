<?php

use App\Http\Controllers\SiteController;
use App\Http\Controllers\User\ForgotPasswordController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\Admin\DonationController;
use App\Http\Controllers\Admin\DonorController;
use App\Http\Controllers\User\ResetPasswordController;
use App\Http\Middleware\MaintenanceMode;
use App\Models\Admin;
use App\Models\AdminLogin;
use App\Models\AdminNotification;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserLogin;
use App\Services\FileService;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\SiteVisit;
use Illuminate\Support\Facades\Artisan;

Route::middleware('admin')->group(function () {
    Route::get('sync', function () {
        $visits = SiteVisit::whereNot('ip', '127.0.0.1')->limit(1)->whereNull('country')->select('id', 'ip')->get();

        foreach($visits as $vv) {
            $ip = $vv->ip;

            $u = "https://ipinfo.io/" . $ip . "/json";
            $response = Http::get($u);
            $json = $response->json();

            $vv->country = $json['country'];
            $vv->region = $json['region'];
            $vv->save();
        }
    });

    Route::get('/clear', function () {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('storage:link');

    })->name('system.clear')->withoutMiddleware(MaintenanceMode::class);
});

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
    Route::get('donation-failed/{donationId}', [SiteController::class, 'donationFailed'])->name('donation.failed');
    Route::get('donation-cancelled/{donationId}', [SiteController::class, 'donationCancelled'])->name('donation.cancelled');
    Route::get('/', [SiteController::class, 'home'])->name('home');
    Route::post('/guest-donation', [SiteController::class, 'guestDonate'])->name('guest.donate');
    Route::get('/protected-files/{filename}', function ($filename, FileService $fileService) {
        abort_unless(auth()->check(), 403);
        return $fileService->download($filename);
    })->name('files.protected');

    Route::any('payment/notify/{key}', [PaymentController::class, 'notify'])->name('payment.notify')->withoutMiddleware(VerifyCsrfToken::class);

    Route::get('/payment/success', [PaymentController::class, 'paymentSuccess'])->middleware('auth')->name('payment.success');

    Route::get('/change-lang/{lang}', [SiteController::class, 'changeLang'])->name('lang.switch')->withoutMiddleware(MaintenanceMode::class);

    Route::controller(SiteController::class)->name('site.')->group(function () {
        Route::get('/donate', 'donate')->name('donate');
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


// donors live report auto (admin-only: these expose donor PII/exports)

Route::middleware('admin')->group(function () {
    Route::get('/donors/live/report', [App\Http\Controllers\Admin\DonorController::class, 'live_report'])->name('donor.live.report');

    Route::get('/donors/live/report/download', [App\Http\Controllers\Admin\DonorController::class, 'live_report_download'])->name('donor.live.report.download');

    //month of donation amount by pdf

    Route::get('/donations/download/monthly', [App\Http\Controllers\Admin\DonationController::class, 'downloadMonthlyPdf'])->name('admin.donation.download.monthly');
    Route::get('/donations/download/monthly-excel', [DonationController::class, 'downloadMonthlyExcel'])->name('admin.donation.download.excel');

    Route::get('/donors/download/max', [App\Http\Controllers\Admin\DonorController::class, 'downloadMaxDonors'])->name('admin.donor.download.max.donor');
});



// approved automation route
Route::post('/receive-sms-automation', [App\Http\Controllers\Admin\DonationController::class, 'autoApproveApi']);
