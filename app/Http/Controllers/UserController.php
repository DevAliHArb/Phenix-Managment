<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $items = User::all();
        return view('user.index', compact('items'));
    }

    public function show($id)
    {
        $item = User::findOrFail($id);
        return view('user.show', compact('item'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                // Add your validation rules here
            ]);
            $item = User::create($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('user.index')]);
            }
            return redirect()->route('user.index')->with('success', 'Created successfully');
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
            $item = User::findOrFail($id);
            $item->update($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('user.index')]);
            }
            return redirect()->route('user.index')->with('success', 'Updated successfully');
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
