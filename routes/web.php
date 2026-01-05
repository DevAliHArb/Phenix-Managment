
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
use App\Http\Controllers\EmployeeVacationController;
Route::resource('employees', EmployeeController::class);
Route::post('employees/sync-vacations', [EmployeeController::class, 'syncVacations'])->name('employees.syncVacations');
// Custom employee_times routes (must be before resource route)
Route::get('employee_times/export-multiple', [EmployeeTimeController::class, 'exportMultipleTimesheets'])->name('employee_times.export-multiple');
Route::get('employee_times/import/progress', [EmployeeTimeController::class, 'importProgress'])->name('employee_times.import.progress');
Route::post('employee_times/import', [EmployeeTimeController::class, 'importExcel'])->name('employee_times.import');
Route::post('employee_times/bulk-update', [EmployeeTimeController::class, 'bulkUpdate'])->name('employee_times.bulk-update');
Route::post('employee_times/bulk-add', [EmployeeTimeController::class, 'bulkAdd'])->name('employee_times.bulk-add');
Route::get('employee_times/{employee}/export', [EmployeeTimeController::class, 'exportTimesheet'])->name('employee_times.export');
// Resource route (must be after custom routes)
Route::resource('employee_times', EmployeeTimeController::class);
Route::resource('yearly-vacations', YearlyVacationController::class);
Route::resource('sick-leaves', SickLeaveController::class);
Route::resource('salary', SalaryController::class);
Route::resource('position-improvements', PositionImprovementController::class);
Route::resource('vacation-dates', VacationDateController::class);
Route::post('vacation-dates/add-yearly', [VacationDateController::class, 'addYearly'])->name('vacation-dates.addYearly');
Route::resource('employee-vacations', EmployeeVacationController::class);

// Work Schedule routes
Route::get('work-schedule', function () {
    $schedule = \App\Models\WorkSchedule::first();
    return view('work_schedule', compact('schedule'));
})->name('work-schedule.edit');
Route::put('work-schedule', [WorkScheduleController::class, 'update'])->name('work-schedule.update');
