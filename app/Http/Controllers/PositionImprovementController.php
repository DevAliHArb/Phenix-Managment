<?php

namespace App\Http\Controllers;

use App\Models\PositionImprovement;
use Illuminate\Http\Request;

class PositionImprovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $items = PositionImprovement::all();
    return view('position-improvements.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $positions = \App\Models\Lookup::where('parent_id', 1)->get();
        $employees = \App\Models\Employee::all();
        $selectedEmployeeId = $request->get('employee_id');
        return view('position-improvements.create', compact('positions', 'employees', 'selectedEmployeeId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'position_id' => 'required|exists:lookup,id',
                'employee_id' => 'required|exists:employees,id',
                'start_date' => 'required|date',
            ]);

            // Deactivate current active row for this employee and set its end_date
            $activeRow = \App\Models\PositionImprovement::where('employee_id', $validated['employee_id'])
                ->where('is_active', true)
                ->first();
            if ($activeRow) {
                $activeRow->is_active = false;
                $activeRow->end_date = $validated['start_date'];
                $activeRow->save();
            }

            \App\Models\PositionImprovement::create($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('position-improvements.index')]);
            }
            return redirect()->route('position-improvements.index')->with('success', 'Position improvement created successfully');
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
    $item = PositionImprovement::findOrFail($id);
    return view('position-improvements.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $positionImprovement = PositionImprovement::findOrFail($id);
        $positions = \App\Models\Lookup::where('parent_id', 1)->get();
        $employees = \App\Models\Employee::all();
        return view('position-improvements.edit', compact('positionImprovement', 'positions', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'position_id' => 'required|exists:lookup,id',
                'employee_id' => 'required|exists:employees,id',
                'start_date' => 'required|date',
            ]);
            $positionImprovement = PositionImprovement::findOrFail($id);
            $positionImprovement->update($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('position-improvements.index')]);
            }
            return redirect()->route('position-improvements.index')->with('success', 'Position improvement updated successfully');
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
    $item = PositionImprovement::findOrFail($id);
    $item->delete();
    return redirect()->route('position-improvements.index')->with('success', 'Position improvement deleted successfully');
    }
}
