<?php

namespace App\Http\Controllers;

use App\Models\Lookup;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function index()
    {
        $items = Lookup::all();
        return view('lookup.index', compact('items'));
    }

    public function show($id)
    {
        $item = Lookup::findOrFail($id);
        return view('lookup.show', compact('item'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                // Add your validation rules here
            ]);
            \App\Models\Lookup::create($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('lookup.index')]);
            }
            return redirect()->route('lookup.index')->with('success', 'Lookup created successfully');
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
            $model = \App\Models\Lookup::findOrFail($id);
            $model->update($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('lookup.index')]);
            }
            return redirect()->route('lookup.index')->with('success', 'Lookup updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->validator->errors()->all()], 422);
            }
            throw $e;
        }
    }
}
