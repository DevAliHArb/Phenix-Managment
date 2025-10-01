<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmployeeVacationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = \App\Models\EmployeeVacation::with(['employee', 'type'])->get();
        return view('employee-vacations.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $employees = \App\Models\Employee::all();
        $types = \App\Models\Lookup::where('parent_id', 30)->get();
        $selectedEmployeeId = $request->get('employee_id');
        $lockEmployee = $request->get('lock_employee', false);
        $returnUrl = $request->get('return_url', route('employee-vacations.index'));
        return view('employee-vacations.create', compact('employees', 'types', 'selectedEmployeeId', 'lockEmployee', 'returnUrl'));
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
                'lookup_type_id' => 'required|exists:lookup,id',
                'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            ]);
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = uniqid().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('attachments'), $filename);
                $validated['attachment'] = $filename;
            } else {
                $validated['attachment'] = null;
            }
            \App\Models\EmployeeVacation::create($validated);
            

            // Set vacation_type based on lookup_type_id
            $vacationType = null;
            if ($validated['lookup_type_id'] == 31) {
                $vacationType = 'Vacation';
            } elseif ($validated['lookup_type_id'] == 32) {
                $vacationType = 'Sick Leave';
            }

            // Set off_day and reason in employee_times, create if not exist
            $employeeTime = \App\Models\EmployeeTime::where('employee_id', $validated['employee_id'])
                ->where('date', $validated['date'])
                ->first();
            if ($employeeTime) {
                if (!$employeeTime->off_day) {
                    $employeeTime->off_day = true;
                    $employeeTime->reason = $validated['reason'];
                    $employeeTime->vacation_type = $vacationType;
                    $employeeTime->save();
                }
            } else {
                \App\Models\EmployeeTime::create([
                    'employee_id' => $validated['employee_id'],
                    'date' => $validated['date'],
                    'off_day' => true,
                    'reason' => $validated['reason'],
                    'vacation_type' => $vacationType,
                ]);
            }
            $returnUrl = $request->get('return_url', route('employee-vacations.index'));
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => $returnUrl]);
            }
            return redirect($returnUrl)->with('success', 'Employee vacation created successfully');
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
        $item = \App\Models\EmployeeVacation::with(['employee', 'type'])->findOrFail($id);
        return view('employee-vacations.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = \App\Models\EmployeeVacation::findOrFail($id);
        $employees = \App\Models\Employee::all();
        $types = \App\Models\Lookup::where('parent_id', 30)->get();
        return view('employee-vacations.edit', compact('item', 'employees', 'types'));
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
                'lookup_type_id' => 'required|exists:lookup,id',
                'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            ]);
            $model = \App\Models\EmployeeVacation::findOrFail($id);
            // Revert old date: set off_day = false, reason = null, vacation_type = null
            $oldEmployeeTime = \App\Models\EmployeeTime::where('employee_id', $model->employee_id)
                ->where('date', $model->date)
                ->first();
            if ($oldEmployeeTime && $oldEmployeeTime->off_day) {
                $oldEmployeeTime->off_day = false;
                $oldEmployeeTime->reason = null;
                $oldEmployeeTime->vacation_type = null;
                $oldEmployeeTime->save();
            }

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = uniqid().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('attachments'), $filename);
                $validated['attachment'] = $filename;
            } else {
                unset($validated['attachment']);
            }
            $model->update($validated);

            // Set vacation_type based on lookup_type_id
            $vacationType = null;
            if ($validated['lookup_type_id'] == 31) {
                $vacationType = 'Vacation';
            } elseif ($validated['lookup_type_id'] == 32) {
                $vacationType = 'Sick Leave';
            }

            // Set off_day and reason in employee_times for new date, create if not exist
            $employeeTime = \App\Models\EmployeeTime::where('employee_id', $validated['employee_id'])
                ->where('date', $validated['date'])
                ->first();
            if ($employeeTime) {
                if (!$employeeTime->off_day) {
                    $employeeTime->off_day = true;
                    $employeeTime->reason = $validated['reason'];
                    $employeeTime->vacation_type = $vacationType;
                    $employeeTime->save();
                }
            } else {
                \App\Models\EmployeeTime::create([
                    'employee_id' => $validated['employee_id'],
                    'date' => $validated['date'],
                    'off_day' => true,
                    'reason' => $validated['reason'],
                    'vacation_type' => $vacationType,
                ]);
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('employee-vacations.index')]);
            }
            return redirect()->route('employee-vacations.index')->with('success', 'Employee vacation updated successfully');
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
        try {
            $item = \App\Models\EmployeeVacation::findOrFail($id);
            
            // Revert employee_times: set off_day = false, reason = null, vacation_type = null
            $employeeTime = \App\Models\EmployeeTime::where('employee_id', $item->employee_id)
                ->where('date', $item->date)
                ->first();
            if ($employeeTime && $employeeTime->off_day) {
                $employeeTime->off_day = false;
                $employeeTime->reason = null;
                $employeeTime->vacation_type = null;
                $employeeTime->save();
            }
            
            $item->delete();
            
            return redirect()->back()->with('success', 'Employee vacation deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete employee vacation: ' . $e->getMessage());
        }
    }
}
