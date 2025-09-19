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
            $item = Lookup::create($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('lookup.index')]);
            }
            return redirect()->route('lookup.index')->with('success', 'Created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->validator->errors()->all()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                // Add your validation rules here
            ]);
            $item = Lookup::findOrFail($id);
            $item->update($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('lookup.index')]);
            }
            return redirect()->route('lookup.index')->with('success', 'Updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->validator->errors()->all()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
