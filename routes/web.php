<?php

use Illuminate\Support\Facades\Route;
use yousefkadah\FreePbx\Http\Controllers\DashboardController;

Route::prefix('freepbx')->middleware(['web'])->group(function () {
    Route::get('/dashboard', function () {
        return view('freepbx::dashboard');
    })->name('freepbx.dashboard');
});
