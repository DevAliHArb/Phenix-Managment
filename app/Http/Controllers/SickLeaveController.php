<?php

namespace App\Http\Controllers;

use App\Helpers\AttachmentHelper;
use Illuminate\Http\Request;
use App\Models\Employee;

class SickLeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $items = \App\Models\SickLeave::all();
    return view('sick-leaves.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
    $employees = Employee::all();
    $selectedEmployeeId = $request->get('employee_id');
    return view('sick-leaves.create', compact('employees', 'selectedEmployeeId'));
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
                'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            ]);
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $validated['attachment'] = AttachmentHelper::handleAttachment($file);
            } else {
                $validated['attachment'] = null;
            }

            // Create sick leave
            \App\Models\SickLeave::create($validated);

            // Set off_day and reason in employee_times if not already off_day
            $employeeTime = \App\Models\EmployeeTime::where('employee_id', $validated['employee_id'])
                ->where('date', $validated['date'])
                ->first();
            if ($employeeTime && !$employeeTime->off_day) {
                $employeeTime->off_day = true;
                $employeeTime->reason = 'Sick Leave';
                $employeeTime->save();
            }

            // Increment sick_leave_used for the employee
            $employee = \App\Models\Employee::find($request->employee_id);
            if ($employee) {
                $employee->increment('sick_leave_used');
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('sick-leaves.index')]);
            }
            return redirect()->route('sick-leaves.index')->with('success', 'Sick leave created successfully');
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
    $item = \App\Models\SickLeave::findOrFail($id);
    return view('sick-leaves.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    $item = \App\Models\SickLeave::findOrFail($id);
    return view('sick-leaves.edit', compact('item'));
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
                'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            ]);
            $model = \App\Models\SickLeave::findOrFail($id);
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $validated['attachment'] = AttachmentHelper::handleAttachment($file);
            } else {
                unset($validated['attachment']); // Don't overwrite if not uploading new file
            }
            $model->update($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('sick-leaves.index')]);
            }
            return redirect()->route('sick-leaves.index')->with('success', 'Sick leave updated successfully');
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
    $item = \App\Models\SickLeave::findOrFail($id);
    $item->delete();
    return redirect()->route('sick-leaves.index')->with('success', 'Sick leave deleted successfully');
    }
}
