<?php

namespace App\Http\Controllers;

use App\Models\VacationDate;
use Illuminate\Http\Request;

class VacationDateController extends Controller
{
    public function addYearly(Request $request)
    {
        $year = intval($request->input('year'));
        if ($year < 2000 || $year > 2100) {
            return response()->json(['success' => false, 'message' => 'Invalid year.'], 400);
        }
        $dates = [
            ['month' => '01', 'day' => '01', 'name' => 'New year'],
            ['month' => '04', 'day' => '10', 'name' => 'Easter'],
            ['month' => '07', 'day' => '14', 'name' => 'National holiday France'],
            ['month' => '05', 'day' => '01', 'name' => 'Workers day'],
            ['month' => '08', 'day' => '15', 'name' => 'Assumption of the virgin Mary'],
            ['month' => '11', 'day' => '22', 'name' => 'Independence day'],
        ];
        $inserted = 0;
        foreach ($dates as $d) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $d['month'], $d['day']);
            if (!VacationDate::where('date', $dateStr)->exists()) {
                VacationDate::create([
                    'date' => $dateStr,
                    'name' => $d['name'],
                ]);
                $inserted++;
            }
        }
        return response()->json(['success' => true, 'inserted' => $inserted]);
    }
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
