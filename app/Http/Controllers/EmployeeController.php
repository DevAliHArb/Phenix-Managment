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
            'EmployeeType',
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
            'EmployeeType',
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
    $positions = \App\Models\Lookup::where('parent_id', 1)->get();
    $employmentTypes = \App\Models\Lookup::where('parent_id', 7)->get();
    return view('employees.create', compact('positions', 'employmentTypes'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'date_of_birth' => 'required|date|before:today',
                'phone' => 'required|string|max:20',
                // 'image' => 'required|url|max:255',
                'position_id' => 'required|integer|exists:lookup,id',
                'lookup_employee_type_id' => 'required|integer|exists:lookup,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'status' => 'required|in:active,inactive',
                'working_hours_from' => 'required|date_format:H:i',
                'working_hours_to' => 'required|date_format:H:i|after:working_hours_from',
                'yearly_vacations_total' => 'integer|min:0',
                'yearly_vacations_used' => 'integer|min:0',
                'yearly_vacations_left' => 'integer|min:0',
                'sick_leave_used' => 'integer|min:0',
                'last_salary' => 'numeric|min:0',
            ]);
            $validated['image'] = AttachmentHelper::handleAttachment($request['image']);

            // Set working day fields
            $days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
            foreach ($days as $day) {
                $validated[$day] = in_array($day, $request->working_days ?? []) ? true : false;
            }

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
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $positions = \App\Models\Lookup::where('parent_id', 1)->get();
        $employmentTypes = \App\Models\Lookup::where('parent_id', 7)->get();
        return view('employees.edit', compact('employee', 'positions', 'employmentTypes'));
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
                'lookup_employee_type_id' => 'sometimes|integer|exists:lookup,id',
            ]);
            $employee = Employee::findOrFail($id);
            $validated['image'] = AttachmentHelper::handleAttachment($validated['image']);

            // Set working day fields
            $days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
            foreach ($days as $day) {
                $validated[$day] = in_array($day, $request->working_days ?? []) ? true : false;
            }

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
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully');
    }
}
