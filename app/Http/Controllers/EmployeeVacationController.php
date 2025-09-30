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
        return view('employee-vacations.create', compact('employees', 'types', 'selectedEmployeeId'));
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
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('employee-vacations.index')]);
            }
            return redirect()->route('employee-vacations.index')->with('success', 'Employee vacation created successfully');
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
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = uniqid().'.'.$file->getClientOriginalExtension();
                $file->move(public_path('attachments'), $filename);
                $validated['attachment'] = $filename;
            } else {
                unset($validated['attachment']);
            }
            $model->update($validated);
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
        $item = \App\Models\EmployeeVacation::findOrFail($id);
        $item->delete();
        return redirect()->route('employee-vacations.index')->with('success', 'Employee vacation deleted successfully');
    }
}
