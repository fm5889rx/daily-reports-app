<?php

use App\Http\Controllers\Api\DailyReportApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the application and assigned to the "api"
| middleware group.
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/daily-reports', [DailyReportApiController::class, 'index']);
    Route::post('/daily-reports', [DailyReportApiController::class, 'store']);
    Route::get('/daily-reports/{dailyReport}', [DailyReportApiController::class, 'show']);
    Route::put('/daily-reports/{dailyReport}', [DailyReportApiController::class, 'update']);
    Route::delete('/daily-reports/{dailyReport}', [DailyReportApiController::class, 'destroy']);
});
