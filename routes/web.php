<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkersController;
use App\Http\Controllers\WorkScheduleController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [WorkersController::class, 'index']);
// Route::get('/schedule/{startDate}/{endDate}/{id}', [WorkScheduleController::class, 'index'])->name('schedule');
Route::get('/schedule/{id}', [WorkScheduleController::class, 'index']);
