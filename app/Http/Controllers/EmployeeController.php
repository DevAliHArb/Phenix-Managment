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
            'positionImprovements' => function ($query) {
                $query->with('salaries');
            },
            'attachments',
            'yearlyVacations',
            'sickLeaves',
            'employeeVacations' => function ($query) {
                $query->with('type');
            },
            'lateEarlyRecords',
            'employeeTimes'
        ])->get();
        return view('employees.index', compact('employees'));
    }

    public function show($id)
    {
        $employee = Employee::with([
            'position',
            'EmployeeType',
            'positionImprovements' => function ($query) {
                $query->with('salaries');
            },
            'attachments',
            'yearlyVacations',
            'sickLeaves',
            'attachments',
            'employeeVacations' => function ($query) {
                $query->with('type');
            },
            'lateEarlyRecords',
            'employeeTimes'
        ])->findOrFail($id);
        return view('employees.show', compact('employee'));
    }

     public function create()
    {
    $positions = \App\Models\Lookup::where('parent_id', 1)->get();
    $employmentTypes = \App\Models\Lookup::where('parent_id', 24)->get();
    return view('employees.create', compact('positions', 'employmentTypes'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'mid_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'address' => 'string|max:255',
                'date_of_birth' => 'date|before:today',
                'phone' => 'string|max:20',
                // 'image' => 'required|url|max:255',
                // 'position_id' => 'required|integer|exists:lookup,id',
                'lookup_employee_type_id' => 'integer|exists:lookup,id',
                'start_date' => 'date',
                // 'end_date' => 'date|after_or_equal:start_date',
                'status' => 'in:active,inactive',
                'working_hours_from' => 'date_format:H:i',
                'working_hours_to' => 'date_format:H:i|after:working_hours_from',
                // 'yearly_vacations_total' => 'integer|min:0',
                // 'yearly_vacations_used' => 'integer|min:0',
                // 'yearly_vacations_left' => 'integer|min:0',
                // 'sick_leave_used' => 'integer|min:0',
                // 'last_salary' => 'numeric|min:0',
                // New fields (not required)
                'city' => 'nullable|string|max:255',
                'province' => 'nullable|string|max:255',
                'building_name' => 'nullable|string|max:255',
                'floor' => 'nullable|string|max:255',
                'housing_type' => 'nullable|in:rent,own',
                'owner_name' => 'nullable|string|max:255',
                'owner_mobile_number' => 'nullable|string|max:20',
            ]);
            $validated['image'] = AttachmentHelper::handleAttachment($request['image']);

            // Set working day fields
            $days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
            foreach ($days as $day) {
                $validated[$day] = in_array($day, $request->working_days ?? []) ? true : false;
            }


            // Calculate completed months from start_date to now
            $startDate = isset($validated['start_date']) ? \Carbon\Carbon::parse($validated['start_date']) : null;
            $now = \Carbon\Carbon::now();
            $monthsCompleted = 0;
            if ($startDate && $startDate->lessThanOrEqualTo($now)) {
                $monthsCompleted = $startDate->diffInMonths($now);
            }
            $validated['yearly_vacations_total'] = floor($monthsCompleted * 1.25);

            $employee = Employee::create($validated);

            // Handle attachments
            if ($request->has('attachments')) {
                foreach ($request->input('attachments') as $index => $attachment) {
                    // Check if both file and type are present
                    if (isset($attachment['type']) && !empty($attachment['type']) && $request->hasFile("attachments.{$index}.file")) {
                        // Get the uploaded file
                        $uploadedFile = $request->file("attachments.{$index}.file");
                        
                        // Create attachments directory if it doesn't exist
                        $attachmentsPath = public_path('attachments');
                        if (!file_exists($attachmentsPath)) {
                            mkdir($attachmentsPath, 0755, true);
                        }
                        
                        // Move file to attachments directory
                        $fileName = time() . '_' . $index . '_' . $uploadedFile->getClientOriginalName();
                        $uploadedFile->move($attachmentsPath, $fileName);
                        $url = asset('attachments/' . $fileName);
                        
                        // Create attachment record
                        EmployeeAttachment::create([
                            'employee_id' => $employee->id,
                            'image' => $url,
                            'type' => $attachment['type'],
                        ]);
                    }
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
        $employmentTypes = \App\Models\Lookup::where('parent_id', 24)->get();
        return view('employees.edit', compact('employee', 'positions', 'employmentTypes'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'mid_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date|before:today',
                'phone' => 'nullable|string|max:20',
                'image' => 'nullable|string',
                'position_id' => 'nullable|integer|exists:lookup,id',
                'lookup_employee_type_id' => 'nullable|integer|exists:lookup,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'status' => 'nullable|in:active,inactive',
                'working_hours_from' => 'nullable|date_format:H:i',
                'working_hours_to' => 'nullable|date_format:H:i|after:working_hours_from',
                // New fields (not required)
                'city' => 'nullable|string|max:255',
                'province' => 'nullable|string|max:255',
                'building_name' => 'nullable|string|max:255',
                'floor' => 'nullable|string|max:255',
                'housing_type' => 'nullable|in:rent,own',
                'owner_name' => 'nullable|string|max:255',
                'owner_mobile_number' => 'nullable|string|max:20',
                'acc_number' => 'nullable|integer',
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
                foreach ($request->input('attachments') as $index => $attachment) {
                    // Check if both file and type are present
                    if (isset($attachment['type']) && !empty($attachment['type']) && $request->hasFile("attachments.{$index}.file")) {
                        // Get the uploaded file
                        $uploadedFile = $request->file("attachments.{$index}.file");
                        
                        // Create attachments directory if it doesn't exist
                        $attachmentsPath = public_path('attachments');
                        if (!file_exists($attachmentsPath)) {
                            mkdir($attachmentsPath, 0755, true);
                        }
                        
                        // Move file to attachments directory
                        $fileName = time() . '_' . $index . '_' . $uploadedFile->getClientOriginalName();
                        $uploadedFile->move($attachmentsPath, $fileName);
                        $url = asset('attachments/' . $fileName);
                        
                        // Create attachment record
                        EmployeeAttachment::create([
                            'employee_id' => $employee->id,
                            'image' => $url,
                            'type' => $attachment['type'],
                        ]);
                    }
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
