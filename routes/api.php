<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AllocationController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DailyBudgetController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.api')->group(function () {
    Route::apiResource('transactions', TransactionController::class);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/accounts', [AccountController::class, 'index']);

    Route::apiResource('allocations', AllocationController::class);

    Route::get('/daily-budget', [DailyBudgetController::class, 'show']);
    Route::put('/budget', [DailyBudgetController::class, 'update']);
});
