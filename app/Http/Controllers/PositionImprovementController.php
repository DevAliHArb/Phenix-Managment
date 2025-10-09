<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Lookup;
use App\Models\PositionImprovement;
use App\Models\Salary;
use Illuminate\Http\Request;

class PositionImprovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $items = PositionImprovement::all();
    $salaryItems = Salary::all();
    return view('position-improvements.index', compact('items', 'salaryItems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $positions = Lookup::where('parent_id', 1)->get();
        $employees = Employee::all();
        $selectedEmployeeId = $request->get('employee_id');
        $lockEmployee = $request->get('lock_employee', false);
        $returnUrl = $request->get('return_url', route('position-improvements.index'));
        return view('position-improvements.create', compact('positions', 'employees', 'selectedEmployeeId', 'lockEmployee', 'returnUrl'));
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

            // Check if there's already an active position improvement for the same employee and position
            $existingActive = PositionImprovement::where('employee_id', $validated['employee_id'])
                ->where('position_id', $validated['position_id'])
                ->where('is_active', true)
                ->first();

            if ($existingActive) {
                $errorMessage = 'This employee already has an active position improvement for the selected position.';
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'errors' => [$errorMessage]], 422);
                }
                return back()->withInput()->withErrors(['position_id' => $errorMessage]);
            }

            // Check for same or earlier date validation
            $lastPositionImprovement = PositionImprovement::where('employee_id', $validated['employee_id'])
                ->orderBy('start_date', 'desc')
                ->first();

            if ($lastPositionImprovement) {
                $newStartDate = new \DateTime($validated['start_date']);
                $lastStartDate = new \DateTime($lastPositionImprovement->start_date);

                if ($newStartDate <= $lastStartDate) {
                    $errorMessage = 'Start date must be after the employee\'s last position improvement date (' . $lastPositionImprovement->start_date . ').';
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'errors' => [$errorMessage]], 422);
                    }
                    return back()->withInput()->withErrors(['start_date' => $errorMessage]);
                }
            }

            $newSalary = null;
            // Deactivate current active row for this employee and set its end_date
            $activeRow = PositionImprovement::where('employee_id', $validated['employee_id'])
                ->where('is_active', true)
                ->first();
            if ($activeRow) {
                $activeRow->is_active = false;
                $activeRow->end_date = $validated['start_date'];
                $activeRow->save();

                $activeSalary = Salary::where('position_improvement_id', $activeRow->id)
                    ->where('status', true)
                    ->first();
                if ($activeSalary) {
                    $activeSalary->status = false;
                    $activeSalary->end_date = $validated['start_date'];
                    $activeSalary->save();

                    // Create new salary row for the new position improvement
                    $newSalary = $activeSalary->replicate();
                    $newSalary->status = true;
                    $newSalary->start_date = $validated['start_date'];
                    $newSalary->end_date = null;
                }
            }

            $newPositionImprovement = PositionImprovement::create($validated);

            // If a new salary was prepared, assign the new position_improvement_id and save
            if ($newSalary) {
                $newSalary->position_improvement_id = $newPositionImprovement->id;
                $newSalary->save();
            }
            $returnUrl = $request->get('return_url', route('position-improvements.index'));
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => $returnUrl]);
            }
            return redirect($returnUrl)->with('success', 'Position improvement created successfully');
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
        $positions = Lookup::where('parent_id', 1)->get();
        $employees = Employee::all();
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
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);
            
            // Convert empty end_date to null
            $endDateDeleted = false;
            if (empty($validated['end_date'])) {
                $validated['end_date'] = null;
                $endDateDeleted = true;
            }
            
            $positionImprovement = PositionImprovement::findOrFail($id);
            
            // Handle activation/deactivation logic based on end_date
            if ($endDateDeleted) {
                // If end_date is deleted (set to null), make this record active
                $validated['is_active'] = true;
                
                // Find any other active position improvement for the same employee (excluding current record)
                $otherActiveRecord = PositionImprovement::where('employee_id', $validated['employee_id'])
                    ->where('id', '!=', $id)
                    ->where('is_active', true)
                    ->first();
                
                if ($otherActiveRecord) {
                    // Make the other record inactive and set its end_date to this record's start_date
                    $otherActiveRecord->is_active = false;
                    $otherActiveRecord->end_date = $validated['start_date'];
                    $otherActiveRecord->save();
                }
            } else {
                // If end_date is provided, make this record inactive
                $validated['is_active'] = false;
                
                // Check if this was previously active and we're adding an end_date
                if ($positionImprovement->is_active && $validated['end_date']) {
                    // This record is becoming inactive, no need to change other records
                    // as there should be no other active records for this employee
                }
            }
            
            // Check for same or earlier date validation (excluding current record)
            $lastPositionImprovement = PositionImprovement::where('employee_id', $validated['employee_id'])
                ->where('id', '!=', $id) // Exclude current record
                ->orderBy('start_date', 'desc')
                ->first();

            if ($lastPositionImprovement) {
                $newStartDate = new \DateTime($validated['start_date']);
                $lastStartDate = new \DateTime($lastPositionImprovement->start_date);

                if ($newStartDate <= $lastStartDate) {
                    $errorMessage = 'Start date must be after the employee\'s last position improvement date (' . $lastPositionImprovement->start_date . ').';
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'errors' => [$errorMessage]], 422);
                    }
                    return back()->withInput()->withErrors(['start_date' => $errorMessage]);
                }
            }
            
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
