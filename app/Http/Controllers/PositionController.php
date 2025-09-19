<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $items = \App\Models\Lookup::all();
    return view('positions.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    return view('positions.create');
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
            \App\Models\Lookup::create($request->all());
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('positions.index')]);
            }
            return redirect()->route('positions.index')->with('success', 'Position created successfully');
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
    $item = \App\Models\Lookup::findOrFail($id);
    return view('positions.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    $item = \App\Models\Lookup::findOrFail($id);
    return view('positions.edit', compact('item'));
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
            $model = \App\Models\Lookup::findOrFail($id);
            $model->update($request->all());
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('positions.index')]);
            }
            return redirect()->route('positions.index')->with('success', 'Position updated successfully');
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
    $item = \App\Models\Lookup::findOrFail($id);
    $item->delete();
    return redirect()->route('positions.index')->with('success', 'Position deleted successfully');
    }
}
