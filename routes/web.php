<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\DonationController;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/donate', [DonationController::class, 'create'])->name('donate.submit');
Route::get('/thank-you', [LandingController::class, 'thankYou'])->name('thank-you');
