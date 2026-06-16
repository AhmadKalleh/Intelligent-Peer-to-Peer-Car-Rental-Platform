<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function ()
{
    Route::post('/register',     [AuthController::class, 'register']);
    Route::post('/login',        [AuthController::class, 'login']);
    Route::post('/googleLogin',  [AuthController::class, 'googleLogin']);
    Route::post('/verify_code',  [AuthController::class, 'verify_code']);
    Route::post('/resend-code',  [AuthController::class, 'resend_code']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

});

Route::middleware('auth:sanctum')->group(function () {
    // Protected routes that require authentication


});
