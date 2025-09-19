<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmployeeAttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $items = \App\Models\EmployeeAttachment::all();
    return view('employee_attachments.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    return view('employee_attachments.create');
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
            \App\Models\EmployeeAttachment::create($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('employee_attachments.index')]);
            }
            return redirect()->route('employee_attachments.index')->with('success', 'Attachment created successfully');
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
    $item = \App\Models\EmployeeAttachment::findOrFail($id);
    return view('employee_attachments.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    $item = \App\Models\EmployeeAttachment::findOrFail($id);
    return view('employee_attachments.edit', compact('item'));
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
            $model = \App\Models\EmployeeAttachment::findOrFail($id);
            $model->update($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('employee_attachments.index')]);
            }
            return redirect()->route('employee_attachments.index')->with('success', 'Attachment updated successfully');
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
    $item = \App\Models\EmployeeAttachment::findOrFail($id);
    $item->delete();
    return redirect()->route('employee_attachments.index')->with('success', 'Attachment deleted successfully');
    }
}
