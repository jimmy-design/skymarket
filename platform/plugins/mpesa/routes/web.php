<?php

use Njovu\Mpesa\Http\Controllers\MpesaController;
use Njovu\Mpesa\Http\Controllers\MpesaRegionController;

Route::group(['middleware' => ['web']], function () {
    // Existing Mpesa-specific routes
    Route::prefix('payment/mpesa')->group(function () {
        Route::post('/callback', [MpesaController::class, 'handleCallback'])
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->name('payments.mpesa.callback');

        Route::get('/check-status', [MpesaController::class, 'postCheckout'])
            ->name('check.payment.status');

        Route::get('/error/{token}', action: [MpesaController::class, 'showWaitingPage'])
            ->name('public.checkout.error');

        Route::get('/failed', [MpesaController::class, 'failedCall'])
            ->name('public.checkout.failed');
    });

    // New region-specific routes (admin routes)
    Route::prefix('admin/payment/mpesa/regions')->middleware(['auth:sanctum', 'core'])->group(function () {
        // Place the states route first to avoid conflict with {regionId} route
        // Route::get('/states', [MpesaRegionController::class, 'getStatesByCoId'])
        //     ->name('payments.mpesa.regions.states');

        Route::get('/{regionId}', [MpesaRegionController::class, 'getRegionSettings'])
            ->name('payments.mpesa.regions.get');

        Route::post('/', [MpesaRegionController::class, 'saveRegionSettings'])
            ->name('payments.mpesa.regions.save');

        Route::delete('/{regionId}', [MpesaRegionController::class, 'deleteRegionSettings'])
            ->name('payments.mpesa.regions.delete');
    });
});
Route::get('/states', [MpesaRegionController::class, 'getStatesByCoId'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->name('payments.mpesa.regions.states');