<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LateEarlyRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $items = \App\Models\LateEarlyRecord::all();
    return view('late_early_records.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    return view('late_early_records.create');
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
            \App\Models\LateEarlyRecord::create($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('late_early_records.index')]);
            }
            return redirect()->route('late_early_records.index')->with('success', 'Late/Early record created successfully');
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
    $item = \App\Models\LateEarlyRecord::findOrFail($id);
    return view('late_early_records.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    $item = \App\Models\LateEarlyRecord::findOrFail($id);
    return view('late_early_records.edit', compact('item'));
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
            $model = \App\Models\LateEarlyRecord::findOrFail($id);
            $model->update($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('late_early_records.index')]);
            }
            return redirect()->route('late_early_records.index')->with('success', 'Late/Early record updated successfully');
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
    $item = \App\Models\LateEarlyRecord::findOrFail($id);
    $item->delete();
    return redirect()->route('late_early_records.index')->with('success', 'Late/Early record deleted successfully');
    }
}
