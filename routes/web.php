<?php

use App\Http\Controllers\DailyReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the application and assigned to the "web"
| middleware group.
|
*/

Route::redirect('/', '/daily-reports');

Route::resource('daily-reports', DailyReportController::class)
    ->parameters(['daily-reports' => 'dailyReport'])
    ->except(['show'])
    ->middleware('auth');
