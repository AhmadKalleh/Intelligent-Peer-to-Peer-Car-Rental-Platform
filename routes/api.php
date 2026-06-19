<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Vehicle\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =====================
//     Auth Routes
// =====================

Route::controller(AuthController::class)->group(function () {
    Route::post('/register',     [AuthController::class, 'register']);
    Route::post('/login',        [AuthController::class, 'login']);
    Route::post('/googleLogin',  [AuthController::class, 'googleLogin']);
    Route::post('/verify_code',  [AuthController::class, 'verify_code']);
    Route::post('/resend-code',  [AuthController::class, 'resend_code']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// =====================
//     Protected Routes
// =====================

Route::middleware('auth:sanctum')->group(function () {

    // =====================
    //     Vehicle Routes
    // =====================

    Route::prefix('vehicles')->group(function () {
        Route::get('/',            [VehicleController::class, 'index']);   
        Route::get('/{vehicle}',   [VehicleController::class, 'show']);    
        Route::post('/',           [VehicleController::class, 'store']);   
        Route::put('/{vehicle}',   [VehicleController::class, 'update']); 
        Route::patch('/{vehicle}', [VehicleController::class, 'update']); 
        Route::delete('/{vehicle}',[VehicleController::class, 'destroy']); 
    });

});
