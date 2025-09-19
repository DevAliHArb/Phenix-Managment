<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PositionImprovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $items = \App\Models\PositionImprovement::all();
    return view('position_improvements.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    return view('position_improvements.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                // Add your validation rules here
            ]);
            $model = new \App\Models\PositionImprovement();
            $model->create($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('position_improvements.index')]);
            }
            return redirect()->route('position_improvements.index')->with('success', 'Position improvement created successfully');
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
    $item = \App\Models\PositionImprovement::findOrFail($id);
    return view('position_improvements.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    $item = \App\Models\PositionImprovement::findOrFail($id);
    return view('position_improvements.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                // Add your validation rules here
            ]);
            $model = \App\Models\PositionImprovement::findOrFail($id);
            $model->update($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('position_improvements.index')]);
            }
            return redirect()->route('position_improvements.index')->with('success', 'Position improvement updated successfully');
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
    $item = \App\Models\PositionImprovement::findOrFail($id);
    $item->delete();
    return redirect()->route('position_improvements.index')->with('success', 'Position improvement deleted successfully');
    }
}
