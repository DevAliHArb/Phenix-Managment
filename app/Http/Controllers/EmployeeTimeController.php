<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeTime;
use App\Models\VacationDate;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class EmployeeTimeController extends Controller
{
    /**
     * Return import progress for the current session/key
     */
    public function importProgress(Request $request)
    {
        $progressKey = $request->get('progress_key') ?? $request->session()->get('import_progress_key');
        if (!$progressKey) {
            return response()->json(['progress' => 0]);
        }
        $progress = \Cache::get($progressKey, 0);
        return response()->json(['progress' => $progress]);
    }

    /**
     * Export multiple employees' timesheets as individual pages in a single PDF
     */
    public function exportMultipleTimesheets(Request $request)
    {
        $ids = $request->input('ids', []);
        $months = $request->input('months', []);
        $years = $request->input('years', []);
        $year = $request->input('year'); // For backward compatibility
        
        // Handle backward compatibility - if 'month' is provided instead of 'months'
        if (empty($months) && $request->has('month')) {
            $months = [$request->input('month')];
        }
        
        // Handle backward compatibility - if 'year' is provided instead of 'years'
        if (empty($years) && $year) {
            $years = [$year];
        }
        
        if (empty($years)) {
            $years = [Carbon::now()->year];
        }

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'No employee IDs provided'], 400);
        }
        
        if (!is_array($months) || empty($months)) {
            return response()->json(['error' => 'No months provided'], 400);
        }

            // Sort employee IDs in ascending order
            sort($ids);

        $allSheets = [];
        
        // Loop through years first, then employees, then months to organize the data properly
        foreach ($years as $currentYear) {
            foreach ($ids as $employeeId) {
                $employee = Employee::with(['position', 'yearlyVacations', 'employeeVacations'])->find($employeeId);
                if (!$employee) continue;
                
                $department = $employee->position ? $employee->position->name : '';
                
                foreach ($months as $month) {
                    $query = EmployeeTime::where('employee_id', $employeeId)
                        ->whereYear('date', $currentYear)
                        ->whereMonth('date', $month);
                    $times = $query->orderBy('date')->get();

                    $timesheet = $times->map(function ($row) {
                        $isWeekend = $row->vacation_type === 'Off' && $row->reason === 'Weekend';
                        $isVacation = $row->off_day && $row->reason === 'vacation';
                        $status = $row->off_day ? 'Off' : 'Attended';
                        $totalHourscalc = 0;
                        if ($row->total_time) {
                            $parts = explode(':', $row->total_time);
                            $h = isset($parts[0]) ? (int)$parts[0] : 0;
                            $m = isset($parts[1]) ? (int)$parts[1] : 0;
                            $s = isset($parts[2]) ? (int)$parts[2] : 0;
                            $totalHourscalc = round($h + ($m / 60) + ($s / 3600), 2);
                        }
                        $totalHours = $row->total_time ? $row->total_time : 0;
                        $extra = $totalHourscalc - 9;
                        $extraFormatted = ($extra >= 0 ? '+' : '') . number_format($extra, 2);
                        $notes = $row->off_day ? ($row->reason ?: 'Off') : ($row->reason ?: '');
                        return [
                            'date' => $row->date,
                            'timein' => $row->clock_in,
                            'timeout' => $row->clock_out,
                            'totalhours' => $totalHours,
                            'totalhourscalc' => $totalHourscalc,
                            'status' => $status,
                            'extra' => $extraFormatted,
                            'notes' => $notes,
                            'is_weekend' => $isWeekend,
                            'vacation' => $isVacation,
                            'dayoff' => $row->off_day,
                        ];
                    });

                    // Calculate attendanceRequired for this employee/month/year
                    $workSchedule = \App\Models\WorkSchedule::first();
                    $vacationDates = \App\Models\VacationDate::whereYear('date', $currentYear)->whereMonth('date', $month)->pluck('date')->toArray();
                    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $currentYear);
                    $attendanceRequiredCount = 0;
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $date = sprintf('%04d-%02d-%02d', $currentYear, $month, $day);
                        $carbon = \Carbon\Carbon::parse($date);
                        $weekday = strtolower($carbon->format('l'));
                        if ($workSchedule && $workSchedule->$weekday) {
                            $attendanceRequiredCount++;
                        }
                    }

                    $dailyHoursRequired = null;
                    if ($workSchedule && $workSchedule->total_hours_per_day) {
                        $parts = explode(':', $workSchedule->total_hours_per_day);
                        $h = isset($parts[0]) ? (int)$parts[0] : 0;
                        $m = isset($parts[1]) ? (int)$parts[1] : 0;
                        $dailyHoursRequired = sprintf('%d:%02d', $h, $m);
                    }

                    // Get employee vacations for the month
                    $vacations = $employee->employeeVacations()
                        ->where('lookup_type_id', 31)
                        ->whereYear('date', $currentYear)
                        ->whereMonth('date', $month)
                        ->pluck('date')
                        ->toArray();

                    $unpaid = $employee->employeeVacations()
                        ->where('lookup_type_id', 34)
                        ->whereYear('date', $currentYear)
                        ->whereMonth('date', $month)
                        ->pluck('date')
                        ->toArray();

                    $sickleave = $employee->employeeVacations()
                        ->where('lookup_type_id', 32)
                        ->whereYear('date', $currentYear)
                        ->whereMonth('date', $month)
                        ->pluck('date')
                        ->toArray();

                        
                    $halfday = $employee->employeeVacations()
                        ->where('lookup_type_id', 35)
                        ->whereYear('date', $currentYear)
                        ->whereMonth('date', $month)
                        ->pluck('date')
                        ->toArray();

                    $offDays = $vacationDates;

                    $allSheets[] = [
                        'department' => $department,
                        'employee' => (object)[
                            'name' => $employee->first_name . ' ' . $employee->mid_name . ' ' . $employee->last_name,
                        ],
                        'timesheet' => $timesheet,
                        'times' => $times,
                        'month' => $month,
                        'year' => $currentYear,
                        'attendanceRequired' => $attendanceRequiredCount,
                        'dailyhoursrequired' => $dailyHoursRequired,
                        'vacations' => $vacations,
                        'sickleave' => $sickleave,
                        'offdays' => $offDays,
                        'unpaid' => $unpaid,
                        'halfday' => $halfday,
                    ];
                }
            }
        }

        if (empty($allSheets)) {
            return response()->json(['error' => 'No valid employees found'], 400);
        }

        // Generate a filename based on the number of employees, months and years
        $employeeCount = count($ids);
        $monthCount = count($months);
        $yearCount = count($years);
        $monthsText = $monthCount === 12 ? 'all_months' : implode('_', $months);
        $yearsText = $yearCount === 1 ? $years[0] : implode('_', $years);
        $filename = 'multiple_timesheets_' . $employeeCount . '_employees_' . $monthsText . '_' . $yearsText . '.pdf';
        
        // Render a single PDF with multiple sheets (pages) - each employee+month+year gets their own page
        $pdf = Pdf::loadView('export.timesheet_multiple', [
            'sheets' => $allSheets,
            'months' => $months,
            'years' => $years,
        ]);
        
        return $pdf->download($filename);
    }
    /**
     * Export employee timesheet as PDF
     */
    public function exportTimesheet(Request $request, $employeeId)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        // Default to current month/year if not provided
        if (!$month || !$year) {
            $now = Carbon::now();
            $month = $month ?: $now->month;
            $year = $year ?: $now->year;
        }
        $employee = Employee::with(['position', 'yearlyVacations', 'employeeVacations'])->findOrFail($employeeId);
        $department = $employee->position ? $employee->position->name : '';
        $query = EmployeeTime::where('employee_id', $employeeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month);
        $times = $query->orderBy('date')->get();

        $timesheet = $times->map(function ($row) {
                $isWeekend = $row->vacation_type === 'Off' && $row->reason === 'Weekend';
            $isVacation = $row->off_day && $row->reason === 'vacation';
            $status = $row->off_day ? 'Off' : 'Attended';
            // Parse total_time as H:i:s string to decimal hours
            $totalHourscalc = 0;
            if ($row->total_time) {
                $parts = explode(':', $row->total_time);
                $h = isset($parts[0]) ? (int)$parts[0] : 0;
                $m = isset($parts[1]) ? (int)$parts[1] : 0;
                $s = isset($parts[2]) ? (int)$parts[2] : 0;
                $totalHourscalc = round($h + ($m / 60) + ($s / 3600), 2);
            }
            $totalHours = 0;
            if ($row->total_time) {
                $totalHours = $row->total_time;
            }
            $extra = $totalHourscalc - 9;
            $extraFormatted = ($extra >= 0 ? '+' : '') . number_format($extra, 2);
            $notes = $row->off_day ? ($row->reason ?: 'Off') : ($row->reason ?: '');
            return [
                'date' => $row->date,
                'timein' => $row->clock_in,
                'timeout' => $row->clock_out,
                'totalhours' => $totalHours,
                'totalhourscalc' => $totalHourscalc,
                'status' => $status,
                'extra' => $extraFormatted,
                'notes' => $notes,
                'is_weekend' => $isWeekend,
                'vacation' => $isVacation,
                'dayoff' => $row->off_day,
            ];
        });

        // Calculate attendanceRequired for this employee/month/year
        $workSchedule = \App\Models\WorkSchedule::first();
        $vacationDates = \App\Models\VacationDate::whereYear('date', $year)->whereMonth('date', $month)->pluck('date')->toArray();
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $attendanceRequiredCount = 0;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $carbon = \Carbon\Carbon::parse($date);
            $weekday = strtolower($carbon->format('l'));
            if ($workSchedule && $workSchedule->$weekday) {
                // if (in_array($date, $vacationDates)) {
                //     continue;
                // }
                // $hasYearlyVacation = $employee->yearlyVacations()->whereDate('date', $date)->exists();
                // if ($hasYearlyVacation) {
                //     continue;
                // }
                $attendanceRequiredCount++;
            }
        }

        // Get dailyhoursrequired from work schedule (format as hr:min string)
        $dailyHoursRequired = null;
        if ($workSchedule && $workSchedule->total_hours_per_day) {
            $parts = explode(':', $workSchedule->total_hours_per_day);
            $h = isset($parts[0]) ? (int)$parts[0] : 0;
            $m = isset($parts[1]) ? (int)$parts[1] : 0;
            $dailyHoursRequired = sprintf('%d:%02d', $h, $m);
        }

        // Get employee vacations (lookup_type_id = 31) for the month
        $vacations = $employee->employeeVacations()
            ->where('lookup_type_id', 31)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('date')
            ->toArray();

        // Get employee unpaid (lookup_type_id = 34) for the month
        $unpaid = $employee->employeeVacations()
            ->where('lookup_type_id', 34)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('date')
            ->toArray();

        // Get employee sickleave (lookup_type_id = 32) for the month
        $sickleave = $employee->employeeVacations()
            ->where('lookup_type_id', 32)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('date')
            ->toArray();

        $halfday = $employee->employeeVacations()
            ->where('lookup_type_id', 35)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('date')
            ->toArray();
        // Off days (vacation dates for the month)
        $offDays = $vacationDates;

        $data = [
            'department' => $department,
            'employee' => (object)[
                'name' => $employee->first_name . ' ' . $employee->mid_name . ' ' . $employee->last_name,
            ],
            'timesheet' => $timesheet,
            'times' => $times,
            'month' => $month,
            'year' => $year,
            'attendanceRequired' => $attendanceRequiredCount,
            'dailyhoursrequired' => $dailyHoursRequired,
            'vacations' => $vacations,
            'sickleave' => $sickleave,
            'offdays' => $offDays,
            'unpaid' => $unpaid,
            'halfday' => $halfday,
        ];

    $empName = str_replace(' ', '_', $employee->first_name . ' ' . $employee->mid_name . ' ' . $employee->last_name);
    $filename = $empName . '_timesheet_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf';
    $pdf = Pdf::loadView('export.timesheet', $data);
    return $pdf->download($filename);
    }
    public function index()
    {
        $employeeTimes = EmployeeTime::with('employee')->get();
        $employees = Employee::where('status', 'active')->get();
        return view('employee_times.index', compact('employeeTimes', 'employees'));
    }

    public function show($id)
    {
        $employeeTime = EmployeeTime::with('employee')->findOrFail($id);
        return view('employee_times.show', compact('employeeTime'));
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->get();
        $vacationDates = VacationDate::orderBy('date')->get();
        return view('employee_times.create', compact('employees', 'vacationDates'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_ids' => 'required_without:employee_id|array|min:1',
                'employee_ids.*' => 'exists:employees,id',
                'employee_id' => 'required_without:employee_ids|exists:employees,id',
                'date' => 'required|date',
                'clock_in' => 'nullable',
                'clock_out' => 'nullable',
                'total_time' => 'nullable',
                'off_day' => 'nullable|boolean',
                'reason' => 'nullable|string',
                'reason_select' => 'nullable|string',
                'vacation_type' => 'nullable|string',
            ]);

            // Normalize to an array so we can create one record per employee when multiple are selected
            $employeeIds = $validated['employee_ids'] ?? [$validated['employee_id']];

            // Build the shared payload once (reason_select overrides the free-text reason when present)
            $reason = $validated['reason_select'] ?? ($validated['reason'] ?? null);
            $baseData = [
                'date' => $validated['date'],
                'clock_in' => $validated['clock_in'] ?? null,
                'clock_out' => $validated['clock_out'] ?? null,
                'total_time' => $validated['total_time'] ?? null,
                'vacation_type' => $validated['vacation_type'] ?? null,
                'reason' => $reason,
            ];

            // Set off_day to true if vacation_type is Off, Vacation, Holiday, or Sick Leave
            $offDayTypes = ['Off', 'Vacation', 'Holiday', 'Sick Leave'];
            $baseData['off_day'] = in_array($baseData['vacation_type'], $offDayTypes)
                ? true
                : $request->boolean('off_day');

            foreach ($employeeIds as $employeeId) {
                EmployeeTime::create($baseData + ['employee_id' => $employeeId]);
            }
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('employee_times.index')]);
            }
            return redirect()->route('employee_times.index')->with('success', 'Time log created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->validator->errors()->all()], 422);
            }
            throw $e;
        }
    }

    public function edit($id)
    {
        $employeeTime = EmployeeTime::findOrFail($id);
        $employees = Employee::all();
        $vacationDates = VacationDate::orderBy('date')->get();
        return view('employee_times.edit', compact('employeeTime', 'employees', 'vacationDates'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'date' => 'sometimes|date',
                'clock_in' => 'sometimes',
                'clock_out' => 'nullable',
                'total_time' => 'nullable',
                'off_day' => 'nullable|boolean',
                'reason' => 'nullable|string',
                'vacation_type' => 'nullable|string',
            ]);

            // Set off_day to true if vacation_type is Off, Vacation, Holiday, or Sick Leave
            $offDayTypes = ['Off', 'Vacation', 'Holiday', 'Sick Leave'];
            if (in_array($validated['vacation_type'] ?? null, $offDayTypes)) {
                $validated['off_day'] = true;
            } else {
                $validated['off_day'] = $request->has('off_day');
            }
            $model = EmployeeTime::findOrFail($id);
            $model->update($validated);
            if ($request->ajax()) {
                return response()->json(['success' => true, 'redirect' => route('employee_times.index')]);
            }
            return redirect()->route('employee_times.index')->with('success', 'Time log updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->validator->errors()->all()], 422);
            }
            throw $e;
        }
    }

    public function destroy($id)
    {
        $employeeTime = EmployeeTime::findOrFail($id);
        $employeeTime->delete();
        return redirect()->route('employee_times.index')->with('success', 'Time log deleted successfully');
    }
    
    public function importExcel(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            // Generate a unique progress key for this import (per user/session)
            $progressKey = 'import_progress_' . ($request->user() ? $request->user()->id : $request->ip()) . '_' . uniqid();
            $request->session()->put('import_progress_key', $progressKey);
            \Cache::put($progressKey, 0, 600);
            $import = new \App\Imports\EmployeeTimeImport($progressKey);
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('excel_file'));

            return response()->json([
                'status' => 'success',
                'message' => 'Punch Time import completed',
                'progress_key' => $progressKey
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

        /**
     * Calculate attendanceRequired for each employee for a given month and year
     */
    public function attendanceRequired(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        if (!$month || !$year) {
            $now = Carbon::now();
            $month = $month ?: $now->month;
            $year = $year ?: $now->year;
        }

        $workSchedule = WorkSchedule::first();
        $vacationDates = VacationDate::whereYear('date', $year)->whereMonth('date', $month)->pluck('date')->toArray();
        $employees = Employee::all();
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $results = [];
        foreach ($employees as $employee) {
            $attendanceRequired = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $carbon = Carbon::parse($date);
                $weekday = strtolower($carbon->format('l'));
                // Check if this day is a work day (not weekend)
                if ($workSchedule && $workSchedule->$weekday) {
                    // Check if this date is a global vacation
                    if (in_array($date, $vacationDates)) {
                        $attendanceRequired[$date] = false;
                        continue;
                    }
                    // Check if this employee has a yearly vacation on this date
                    $hasYearlyVacation = $employee->yearlyVacations()->whereDate('date', $date)->exists();
                    if ($hasYearlyVacation) {
                        $attendanceRequired[$date] = false;
                        continue;
                    }
                    $attendanceRequired[$date] = true;
                } else {
                    $attendanceRequired[$date] = false;
                }
            }
            $results[$employee->id] = $attendanceRequired;
        }
        return response()->json($results);
    }

    /**
     * Bulk update employee time records
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:employee_times,id',
        ]);

        $ids = $request->input('ids');
        
        // Check if clock_in or clock_out is being updated
        $updatingTimes = $request->filled('clock_in') || $request->filled('clock_out');
        
        try {
            if ($updatingTimes) {
                // If updating clock times, we need to update each record individually to recalculate total_time
                $records = EmployeeTime::whereIn('id', $ids)->get();
                
                foreach ($records as $record) {
                    $clockIn = $request->filled('clock_in') ? $request->input('clock_in') : $record->clock_in;
                    $clockOut = $request->filled('clock_out') ? $request->input('clock_out') : $record->clock_out;
                    
                    // Calculate total_time if both clock_in and clock_out are present
                    $totalTime = null;
                    if ($clockIn && $clockOut) {
                        try {
                            $startTime = Carbon::parse($clockIn);
                            $endTime = Carbon::parse($clockOut);
                            
                            // If end time is before start time, assume it's next day
                            if ($endTime->lt($startTime)) {
                                $endTime->addDay();
                            }
                            
                            $diffInSeconds = $endTime->diffInSeconds($startTime);
                            $hours = floor($diffInSeconds / 3600);
                            $minutes = floor(($diffInSeconds % 3600) / 60);
                            $seconds = $diffInSeconds % 60;
                            
                            $totalTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                        } catch (\Exception $e) {
                            // If time parsing fails, keep the existing total_time
                            $totalTime = $record->total_time;
                        }
                    }
                    
                    // Update the record
                    $updateData = [];
                    if ($request->filled('clock_in')) {
                        $updateData['clock_in'] = $clockIn;
                    }
                    if ($request->filled('clock_out')) {
                        $updateData['clock_out'] = $clockOut;
                    }
                    if ($totalTime !== null) {
                        $updateData['total_time'] = $totalTime;
                    }
                    if ($request->filled('vacation_type')) {
                        $updateData['vacation_type'] = $request->input('vacation_type');
                        if ($request->input('vacation_type') !== 'Attended') {
                            $updateData['off_day'] = true;
                        } else {
                            $updateData['off_day'] = false;
                        }
                    }
                    if ($request->boolean('clear_reason')) {
                        $updateData['reason'] = '';
                    } elseif ($request->filled('reason')) {
                        $updateData['reason'] = $request->input('reason');
                    }
                    
                    if (!empty($updateData)) {
                        $record->update($updateData);
                    }
                }
            } else {
                // No time updates, use bulk update for efficiency
                $updateData = [];
                
                if ($request->filled('total_time')) {
                    $updateData['total_time'] = $request->input('total_time');
                }
                if ($request->filled('vacation_type')) {
                    $updateData['vacation_type'] = $request->input('vacation_type');
                    if ($request->input('vacation_type') !== 'Attended') {
                        $updateData['off_day'] = true;
                    } else {
                        $updateData['off_day'] = false;
                    }
                }
                if ($request->boolean('clear_reason')) {
                    $updateData['reason'] = '';
                } elseif ($request->filled('reason')) {
                    $updateData['reason'] = $request->input('reason');
                }

                if (empty($updateData)) {
                    return response()->json([
                        'message' => 'No fields to update'
                    ], 400);
                }
                
                EmployeeTime::whereIn('id', $ids)->update($updateData);
            }

            return response()->json([
                'message' => count($ids) . ' record(s) updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk add employee time records for multiple employees
     */
    public function bulkAdd(Request $request)
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'date' => 'required|date',
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'vacation_type' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        try {
            $employeeIds = $validated['employee_ids'];
            $recordsCreated = 0;

            // Build the shared data
            $clockIn = $validated['clock_in'] ?? null;
            $clockOut = $validated['clock_out'] ?? null;
            
            // Calculate total_time if both times are present
            $totalTime = null;
            if ($clockIn && $clockOut) {
                try {
                    $startTime = Carbon::parse($clockIn);
                    $endTime = Carbon::parse($clockOut);
                    
                    // If end time is before start time, assume it's next day
                    if ($endTime->lt($startTime)) {
                        $endTime->addDay();
                    }
                    
                    $diffInSeconds = $endTime->diffInSeconds($startTime);
                    $hours = floor($diffInSeconds / 3600);
                    $minutes = floor(($diffInSeconds % 3600) / 60);
                    $seconds = $diffInSeconds % 60;
                    
                    $totalTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                } catch (\Exception $e) {
                    // If time parsing fails, leave total_time as null
                }
            }

            // Determine off_day based on vacation_type
            $offDay = false;
            $offDayTypes = ['Off', 'Vacation', 'Holiday', 'Sick Leave', 'Unpaid', 'Half Day Vacation'];
            if (isset($validated['vacation_type']) && in_array($validated['vacation_type'], $offDayTypes)) {
                $offDay = true;
            }
            // Create a record for each employee
            foreach ($employeeIds as $employeeId) {
                // Check if a record already exists for this employee and date
                $existingRecord = EmployeeTime::where('employee_id', $employeeId)
                    ->where('date', $validated['date'])
                    ->first();
                
                if ($existingRecord) {
                    // Skip this employee if record already exists
                    continue;
                }
                
                EmployeeTime::create([
                    'employee_id' => $employeeId,
                    'date' => $validated['date'],
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'total_time' => $totalTime,
                    'vacation_type' => $validated['vacation_type'] ?? null,
                    'reason' => $validated['reason'] ?? null,
                    'off_day' => $offDay,
                ]);
                $recordsCreated++;
            }

            return response()->json([
                'message' => $recordsCreated . ' record(s) added successfully for ' . count($employeeIds) . ' employee(s)!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add records: ' . $e->getMessage()
            ], 500);
        }
    }
}

