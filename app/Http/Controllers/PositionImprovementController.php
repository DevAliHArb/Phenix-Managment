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
    public function create()
    {
    return view('position-improvements.create');
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
            PositionImprovement::create($request->all());
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
    $item = PositionImprovement::findOrFail($id);
    return view('position-improvements.edit', compact('item'));
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
            $model->update($request->all());
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
