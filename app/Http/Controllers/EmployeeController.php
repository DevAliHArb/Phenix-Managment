<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('position', 'positionImprovements')->get();
        return view('employees.index', compact('employees'));
    }

    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.show', compact('employee'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $employee = Employee::create($request->validate([
            'name' => 'required|string',
            'image' => 'nullable|string',
            'position_id' => 'required|integer|exists:lookup,id',
            'birthdate' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'employment_type' => 'required|string',
        ]));
        return redirect()->route('employees.index')->with('success', 'Employee created successfully');
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update($request->validate([
            'name' => 'sometimes|string',
            'image' => 'nullable|string',
            'position_id' => 'sometimes|integer|exists:lookup,id',
            'birthdate' => 'sometimes|date',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date',
            'employment_type' => 'sometimes|string',
        ]));
        return redirect()->route('employees.index')->with('success', 'Employee updated successfully');
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully');
    }
}
