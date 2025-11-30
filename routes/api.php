<?php

use Illuminate\Support\Facades\Route;
use yousefkadah\FreePbx\Http\Controllers\CallController;
use yousefkadah\FreePbx\Http\Controllers\DashboardController;

Route::prefix('api/freepbx')->middleware(['api'])->group(function () {
    // Dashboard endpoints
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/queues', [DashboardController::class, 'queues']);
    Route::get('/dashboard/agents', [DashboardController::class, 'agents']);
    Route::post('/dashboard/clear-cache', [DashboardController::class, 'clearCache']);

    // Call endpoints
    Route::post('/call/click-to-call', [CallController::class, 'clickToCall']);
});
