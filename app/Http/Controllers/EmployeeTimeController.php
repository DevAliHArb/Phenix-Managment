<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTime;
use Illuminate\Http\Request;

class EmployeeTimeController extends Controller
    public function importExcel(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $import = new \App\Imports\EmployeeTimeImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('excel_file'));

            // You can extend EmployeeTimeImport to collect results if needed
            return response()->json([
                'status' => 'success',
                'message' => 'Employee times import completed',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }
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
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'acc_number' => 'required|string',
                'date' => 'required|date',
                'clock_in' => 'required',
                'clock_out' => 'nullable',
                'total_time' => 'nullable|integer',
                'off_day' => 'nullable|boolean',
                'reason' => 'nullable|string',
            ]);
            $validated['off_day'] = $request->has('off_day');
            \App\Models\EmployeeTime::create($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('employee_times.index')]);
            }
            return redirect()->route('employee_times.index')->with('success', 'Time log created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->validator->errors()->all()], 422);
            }
            throw $e;
        }
    }

    public function edit($id)
    {
        $employeeTime = EmployeeTime::findOrFail($id);
        $employees = \App\Models\Employee::all();
        return view('employee_times.edit', compact('employeeTime', 'employees'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'acc_number' => 'sometimes|string',
                'date' => 'sometimes|date',
                'clock_in' => 'sometimes',
                'clock_out' => 'nullable',
                'total_time' => 'nullable|integer',
                'off_day' => 'nullable|boolean',
                'reason' => 'nullable|string',
            ]);
            $validated['off_day'] = $request->has('off_day');
            $model = \App\Models\EmployeeTime::findOrFail($id);
            $model->update($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('employee_times.index')]);
            }
            return redirect()->route('employee_times.index')->with('success', 'Time log updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->validator->errors()->all()], 422);
            }
            throw $e;
        }
    }

    public function destroy($id)
    {
        $employeeTime = EmployeeTime::findOrFail($id);
        $employeeTime->delete();
        return redirect()->route('employee_times.index')->with('success', 'Time log deleted successfully');
    }
}
