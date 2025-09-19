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
use App\Http\Controllers\SickLeaveController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\PositionImprovementController;
Route::resource('employees', EmployeeController::class);
Route::resource('employee_times', EmployeeTimeController::class);
Route::resource('yearly-vacations', YearlyVacationController::class);
Route::resource('sick-leaves', SickLeaveController::class);
Route::resource('salary', SalaryController::class);
Route::resource('position-improvements', PositionImprovementController::class);
