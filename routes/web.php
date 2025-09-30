<?php

use App\Http\Controllers\VacationDateController;
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
    return view('welcome_logo');
});

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\WorkScheduleController;
use App\Http\Controllers\EmployeeTimeController;
use App\Http\Controllers\YearlyVacationController;
use App\Http\Controllers\SickLeaveController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\PositionImprovementController;
Route::resource('employees', EmployeeController::class);
Route::resource('employee_times', EmployeeTimeController::class);
Route::get('employee_times/{employee}/export', [EmployeeTimeController::class, 'exportTimesheet'])->name('employee_times.export');
Route::post('employee_times/import', [EmployeeTimeController::class, 'importExcel'])->name('employee_times.import');
Route::resource('yearly-vacations', YearlyVacationController::class);
Route::resource('sick-leaves', SickLeaveController::class);
Route::resource('salary', SalaryController::class);
Route::resource('position-improvements', PositionImprovementController::class);
Route::resource('vacation-dates', VacationDateController::class);

// Work Schedule routes
Route::get('work-schedule', function () {
    $schedule = \App\Models\WorkSchedule::first();
    return view('work_schedule', compact('schedule'));
})->name('work-schedule.edit');
Route::put('work-schedule', [WorkScheduleController::class, 'update'])->name('work-schedule.update');
