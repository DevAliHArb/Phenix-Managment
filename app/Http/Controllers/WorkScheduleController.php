<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    // Update the single work schedule row (assuming id=1)
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'monday' => 'boolean',
                'tuesday' => 'boolean',
                'wednesday' => 'boolean',
                'thursday' => 'boolean',
                'friday' => 'boolean',
                'saturday' => 'boolean',
                'sunday' => 'boolean',
                'start_time' => 'required',
                'end_time' => 'required',
                'total_hours_per_day' => 'date_format:H:i',
                'late_arrival' => 'required|integer',
                'early_leave' => 'required|integer',
                'vacation_days_per_month' => 'nullable|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => $e->getMessage(),
            ], 422);
        }

        $schedule = WorkSchedule::first();
        if (!$schedule) {
            $schedule = new WorkSchedule();
        }
        // Ensure boolean checkboxes are set to false when not present
        foreach (['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day) {
            if (!array_key_exists($day, $validated)) {
                $validated[$day] = false;
            }
        }

        $schedule->fill($validated);
        $schedule->save();

        return response()->json(['message' => 'Work schedule updated successfully', 'data' => $schedule]);
    }
}
