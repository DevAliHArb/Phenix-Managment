<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function create()
    {
        return view('salary.create');
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
                // Add your validation rules here
            ]);
            \App\Models\Salary::create($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('salary.index')]);
            }
            return redirect()->route('salary.index')->with('success', 'Salary created successfully');
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
                // Add your validation rules here
            ]);
            $model = \App\Models\Salary::findOrFail($id);
            $model->update($validated);
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
}
