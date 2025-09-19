<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeTimeController;
use App\Http\Controllers\YearlyVacationController;
Route::resource('employees', EmployeeController::class);
Route::resource('employee_times', EmployeeTimeController::class);
Route::resource('yearly-vacations', YearlyVacationController::class);
