<?php

use App\Http\Controllers\Api\TicketsController;
use App\Http\Controllers\Archive\ApiBuilderController;
use App\Http\Middleware\VerifyArchiveApiKey;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware([VerifyArchiveApiKey::class, 'throttle:120,1'])->group(function () {
    Route::get('/docs', [ApiBuilderController::class, 'docs']);
    Route::get('/tickets', [TicketsController::class, 'index']);
    Route::get('/tickets/options', [TicketsController::class, 'options']);
    Route::get('/tickets/{id}', [TicketsController::class, 'show'])->whereNumber('id');
    Route::get('/attachments/{id}', [TicketsController::class, 'attachment'])->whereNumber('id');
});
