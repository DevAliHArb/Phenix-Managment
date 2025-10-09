<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function edit($id)
    {
        $item = Salary::findOrFail($id);
        return view('salary.edit', compact('item'));
    }
    public function create(Request $request)
    {
        $selectedPositionImprovementId = $request->get('position_improvement_id', null);
        $lockPositionImprovement = $request->get('lock_position_improvement', false);
        $returnUrl = $request->get('return_url', route('salary.index'));
        return view('salary.create', compact('selectedPositionImprovementId', 'lockPositionImprovement', 'returnUrl'));
    }

    public function index()
    {
        $items = Salary::all();
        return view('salary.index', compact('items'));
    }

    public function show($id)
    {
        $item = Salary::findOrFail($id);
        return view('salary.show', compact('item'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'position_improvement_id' => 'required|exists:position_improvements,id',
                'salary' => 'required|numeric',
                'status' => 'required|boolean',
                'start_date' => 'required|date',
            ]);

            // Check if trying to create an active salary when one already exists for this position improvement
            if ($validated['status'] == true) {
                $existingActiveSalary = Salary::where('position_improvement_id', $validated['position_improvement_id'])
                    ->where('status', true)
                    ->first();

                if ($existingActiveSalary && $existingActiveSalary->salary == $validated['salary']) {
                    $errorMessage = 'There is already an active salary record with the same amount for this position improvement.';
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'errors' => [$errorMessage]], 422);
                    }
                    return back()->withInput()->withErrors(['salary' => $errorMessage]);
                }
            }

            // Check for same or earlier date validation
            $lastSalary = Salary::where('position_improvement_id', $validated['position_improvement_id'])
                ->orderBy('start_date', 'desc')
                ->first();

            if ($lastSalary) {
                $newStartDate = new \DateTime($validated['start_date']);
                $lastStartDate = new \DateTime($lastSalary->start_date);

                if ($newStartDate <= $lastStartDate) {
                    $errorMessage = 'Start date must be after the last salary record date (' . $lastSalary->start_date . ') for this position improvement.';
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'errors' => [$errorMessage]], 422);
                    }
                    return back()->withInput()->withErrors(['start_date' => $errorMessage]);
                }
            }

            // Deactivate current active row for this employee and set its end_date
            $activeRow = Salary::where('position_improvement_id', $request['position_improvement_id'])
                ->where('status', true)
                ->first();
            if ($activeRow) {
                $activeRow->status = false;
                $activeRow->end_date = $request['start_date'];
                $activeRow->save();
            }
            $newSalary = Salary::create($request->all());

            // Update employee last_salary
            $positionImprovement = \App\Models\PositionImprovement::find($request['position_improvement_id']);
            if ($positionImprovement) {
                $employee = \App\Models\Employee::find($positionImprovement->employee_id);
                if ($employee) {
                    $employee->last_salary = $request['salary'];
                    $employee->save();
                }
            }
            
            $returnUrl = $request->get('return_url', route('salary.index'));
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => $returnUrl]);
            }
            return redirect($returnUrl)->with('success', 'Salary created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->validator->errors()->all()], 422);
            }
            throw $e;
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'position_improvement_id' => 'required|exists:position_improvements,id',
                'salary' => 'required|numeric',
                'status' => 'required|boolean',
                'start_date' => 'required|date',
            ]);
            
            $model = Salary::findOrFail($id);
            
            // Check for same or earlier date validation (excluding current record)
            $lastSalary = Salary::where('position_improvement_id', $validated['position_improvement_id'])
                ->where('id', '!=', $id) // Exclude current record
                ->orderBy('start_date', 'desc')
                ->first();

            if ($lastSalary) {
                $newStartDate = new \DateTime($validated['start_date']);
                $lastStartDate = new \DateTime($lastSalary->start_date);

                if ($newStartDate <= $lastStartDate) {
                    $errorMessage = 'Start date must be after the last salary record date (' . $lastSalary->start_date . ') for this position improvement.';
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'errors' => [$errorMessage]], 422);
                    }
                    return back()->withInput()->withErrors(['start_date' => $errorMessage]);
                }
            }
            
            $model->update($request->all());

            // Update employee last_salary
            $positionImprovement = \App\Models\PositionImprovement::find($request['position_improvement_id']);
            if ($positionImprovement) {
                $employee = \App\Models\Employee::find($positionImprovement->employee_id);
                if ($employee) {
                    $employee->last_salary = $request['salary'];
                    $employee->save();
                }
            }
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('salary.index')]);
            }
            return redirect()->route('salary.index')->with('success', 'Salary updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->validator->errors()->all()], 422);
            }
            throw $e;
        }
    }

    public function destroy($id)
    {
        try {
            $salary = Salary::findOrFail($id);
            $positionImprovementId = $salary->position_improvement_id;
            $salary->delete();

            // Find previous salary for same position improvement, set status true and end_date null
            $previousSalary = Salary::where('position_improvement_id', $positionImprovementId)
                ->orderByDesc('end_date')
                ->first();
            if ($previousSalary) {
                $previousSalary->status = true;
                $previousSalary->end_date = null;
                $previousSalary->save();
            }

            // Update employee last_salary
            $positionImprovement = \App\Models\PositionImprovement::find($positionImprovementId);
            if ($positionImprovement) {
                $employee = \App\Models\Employee::find($positionImprovement->employee_id);
                if ($employee) {
                    $employee->last_salary = $previousSalary ? $previousSalary->salary : 0;
                    $employee->save();
                }
            }

            return redirect()->back()->with('success', 'Salary record deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete salary record: ' . $e->getMessage());
        }
    }
}
