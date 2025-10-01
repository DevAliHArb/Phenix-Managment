<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function edit($id)
    {
        $item = \App\Models\Salary::findOrFail($id);
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
            // Deactivate current active row for this employee and set its end_date
            $activeRow = \App\Models\Salary::where('position_improvement_id', $request['position_improvement_id'])
                ->where('status', true)
                ->first();
            if ($activeRow) {
                $activeRow->status = false;
                $activeRow->end_date = $request['start_date'];
                $activeRow->save();
            }
            \App\Models\Salary::create($request->all());
            
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
            $model = \App\Models\Salary::findOrFail($id);
            $model->update($request->all());
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
            $salary->delete();
            
            return redirect()->back()->with('success', 'Salary record deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete salary record: ' . $e->getMessage());
        }
    }
}
