<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');

    Route::patch('/applications/{application}/status', [ApplicationController::class, 'updateStatus'])
        ->name('applications.update-status');

    Route::post('/jobs/{jobListing}/generate-cover-letter', [JobController::class, 'generateCoverLetter'])
        ->name('jobs.generate-cover-letter');

    Route::post('/applications/{application}/interviews', [InterviewController::class, 'store'])
        ->name('interviews.store');

    Route::patch('/interviews/{interview}', [InterviewController::class, 'update'])
        ->name('interviews.update');

    Route::delete('/interviews/{interview}', [InterviewController::class, 'destroy'])
        ->name('interviews.destroy');

    Route::post('/referrals', [ReferralController::class, 'store'])->name('referrals.store');
    Route::patch('/referrals/{referral}', [ReferralController::class, 'update'])->name('referrals.update');
    Route::delete('/referrals/{referral}', [ReferralController::class, 'destroy'])->name('referrals.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
