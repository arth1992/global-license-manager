<?php

use App\Http\Controllers\Api\V1\LicenseApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/handshake', [LicenseApiController::class, 'handshake']);
    Route::post('/verify', [LicenseApiController::class, 'verify']);
});
