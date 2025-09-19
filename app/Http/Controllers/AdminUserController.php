<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index()
    {
        $items = AdminUser::all();
        return view('adminuser.index', compact('items'));
    }

    public function show($id)
    {
        $item = AdminUser::findOrFail($id);
        return view('adminuser.show', compact('item'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                // Add your validation rules here
            ]);
            $item = AdminUser::create($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('adminuser.index')]);
            }
            return redirect()->route('adminuser.index')->with('success', 'Created successfully');
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
            $item = AdminUser::findOrFail($id);
            $item->update($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('adminuser.index')]);
            }
            return redirect()->route('adminuser.index')->with('success', 'Updated successfully');
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
