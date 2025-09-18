<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAttachment;
use App\Helpers\AttachmentHelper;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with([
            'position',
            'positionImprovements',
            'attachments',
            'yearlyVacations',
            'sickLeaves',
            'lateEarlyRecords'
        ])->get();
        return view('employees.index', compact('employees'));
    }

    public function show($id)
    {
        $employee = Employee::with([
            'position',
            'positionImprovements',
            'attachments',
            'yearlyVacations',
            'sickLeaves',
            'lateEarlyRecords'
        ])->findOrFail($id);
        return view('employees.show', compact('employee'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'required|string',
                'position_id' => 'required|integer|exists:lookup,id',
                'birthdate' => 'required|date|before:today',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'employment_type' => 'required|string|max:100',
            ]);
            $validated['image'] = AttachmentHelper::handleAttachment($validated['image']); // Default status
            $employee = Employee::create($validated);

            // Handle attachments
            if ($request->has('attachments')) {
                foreach ($request->attachments as $attachment) {
                    $url = AttachmentHelper::handleAttachment($attachment);
                    EmployeeAttachment::create([
                        'employee_id' => $employee->id,
                        'image' => $url,
                        'type' => 'attachment',
                    ]);
                }
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('employees.index')]);
            }
            return redirect()->route('employees.index')->with('success', 'Employee created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->validator->errors()->all()], 422);
            }
            throw $e;
        }
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string',
                'image' => 'nullable|string',
                'position_id' => 'sometimes|integer|exists:lookup,id',
                'birthdate' => 'sometimes|date',
                'start_date' => 'sometimes|date',
                'end_date' => 'nullable|date',
                'employment_type' => 'sometimes|string',
            ]);
            $employee = Employee::findOrFail($id);
            $validated['image'] = AttachmentHelper::handleAttachment($validated['image']); 
            $employee->update($validated);

            // Handle attachments
            if ($request->has('attachments')) {
                foreach ($request->attachments as $attachment) {
                    $url = AttachmentHelper::handleAttachment($attachment);
                    EmployeeAttachment::create([
                        'employee_id' => $employee->id,
                        'image' => $url,
                        'type' => 'attachment',
                    ]);
                }
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('employees.index')]);
            }
            return redirect()->route('employees.index')->with('success', 'Employee updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->validator->errors()->all()], 422);
            }
            throw $e;
        }
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully');
    }
}
