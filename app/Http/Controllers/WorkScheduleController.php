<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    // Update the single work schedule row (assuming id=1)
    public function update(Request $request)
    {
        $validated = $request->validate([
            'monday' => 'required|boolean',
            'tuesday' => 'required|boolean',
            'wednesday' => 'required|boolean',
            'thursday' => 'required|boolean',
            'friday' => 'required|boolean',
            'saturday' => 'required|boolean',
            'sunday' => 'required|boolean',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'total_hours_per_day' => 'required|date_format:H:i',
            'late_arrival' => 'required|integer',
            'early_leave' => 'required|integer',
        ]);

        $schedule = WorkSchedule::first();
        if (!$schedule) {
            $schedule = new WorkSchedule();
        }
        $schedule->fill($validated);
        $schedule->save();

        return response()->json(['message' => 'Work schedule updated successfully', 'data' => $schedule]);
    }
}
