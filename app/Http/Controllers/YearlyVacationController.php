<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class YearlyVacationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
                $model = new \App\Models\YearlyVacation();
                $model->create($validated);
                if ($request->ajax()) {
                    return response()->json(['success' => true, 'redirect' => route('yearly_vacations.index')]);
                }
                return redirect()->route('yearly_vacations.index')->with('success', 'Yearly vacation created successfully');
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
                $model = \App\Models\YearlyVacation::findOrFail($id);
                $model->update($validated);
                if ($request->ajax()) {
                    return response()->json(['success' => true, 'redirect' => route('yearly_vacations.index')]);
                }
                return redirect()->route('yearly_vacations.index')->with('success', 'Yearly vacation updated successfully');
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
        //
    }
}
