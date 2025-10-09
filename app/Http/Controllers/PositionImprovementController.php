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

            // Set employee status to active and end_date to null
            $employee = Employee::find($validated['employee_id']);
            if ($employee) {
                $employee->status = 'active';
                $employee->end_date = null;
                $employee->save();
            }

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
            $employee = null;
            if (isset($validated['employee_id'])) {
                $employee = Employee::find($validated['employee_id']);
            }
            $salary = Salary::where('position_improvement_id', $positionImprovement->id)->first();

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

                // If there is no other active position for this employee, set employee status to active and clear end_date
                $activeCount = PositionImprovement::where('employee_id', $validated['employee_id'])->where('is_active', true)->count();
                if ($employee && $activeCount === 0) {
                    $employee->status = 'active';
                    $employee->end_date = null;
                    $employee->save();
                }
                // Set salary status to true and clear end_date
                if ($salary) {
                    $salary->status = true;
                    $salary->end_date = null;
                    $salary->save();
                }
            } else {
                // If end_date is provided, make this record inactive
                $validated['is_active'] = false;

                // If this was previously active and we're adding an end_date, set employee status to inactive and set end_date
                if ($positionImprovement->is_active && $validated['end_date']) {
                    if ($employee) {
                        $employee->status = 'inactive';
                        $employee->end_date = $validated['end_date'];
                        $employee->save();
                    }
                    if ($salary) {
                        $salary->status = false;
                        $salary->end_date = $validated['end_date'];
                        $salary->save();
                    }
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
        // 1. Delete all salaries for this position improvement
        \App\Models\Salary::where('position_improvement_id', $item->id)->delete();

        // 2. Find previous position improvement for same employee where end_date == $item->start_date
        $previousPosition = \App\Models\PositionImprovement::where('employee_id', $item->employee_id)
            ->where('end_date', $item->start_date)
            ->first();
        if ($previousPosition) {
            $previousPosition->is_active = true;
            $previousPosition->end_date = null;
            $previousPosition->save();

            // 4. Find latest salary for previous position and update status/end_date
            $latestSalary = \App\Models\Salary::where('position_improvement_id', $previousPosition->id)
                ->orderByDesc('end_date')
                ->first();
            if ($latestSalary) {
                $latestSalary->status = true;
                $latestSalary->end_date = null;
                $latestSalary->save();
            }

            // Update employee last_salary field
            $employee = \App\Models\Employee::find($previousPosition->employee_id);
            if ($employee) {
                $employee->last_salary = $latestSalary ? $latestSalary->amount : 0;
                $employee->save();
            }
        }

        // 3. Delete the position improvement
        $item->delete();
        return redirect()->route('position-improvements.index')->with('success', 'Position improvement deleted successfully');
    }
}
