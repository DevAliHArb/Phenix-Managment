<?php

namespace App\Http\Controllers;

use App\Models\VacationDate;
use Illuminate\Http\Request;

class VacationDateController extends Controller
{
    public function index()
    {
        $vacations = VacationDate::orderBy('date')->get();
        return view('vacation-dates.index', compact('vacations'));
    }

    public function create()
    {
        return view('vacation-dates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'name' => 'required|string|max:255',
        ]);
        VacationDate::create($validated);

        // For all employees, set off_day and reason in employee_times for this date, create if not exist
        $employees = \App\Models\Employee::all();
        foreach ($employees as $employee) {
            $employeeTime = \App\Models\EmployeeTime::where('employee_id', $employee->id)
                ->where('date', $validated['date'])
                ->first();
            if ($employeeTime) {
                if (!$employeeTime->off_day) {
                    $employeeTime->off_day = true;
                    $employeeTime->reason = $validated['name'];
                    $employeeTime->vacation_type = 'Holiday';
                    $employeeTime->save();
                }
            } else {
                // Create a new EmployeeTime row for this employee and date
                \App\Models\EmployeeTime::create([
                    'employee_id' => $employee->id,
                    'date' => $validated['date'],
                    'off_day' => true,
                    'reason' => $validated['name'],
                    'vacation_type' => 'Holiday',
                ]);
            }
        }
        return redirect()->route('vacation-dates.index')->with('success', 'Vacation date added.');
    }

    public function edit(VacationDate $vacation_date)
    {
        return view('vacation-dates.edit', compact('vacation_date'));
    }

    public function update(Request $request, VacationDate $vacation_date)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'name' => 'required|string|max:255',
        ]);

        // Revert old date: set off_day = false and reason = null for all employees
        $employees = \App\Models\Employee::all();
        foreach ($employees as $employee) {
            $employeeTime = \App\Models\EmployeeTime::where('employee_id', $employee->id)
                ->where('date', $vacation_date->date)
                ->first();
            if ($employeeTime && $employeeTime->off_day) {
                $employeeTime->off_day = false;
                $employeeTime->reason = null;
                $employeeTime->vacation_type = null;
                $employeeTime->save();
            }
        }

        $vacation_date->update($validated);

        // For all employees, set off_day and reason in employee_times for new date
        foreach ($employees as $employee) {
            $employeeTime = \App\Models\EmployeeTime::where('employee_id', $employee->id)
                ->where('date', $validated['date'])
                ->first();
            if ($employeeTime) {
                if (!$employeeTime->off_day) {
                    $employeeTime->off_day = true;
                    $employeeTime->reason = $validated['name'];
                    $employeeTime->vacation_type = 'Holiday';
                    $employeeTime->save();
                }
            } else {
                // Create a new EmployeeTime row for this employee and date
                \App\Models\EmployeeTime::create([
                    'employee_id' => $employee->id,
                    'date' => $validated['date'],
                    'off_day' => true,
                    'reason' => $validated['name'],
                    'vacation_type' => 'Holiday',
                ]);
            }
        }

        return redirect()->route('vacation-dates.index')->with('success', 'Vacation date updated.');
    }

    public function destroy(VacationDate $vacation_date)
    {
        $vacation_date->delete();
        return redirect()->route('vacation-dates.index')->with('success', 'Vacation date deleted.');
    }
}
