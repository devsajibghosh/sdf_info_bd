<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\DonorController;


// Single donor report
Route::post('/donor/live-report', [
    DonorController::class,
    'liveReport'
])->middleware('donor.api');


// All donors report
Route::post('/donor/all-report', [
    DonorController::class,
    'allDonorsReport'
])->middleware('donor.api');