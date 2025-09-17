<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTime;
use Illuminate\Http\Request;

class EmployeeTimeController extends Controller
{
    public function index()
    {
        $employeeTimes = EmployeeTime::with('employee')->get();
        return view('employee_times.index', compact('employeeTimes'));
    }

    public function show($id)
    {
        $employeeTime = EmployeeTime::with('employee')->findOrFail($id);
        return view('employee_times.show', compact('employeeTime'));
    }

    public function create()
    {
        $employees = \App\Models\Employee::all();
        return view('employee_times.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'acc_number' => 'required|string',
            'date' => 'required|date',
            'clock_in' => 'required',
            'clock_out' => 'nullable',
            'total_time' => 'nullable|integer',
            'off_day' => 'nullable|boolean',
            'reason' => 'nullable|string',
        ]);
        $data['off_day'] = $request->has('off_day');
        $employeeTime = EmployeeTime::create($data);
        return redirect()->route('employee_times.index')->with('success', 'Time log created successfully');
    }

    public function edit($id)
    {
        $employeeTime = EmployeeTime::findOrFail($id);
        $employees = \App\Models\Employee::all();
        return view('employee_times.edit', compact('employeeTime', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'acc_number' => 'sometimes|string',
            'date' => 'sometimes|date',
            'clock_in' => 'sometimes',
            'clock_out' => 'nullable',
            'total_time' => 'nullable|integer',
            'off_day' => 'nullable|boolean',
            'reason' => 'nullable|string',
        ]);
        $data['off_day'] = $request->has('off_day');
        $employeeTime = EmployeeTime::findOrFail($id);
        $employeeTime->update($data);
        return redirect()->route('employee_times.index')->with('success', 'Time log updated successfully');
    }

    public function destroy($id)
    {
        $employeeTime = EmployeeTime::findOrFail($id);
        $employeeTime->delete();
        return redirect()->route('employee_times.index')->with('success', 'Time log deleted successfully');
    }
}
