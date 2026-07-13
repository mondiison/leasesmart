<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\Auth\TokenController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\MaintenanceRequestController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\TenancyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('tokens', [TokenController::class, 'store'])
        ->middleware('throttle:auth-api')
        ->name('api.v1.tokens.store');

    Route::middleware(['auth:sanctum', 'active', 'throttle:api'])->group(function () {
        Route::delete('tokens/current', [TokenController::class, 'destroy'])->name('api.v1.tokens.destroy');
        Route::get('account', AccountController::class)->name('api.v1.account.show');
        Route::get('tenancies', TenancyController::class)->name('api.v1.tenancies.index');
        Route::get('invoices', InvoiceController::class)->name('api.v1.invoices.index');
        Route::get('payments', PaymentController::class)->name('api.v1.payments.index');
        Route::get('maintenance-requests', MaintenanceRequestController::class)->name('api.v1.maintenance.index');
    });
});
