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

        // For all employees, set off_day and reason in employee_times if not already off_day
        $employees = \App\Models\Employee::all();
        foreach ($employees as $employee) {
            $employeeTime = \App\Models\EmployeeTime::where('employee_id', $employee->id)
                ->where('date', $validated['date'])
                ->first();
            if ($employeeTime && !$employeeTime->off_day) {
                $employeeTime->off_day = true;
                $employeeTime->reason = $validated['name'];
                $employeeTime->save();
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
        $vacation_date->update($validated);
        return redirect()->route('vacation-dates.index')->with('success', 'Vacation date updated.');
    }

    public function destroy(VacationDate $vacation_date)
    {
        $vacation_date->delete();
        return redirect()->route('vacation-dates.index')->with('success', 'Vacation date deleted.');
    }
}
