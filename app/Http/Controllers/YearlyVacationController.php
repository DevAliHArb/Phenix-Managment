<?php

namespace App\Http\Controllers;

use App\Models\YearlyVacation;
use App\Models\Employee;
use Illuminate\Http\Request;

class YearlyVacationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $yearlyVacations = YearlyVacation::all();
    return view('yearly_vacations.index', compact('yearlyVacations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $employees = Employee::all();
        $selectedEmployeeId = $request->get('employee_id');
        return view('yearly_vacations.create', compact('employees', 'selectedEmployeeId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'date' => 'required|date',
                'reason' => 'required|string|max:255',
            ]);

            YearlyVacation::create($validated);

            // Set off_day and reason in employee_times if not already off_day
            $employeeTime = \App\Models\EmployeeTime::where('employee_id', $validated['employee_id'])
                ->where('date', $validated['date'])
                ->first();
            if ($employeeTime && !$employeeTime->off_day) {
                $employeeTime->off_day = true;
                $employeeTime->reason = 'Vacation';
                $employeeTime->save();
            }

            // Increment yearly_vacations_used and decrement yearly_vacations_left for the employee
            $employee = \App\Models\Employee::find($validated['employee_id']);
            if ($employee) {
                $employee->increment('yearly_vacations_used');
                $employee->decrement('yearly_vacations_left');
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('yearly-vacations.index')]);
            }
            return redirect()->route('yearly-vacations.index')->with('success', 'Yearly vacation created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->validator->errors()->all()], 422);
            }
            throw $e;
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    $item = YearlyVacation::findOrFail($id);
    return view('yearly_vacations.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $yearlyVacation = YearlyVacation::findOrFail($id);
        $employees = Employee::all();
        return view('yearly_vacations.edit', compact('yearlyVacation', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'date' => 'required|date',
                'reason' => 'required|string|max:255',
            ]);
            $yearlyVacation = YearlyVacation::findOrFail($id);
            $yearlyVacation->update($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('yearly-vacations.index')]);
            }
            return redirect()->route('yearly-vacations.index')->with('success', 'Yearly vacation updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->validator->errors()->all()], 422);
            }
            throw $e;
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    $item = YearlyVacation::findOrFail($id);
    $item->delete();
    return redirect()->route('yearly_vacations.index')->with('success', 'Yearly vacation deleted successfully');
    }
}
