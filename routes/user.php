<?php

use App\Helpers\RoutesHelper;
use Illuminate\Support\Facades\Route;

RoutesHelper::registerUserRoutes(function () {
    // Donor area: donors log in with phone + OTP on the `donor` guard.
    Route::controller('UserController')->middleware('auth.donor')->group(function () {
        Route::get('/donor-profile', 'donorProfile')->name('donor.profile');
        Route::post('/donor-profile', 'donorProfileSubmit')->name('donor.profile.submit');
        Route::get('/donor-donoations', 'donorDonations')->name('donor.donations');
        Route::post('/logout-donor-user', 'logoutDonorUser')->name('donor.logout');
    });

    Route::middleware('donor.to.profile')->group(function () {
        Route::controller('UserController')->middleware('verified')->group(function () {
            Route::get('/profile/complete', 'profileComplete')->name('profile_data');
            Route::post('/profile/complete/save', 'saveCompleteProfile')->name('save_profile_data');
        });

        Route::middleware(['profile.complete', ])->group(function () {
            Route::controller('UserController')->middleware('verified')->group(function () {
                Route::get('/dashboard', 'dashboard')->name('dashboard');
                Route::get('/notices', 'notices')->name('notices');
                Route::get('/committees', 'committees')->name('committees');
                Route::get('/committees/{id}/details', 'committeeDetail')->name('committees.detail');

                Route::middleware('auth')->group(function() {
                    Route::get('/setting/profile', 'profile')->name('setting.profile');
                    Route::post('/setting/profile', 'saveProfile')->name('setting.profile.save');
                });
            });

            Route::controller('PaymentController')->middleware('auth')->prefix('payment')->name('payment.')->group(function () {
                Route::get('/history', 'paymentHistory')->name('history');
                Route::get('/new', 'newPayment')->name('new');
                Route::post('/', 'paymentInsert')->name('insert');
                Route::get('/download/{trx}', 'downloadPdf')->name('download');
            });
        });
    });
});
