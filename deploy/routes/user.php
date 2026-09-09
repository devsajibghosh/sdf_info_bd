<?php

use App\Helpers\RoutesHelper;
use Illuminate\Support\Facades\Route;

RoutesHelper::registerUserRoutes(function () {
    Route::controller('UserController')->middleware('verified')->group(function () {
        Route::get('/profile/complete', 'profileComplete')->name('profile_data');
        Route::post('/profile/complete/save', 'saveCompleteProfile')->name('save_profile_data');
    });

    Route::middleware(['profile.complete'])->group(function () {
        Route::controller('UserController')->middleware('verified')->group(function () {
            Route::get('/logout-donor-user', 'logoutDonorUser')->name('donor.logout');
            
            Route::get('/dashboard', 'dashboard')->name('dashboard');
            Route::get('/donor-profile', 'donorProfile')->name('donor.profile');
            Route::post('/donor-profile', 'donorProfileSubmit')->name('donor.profile.submit');

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
