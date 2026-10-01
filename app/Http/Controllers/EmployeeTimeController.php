<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeTime;
use App\Models\MachineRecord;
use App\Models\MachineSettings;
use App\Models\MachineSyncData;
use App\Models\VacationDate;
use App\Models\WorkSchedule;
use App\Models\Lookup;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Rats\Zkteco\Lib\ZKTeco;
use function Safe\session_write_close;
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
        $machineSettings = MachineSettings::orderBy('created_at','desc')->first();
        return view('employee_times.index', compact('employeeTimes', 'employees' , 'machineSettings'));
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
        $workSchedule = WorkSchedule::orderBy('created_at', 'desc')->first();
        return view('employee_times.create', compact('employees', 'vacationDates', 'workSchedule'));
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
                 'flagged' => 'in:0,1',
                'total_break_diff' =>'regex:/^\d{2}:\d{2}:\d{2}$/',
                'total_leave_diff' =>'regex:/^\d{2}:\d{2}:\d{2}$/',
                'break_flag' => 'in:Only In,Only Out,pass,No Break,'
            ]);
            
            // Normalize flagged to a boolean (hidden input sends 0 when unchecked)
            $flagged = (bool) $request->input('flagged', 0);

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
                'flagged' => $flagged,
                'total_break_diff' => $validated['total_break_diff'] ?? null,
                'total_leave_diff' => $validated['total_leave_diff'] ?? null,
                'break_flag' => $validated['break_flag'] ?: 'pass'
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
        $events = MachineRecord::with('eventType')->where('employee_time_id',$id)->orderBy('timestamp')->get();
        $employees = Employee::all();
        $vacationDates = VacationDate::orderBy('date')->get();
        $workSchedule = WorkSchedule::orderBy('created_at', 'desc')->first();
        return view('employee_times.edit', compact('employeeTime', 'employees', 'vacationDates' , 'events', 'workSchedule'));
    }

    public function update(Request $request, $id)
    {
        try {
             // Normalize manually entered diff times to HH:MM:SS (e.g. 1:1 -> 01:01:00)
            foreach (['total_break_diff', 'total_leave_diff', 'total_time'] as $field) {
                if ($request->filled($field)) {
                    $request->merge([$field => $this->normalizeTimeToHms($request->input($field))]);
                }
            }
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'date' => 'sometimes|date',
                'clock_in' => 'sometimes',
                'clock_out' => 'nullable',
                'total_time' => 'nullable',
                'off_day' => 'nullable|boolean',
                'reason' => 'nullable|string',
                'vacation_type' => 'nullable|string',
                'flagged'=> 'in:0,1',
                'total_break_diff' => 'regex:/^\d{2}:\d{2}:\d{2}$/',
                'total_leave_diff' => 'regex:/^\d{2}:\d{2}:\d{2}$/',
                'break_flag' => 'in:Only In,Only Out,pass,No Break,',
            ]);

             // Normalize flagged to a boolean (hidden input sends 0 when unchecked)
            $validated['flagged'] = (bool) $request->input('flagged', 0);

            $model = EmployeeTime::findOrFail($id);


            $submittedBreakDiff = $request->input('total_break_diff', '00:00:00');
            $submittedLeaveDiff = $request->input('total_leave_diff', '00:00:00');
            

            $validated['total_break_diff'] = $submittedBreakDiff;
            $validated['total_leave_diff'] = $submittedLeaveDiff;
               

            // If break_flag is empty, default to 'pass'
            $validated['break_flag'] = $validated['break_flag'] ?? 'pass';

            // Set off_day to true if vacation_type is Off, Vacation, Holiday, or Sick Leave
            $offDayTypes = ['Off', 'Vacation', 'Holiday', 'Sick Leave'];
            if (in_array($validated['vacation_type'] ?? null, $offDayTypes)) {
                $validated['off_day'] = true;
            } else {
                $validated['off_day'] = $request->has('off_day');
            }
            
            // Recalculate total_time : (last clock out - first clock in) - allowed break -
            // break diff - leave diff, subtracting the diff values saved above (the manual
            // edits when the user changed them, otherwise the kept/computed values). The
            // allowed break is deducted only when break_flag is 'pass'.
            $validated['total_time'] = $this->recalculateTotalTime(
                $validated['clock_in'] ?? $model->clock_in,
                $validated['clock_out'] ?? $model->clock_out,
                $validated['date'] ?? $model->date,
                $validated['total_break_diff'],
                $validated['total_leave_diff'],
                $validated['break_flag'] ?? $model->break_flag
            );

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

   /**
     * Normalize a user-entered time string to HH:MM:SS.
     *  1:1    -> 01:01:00
     *  01:02  -> 01:02:00
     *  1:2:3  -> 01:02:03
     */
    private function normalizeTimeToHms($value)
    {
        if (!$value) {
            return $value;
        }
        $value = trim((string) $value);
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
            return $value;
        }
        $parts = array_pad(preg_split('/:/', $value), 3, '0');
        $h = (int) $parts[0];
        $i = (int) $parts[1];
        $s = (int) ($parts[2] ?? 0);
        return sprintf('%02d:%02d:%02d', $h, $i, $s);
    }

    /**
     * Convert an HH:MM:SS duration string to seconds.
     */
    private function hmsToSeconds($value)
    {
        if (!$value) {
            return 0;
        }
        $parts = explode(':', (string) $value);
        return ((int) ($parts[0] ?? 0)) * 3600
            + ((int) ($parts[1] ?? 0)) * 60
            + (int) ($parts[2] ?? 0);
    }

    /**
     * Recalculate total_time as :
     *   (clock out - clock in) - break diff - leave diff
     * The allowed break (from the work schedule, default 1 hour) is deducted only when
     * break_flag is 'pass'. The result is clamped at 0 and formatted as HH:MM:SS.
     */
    private function recalculateTotalTime($clockIn, $clockOut, $date, $breakDiff, $leaveDiff, $breakFlag = null)
    {
        $zero = '00:00:00';
        if (!$clockIn || !$clockOut) {
            return $zero;
        }
        try {
            $start = Carbon::parse($date . ' ' . $clockIn);
            $end = Carbon::parse($date . ' ' . $clockOut);
            if ($end->lt($start)) {
                $end->addDay();
            }
            $grossSeconds = max(0, $end->diffInSeconds($start));
        } catch (\Exception $e) {
            return $zero;
        }

        // allowed break (default 1 hour when the schedule doesn't define one) is
        // deducted only when break_flag is 'pass' - all other flags ('Only In',
        // 'Only Out', 'No Break', empty...) leave it out of the total time.
        $allowedBreakSeconds = 0;
        if ($breakFlag === 'pass') {
            $allowedBreakSeconds = 3600;
            $workSchedule = WorkSchedule::orderBy('created_at', 'desc')->first();
            if ($workSchedule && $workSchedule->break_duration) {
                $allowedBreakSeconds = ((int) $workSchedule->break_duration) * 60;
            }
        }

        $totalSeconds = max(0,
            $grossSeconds
            - $allowedBreakSeconds
            - $this->hmsToSeconds($breakDiff)
            - $this->hmsToSeconds($leaveDiff)
        );

        return gmdate('H:i:s', $totalSeconds);
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

    private function sseEvent(string $event, array $data): string
    {
        return "event: {$event}\n"
            . "data: " . json_encode($data) . "\n\n";
    }

    //imports machine records and saves them in the DB
    public function ImportMachineRecords(Request $request){

        return response()->stream(function() use ($request) {
            try {

                ob_implicit_flush(true);

                echo $this->sseEvent('progress', [
                    'progress' => 0,
                    'message' => 'Importing records from machine ...'
                ]);
                flush();

                $machineSettings = MachineSettings::orderBy('created_at','desc')->first();
                $ip = $machineSettings?->ip;
                $port = $machineSettings?->port;
                $timeout = ['sec' => (int) ($machineSettings->timeout ?? config('zkteco.timeout', 2)), 'usec' => 0];
                if(!$ip || !$port)
                    throw new Exception('No Current Attendance Machine Configuration Found.Please Update your machine settings');
                $machine = new ZKTeco($ip, $port);
                
                //modify socket timeout settings
                socket_set_option($machine->_zkclient, SOL_SOCKET, SO_RCVTIMEO, $timeout);
                socket_set_option($machine->_zkclient, SOL_SOCKET, SO_SNDTIMEO, $timeout);
                
                
                $machine->connect();   
                $machine->disableDevice();
                $attendance = $machine->getAttendance();
                $machine->enableDevice();
                $machine->disconnect();
                
                $importedEvents = collect($attendance);
                $recordsCount = $importedEvents->count();

                echo $this->sseEvent('progress', [
                    'progress' => 5,
                    'message' => 'Imported ' . $recordsCount . ' record(s). Creating new records ...',
                    'total' => $recordsCount
                ]);
                flush();

                if ($recordsCount === 0) {
                    echo $this->sseEvent('progress', [
                        'progress' => 100,
                        'message' => 'No records found.'
                    ]);
                    flush();
                }
                
                $recordsAdded = 0;
                $invalidRecords =0;
                $flaggedRecords =[];
                $duplicateRecords =[];
                $oldRows = MachineRecord::count();
                $totalRows = $oldRows;
                $latestRecordData = MachineSyncData::orderBy('created_at' , 'desc')->first();

                //the whole process fails and reverts on error
                $error = null;
                try {
                    DB::transaction(function() use($importedEvents,&$recordsAdded , &$flaggedRecords , &$invalidRecords , &$duplicateRecords , &$totalRows , &$latestRecordData , $recordsCount){
                        if($importedEvents->count() > 0){
                            $lastSyncedRecord = MachineSyncData::orderBy('created_at', 'desc')->first();
                            $syncCycle = null;
                            $lastInsertedEvent = null;

                            $lookup = Lookup::all();

                            foreach($importedEvents as $index => $event){

                                $loginMethodCode = $lookup->where('parent_id', config('zkteco.loginMethodStart'))->where('code', (string) $event['state'])->first();
                                $eventTypeCode = $lookup->where('parent_id', config('zkteco.eventTypeStart'))->where('code', (string) $event['type'])->first();

                                $loginMethod = $loginMethodCode? $loginMethodCode->id :config('zkteco.undefinedLoginMethod');
                                $eventType = $eventTypeCode? $eventTypeCode->id :config('zkteco.undefinedEvent');

                                $shouldInsert = false;

                                //checks the machine_id and timestamp with the last saved ones
                                if($lastSyncedRecord)
                                    {
                                        if ($event['uid'] == $lastSyncedRecord -> last_machine_id  && $event['timestamp'] == $lastSyncedRecord->last_attendance_date ) {
                                            continue;
                                        }
                                        elseif($event['uid'] > $lastSyncedRecord -> last_machine_id  && $event['timestamp'] >= $lastSyncedRecord->last_attendance_date  ){
                                            $shouldInsert = true;
                                        }
                                        elseif($event['uid'] <= $lastSyncedRecord -> last_machine_id  && $event['timestamp'] > $lastSyncedRecord->last_attendance_date  ){
                                                $flaggedRecords[]=$event;                                    
                                        }
                                        elseif($event['uid'] >= $lastSyncedRecord -> last_machine_id  && $event['timestamp'] < $lastSyncedRecord->last_attendance_date  ){
                                            $invalidRecords +=1;
                                        }
                                }
                                else {
                                    $shouldInsert = true;
                                }

                                if($shouldInsert){
                                    //creates a new record on the first iteration of the loop
                                    if($syncCycle === null){
                                        $syncCycle = MachineSyncData::create([
                                            'last_machine_id'=>0,
                                            'last_attendance_date'=>'1970-01-01 00:00:00',
                                            'created_by'=>'0',
                                            'updated_by'=>'0'
                                        ])->id;
                                    }

                                    //inserts a new record 
                                    MachineRecord::create([
                                                'machine_id'=>$event['uid'],
                                                'emp_id'=> $event['id'],
                                                'login_via'=>$loginMethod,
                                                'event_type_id'=>$eventType,
                                                'timestamp'=>$event['timestamp'],
                                                'sync_cycle' => $syncCycle,
                                                ]);
                                            $recordsAdded +=1;

                                    
                                    //saves the last inserted event
                                    if($lastInsertedEvent === null || $event['uid'] > $lastInsertedEvent['uid']){
                                        $lastInsertedEvent = $event;
                                    }
                                }

                                if($index % 10==0){
                                    // Progress tracks every event, including ignored and flagged rows.
                                    $progress = round((($index + 1) / $recordsCount) * 100);
                                    echo $this->sseEvent('progress', [
                                        'progress' => $progress,
                                        'message' => $shouldInsert
                                            ? 'Saved record ' . ($index + 1) . ' of ' . $recordsCount
                                            : 'Processed record ' . ($index + 1) . ' of ' . $recordsCount,
                                        'total' => $recordsCount,
                                        'added' => $recordsAdded
                                    ]);
                                    flush();
                                }
                            }

                            //if anything got inserted , insert the last date and machine id , and update the staticstics in the machine sync data 
                            if($syncCycle !== null && $lastInsertedEvent !== null){
                                MachineSyncData::where('id', $syncCycle)->update([
                                    'last_machine_id'=>$lastInsertedEvent['uid'],
                                    'last_attendance_date'=>$lastInsertedEvent['timestamp'],
                                    'added' => $recordsAdded,
                                    'ignored' => $invalidRecords,
                                    'flagged' => count($flaggedRecords)
                                ]);
                            }

                        }
                        $totalRows = MachineRecord::count();
                    });
                } catch (\Throwable $ex) {
                    $error = $ex;
                }

                if ($error) {
                    echo $this->sseEvent('error', [
                        'message' => $error->getMessage()
                    ]);
                    flush();
                    return;
                }

                echo $this->sseEvent('done', [
                    'success' => true,
                    'recordsAdded' => $recordsAdded,
                    'invalidRecords' => $invalidRecords,
                    'flaggedRecords' => $flaggedRecords,
                    'totalRows' => $totalRows, 
                    'oldRows' => $oldRows,
                    'latestRecord' => $latestRecordData ? $latestRecordData->only(['last_machine_id' , 'last_attendance_date']) : null
                ]);
                flush();

            } catch (\Throwable $e) {
                echo $this->sseEvent('error', [
                    'message' => $e->getMessage()
                ]);
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Encoding' => 'none',
        ]);
    }
    
    //resolves conflicted records by getting the conflicting (by machine-id) new records and adds overwrites the old records
    public function resolveConflictedRecords(Request $request){
        try{
        //get the attendance records from the machine

        $machineSettings = MachineSettings::orderBy('created_at','desc')->first();
        $ip = $machineSettings?->ip;
        $port = $machineSettings?->port;
        $timeout = ['sec' => (int) ($machineSettings->timeout ?? config('zkteco.timeout', 2)), 'usec' => 0];
        
        if(!$ip || !$port)
            throw new Exception('No Current Attendance Machine Configuration Found.Please Update your machine settings');
        $machine = new ZKTeco($ip, $port);
        
        //modify socket timeout settings
        socket_set_option($machine->_zkclient, SOL_SOCKET, SO_RCVTIMEO, $timeout);
        socket_set_option($machine->_zkclient, SOL_SOCKET, SO_SNDTIMEO, $timeout);

        $machine->connect();
        $machine->disableDevice();
        $attendance = $machine->getAttendance();
        $machine->enableDevice();
        $machine->disconnect();

    
        $lookup = Lookup::all();
        $importedEvents = collect($attendance);
        
        //get the duplicatedEvents from the machine records
        $duplicateEvents = $importedEvents->whereIn('uid', $request->conflictIds);
        $recordsModified = 0;

        $lastMachineSyncData = MachineSyncData::orderBy('created_at','desc')->first();
        
        //the records are overwritten one by one -> with any error reverting the whole operation
        DB::transaction(function() use(&$duplicateEvents, $lookup, &$recordsModified, $lastMachineSyncData){
            if (!$lastMachineSyncData) {
                throw new \Exception('no machine sync data available');
            }

            //creates a new machine sync data to save the new insertions with
            $syncCycle = MachineSyncData::create([
                'last_machine_id' => $lastMachineSyncData->last_machine_id,
                'last_attendance_date' => $lastMachineSyncData->last_attendance_date,
                'created_by'=>'0',
                'updated_by'=>'0',
                'flagged' => null,
                'added' => null
            ]);


            foreach($duplicateEvents as $event){

                //gets events codes (clock in , out ...)
                $loginMethodCode = $lookup->where('parent_id', config('zkteco.loginMethodStart'))->where('code', (string) $event['state'])->first();
                $eventTypeCode = $lookup->where('parent_id', config('zkteco.eventTypeStart'))->where('code', (string) $event['type'])->first();

                $loginMethod = $loginMethodCode? $loginMethodCode->id :config('zkteco.undefinedLoginMethod');
                $eventType = $eventTypeCode? $eventTypeCode->id :config('zkteco.undefinedEvent');
                
                //inserts or overwrites the old records (if no old one then just insert)
                MachineRecord::updateOrCreate(
                    ['machine_id' => $event['uid']],
                    [
                        'emp_id' => $event['id'],
                        'login_via' => $loginMethod,
                        'event_type_id'=> $eventType,
                        'timestamp' => $event['timestamp'],
                        'sync_cycle' => $syncCycle->id,
                        'calculated' => false,
                        'employee_time_id' => null
                    ]
                );
                $recordsModified += 1;
            }

            //updates the sync machine data's added count 
            $syncCycle->update([
                'added' => $recordsModified
            ]);
        });
        if ($request->ajax()) {
           
                return response()->json([
                    'success' => true,
                    'recordsModified' => $recordsModified
                ]);
            
            }
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }


         
    }

    //Adds the sync flagged records or overwrites the conflicting ones
    public function addFlaggedRecord(Request $request){
        try{
        $event = $request->event;
        $lookup = Lookup::all();
        $lastMachineSyncData = MachineSyncData::orderBy('created_at','desc')->first();
        if (empty($event) || !isset($event['uid'])) {
            throw new \Exception('no event data provided');
        }

        //Adds or Overwrites , alos creates a new machine sync data record , reverts everything if it failed
        DB::transaction(function() use($lookup, $lastMachineSyncData ,&$event){
            if (!$lastMachineSyncData) {
                throw new \Exception('no machine sync data available');
            }
            //machine sync data record creation
            $syncCycle = MachineSyncData::create([
                'last_machine_id' => $lastMachineSyncData->last_machine_id,
                'last_attendance_date' => $lastMachineSyncData->last_attendance_date,
                'created_by'=>'0',
                'updated_by'=>'0',
                'flagged' => null,
                'added' => null
            ]);

                //getting login Method (fingerprint ...) and Event Type (clock in , out...) for the event
                $loginMethodCode = $lookup->where('parent_id', config('zkteco.loginMethodStart'))->where('code', (string) $event['state'])->first();
                $eventTypeCode = $lookup->where('parent_id', config('zkteco.eventTypeStart'))->where('code', (string) $event['type'])->first();

                $loginMethod = $loginMethodCode? $loginMethodCode->id :config('zkteco.undefinedLoginMethod');
                $eventType = $eventTypeCode? $eventTypeCode->id :config('zkteco.undefinedEvent');
                
                //update or create a new Machine Record
                MachineRecord::updateOrCreate(
                    ['machine_id' => $event['uid']],
                    [
                        'emp_id' => $event['id'],
                        'login_via' => $loginMethod,
                        'event_type_id'=> $eventType,
                        'timestamp' => $event['timestamp'],
                        'sync_cycle' => $syncCycle->id,
                        'calculated' => false
                    ]
                );
            //update the machine sync data record statistics
            $syncCycle->update([
                'added' => 1
            ]);
        });
        if ($request->ajax()) {
           
                return response()->json([
                    'success' => true,
                ]);
            
            }
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    

    //helper method to get the event codes (clock in => 0 , clock out =>2 ...)
    public function getEventCodes(){
            return Lookup::where('parent_id', config('zkteco.eventTypeStart'))
                        ->pluck('name', 'code')
                        ->toArray();        
    }

    //helper method
    public function getMachineRecordsById(Request $request){
        $records = MachineRecord::with('eventType')->whereIn("machine_id", $request->idsToFetch)->get();
        return $records;
    }

    


    //Main method to calculate attendance of any new uncalculated data 
    public function calculateAttendance(Request $request){

        \session_write_close();

        return response()->stream(function() use ($request) {
            try {
                $recordsCreated = 0;
                $recordsModified = 0;
                $recordsFlagged = 0;
                $flaggedRecords = [];
                $conflictedRecords = [];

                echo $this->sseEvent('progress', [
                    'progress' => 0,
                    'message' => 'Preparing to calculate attendance ...'
                ]);
                flush();

                //get all machine events with : 'calculated' set as false or are related to an uncalculated record
                $events = MachineRecord::with('eventType','employee')->where(function ($q) {
                    $q->where('calculated', false)
                    ->orWhereIn('employee_time_id', MachineRecord::where('calculated', false)->pluck('employee_time_id'));
                    })->get();

                //get all the related Attendance Records to compare new and old times with  
                $relatedEmployeeTimes = EmployeeTime::whereIn("date", $events->unique("timestamp")->pluck("timestamp")
                                                        ->map(fn ($t) => Carbon::parse($t)->toDateString())->all())->get();
                
                //group by employee id and date
                $groupedEvents = $events->groupBy(function ($event) {
                    return $event->emp_id . '__' . Carbon::parse($event->timestamp)->toDateString();
                });

                $eventTypeLookups = Lookup::whereIn('name', ['Clock In', 'Clock Out', 'Break In', 'Break Out'])->get()->keyBy('name');
                
                //get workschedule which contains the break_duration
                $workSchedule = WorkSchedule::orderBy('created_at','desc')->first();

                $clockOutCode = (int) ($eventTypeLookups['Clock Out']->code ?? 0);
                $clockInCode = (int) ($eventTypeLookups['Clock In']->code ?? 0);

                $groupCount = $groupedEvents->count();
                $processed = 0;

                echo $this->sseEvent('progress', [
                    'progress' => 5,
                    'message' => 'Found ' . $groupCount . ' day(s) to calculate.',
                    'total' => $groupCount
                ]);
                flush();

                if ($groupCount === 0) {
                    echo $this->sseEvent('progress', [
                        'progress' => 100,
                        'message' => 'Nothing to calculate.'
                    ]);
                    flush();
                }
                
                //for each group of events (events on the same day) -> calculate attendance and save it in a new EmployeeTime record
                $groupedEvents->each(function ($events) use($clockOutCode, $clockInCode , &$recordsCreated, &$recordsModified ,&$recordsFlagged, &$flaggedRecords , &$conflictedRecords ,$relatedEmployeeTimes , $eventTypeLookups , $workSchedule , &$processed , $groupCount) {
                    $orderedEvents = collect($events)->sortBy('timestamp')->values();
                    $first = $orderedEvents->first();
                    $employeeId = $first->emp_id;
                    $employee = $first->employee;
                    $date = Carbon::parse($first->timestamp)->toDateString();
                    
                    //get the related employee time for this day
                    $relatedEmployeeTime = $relatedEmployeeTimes->first(fn ($empTime) => $empTime->date === $date && $empTime->employee_id===$employeeId);
                    
                    //call the helper method to get the data of the day
                    $totalTimeResponse = $this->calculateTotalTime($orderedEvents , $relatedEmployeeTime , $eventTypeLookups , $workSchedule);
                    
                    $employeeName = $employee ? trim("{$employee->first_name} {$employee->mid_name} {$employee->last_name}") : '';
                    
                    // conflict : old employee time disagrees with the new punches -> let the user choose, don't overwrite yet
                    $conflicts = $totalTimeResponse['conflicts'] ?? [];
                    if (!empty($conflicts)) {
                        $conflictedRecords[]= ['employee_id' => $employeeId,
                                                'employee_name' => $employeeName,
                                                'date' => $date,
                                                'conflicts' => $conflicts,
                                                'events' => $orderedEvents,
                                                'relatedEmployeeTime' => $relatedEmployeeTime
                                                ];
                        $processed++;
                        $progress = $groupCount > 0 ? round((($processed) / $groupCount) * 100) : 100;
                        echo $this->sseEvent('progress', [
                            'progress' => $progress,
                            'message' => 'Calculated ' . $processed . ' of ' . $groupCount . ' day(s).',
                            'total' => $groupCount
                        ]);
                        flush();
                        return;
                    }

                    // flag the abnormality but keep calculating/saving (unless its unclosed shift or still on break) (flag + save)
                    if (!empty($totalTimeResponse['abnormality'])) {
                        $recordsFlagged += 1;
                        
                        //if still on shift remove flag
                        if (($totalTimeResponse['abnormality']) =='unclosed_shift' || $totalTimeResponse['abnormality'] =='still_on_break'  ) {
                            $totalTimeResponse['abnormality'] = null;
                        }
                        //if irregular or not normal prompt the user
                        elseif ($totalTimeResponse['abnormality'] == 'irregular_sequence' || $totalTimeResponse['state']!=='normal') {
                            $flaggedRecords[] = [
                                'employee_id' => $employeeId,
                                'employee_name' => $employeeName,
                                'date' => $date,
                                'events' => $orderedEvents,
                                'relatedEmployeeTime' => $relatedEmployeeTime,
                                'abnormality' => $totalTimeResponse['abnormality'],
                                'state' => $totalTimeResponse['state'] ?? null
                            ];
                            $processed++;
                            $progress = $groupCount > 0 ? round((($processed) / $groupCount) * 100) : 100;
                            echo $this->sseEvent('progress', [
                                'progress' => $progress,
                                'message' => 'Calculated ' . $processed . ' of ' . $groupCount . ' day(s).',
                                'total' => $groupCount
                            ]);
                            flush();
                            return;
                        }

                    }


                    $totalTime = $totalTimeResponse['totalTime'];

                    // still no clock in after fills -> flagged above, nothing to save
                    if (empty($totalTimeResponse['clock_in'])) {
                        $processed++;
                        $progress = $groupCount > 0 ? round((($processed) / $groupCount) * 100) : 100;
                        echo $this->sseEvent('progress', [
                            'progress' => $progress,
                            'message' => 'Calculated ' . $processed . ' of ' . $groupCount . ' day(s).',
                            'total' => $groupCount
                        ]);
                        flush();
                        return;
                    }

                    $employeeTime = EmployeeTime::updateOrCreate(
                        ['employee_id' => $employeeId, 'date' => $date],
                        ['acc_number' => $employee->acc_number,
                        'clock_in' => $totalTimeResponse['clock_in'],
                        'clock_out' => $totalTimeResponse['clock_out'],
                        'total_time' => $totalTime,
                        'total_leave_diff' => $totalTimeResponse['total_leave_diff'],
                        'total_break_diff' => $totalTimeResponse['total_break_diff'],
                        'flagged' => !empty($totalTimeResponse['abnormality']),
                        'break_flag' => $totalTimeResponse['break_flag'] ?? null
                        ]);

                    if ($employeeTime->wasRecentlyCreated) {
                        $recordsCreated++;
                    } else {
                        $recordsModified++;
                    }
                    
                    //Mark the events as calculated and link them to the new employeeTime record
                    MachineRecord::whereIn('id', $orderedEvents->pluck('id'))->update(['employee_time_id'=>$employeeTime->id , 'calculated' => true]);

                    $processed++;
                    $progress = $groupCount > 0 ? round((($processed) / $groupCount) * 100) : 100;
                    echo $this->sseEvent('progress', [
                        'progress' => $progress,
                        'message' => 'Calculated ' . $processed . ' of ' . $groupCount . ' day(s).',
                        'total' => $groupCount
                    ]);
                    flush();
                });

                echo $this->sseEvent('done', [
                    'success' => true,
                    'recordsModified' => $recordsModified,
                    'recordsCreated' => $recordsCreated,
                    'recordsFlagged' => $recordsFlagged,
                    'flaggedRecords' => $flaggedRecords,
                    'conflictedRecords' => $conflictedRecords,
                    'totalRecords' => EmployeeTime::count()
                ]);
                flush();

            } catch (\Throwable $e) {
                echo $this->sseEvent('error', [
                    'message' => $e->getMessage()
                ]);
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Encoding' => 'none',
        ]);
    }

    //return the pedning events for a specific emplouyee and date (same criteria as the caluclateAttendance() method)
    private function getPendingEventsFor($employeeId , $date){
        return MachineRecord::where('emp_id', $employeeId)
            ->whereDate('timestamp', $date)
            ->where(function ($q) {
                $q->where('calculated', false)
                ->orWhereIn('employee_time_id', MachineRecord::where('calculated', false)->pluck('employee_time_id'));
            })->get();
    }

    
    //checks if there's a EmployeeTime record for a specific date and employee combination 
    public function checkAttendance($employeeId , $date){
        
        
        $recordExists = EmployeeTime::where(function ($query) use ($date , $employeeId) {
            $query->where('employee_id', $employeeId);
            $query->where('date', $date);
            })->exists();
            
            return $recordExists;
    }
    
    //same as above but as a html request / response
    public function checkAttendanceWithResponse(Request $request){

        $date = $request->date;
        $employeeId = $request->employeeId;
        $recordExists = $this->checkAttendance($employeeId ,$date );
        
        return response()->json(['success'=> true,'exists'=> $recordExists],200);
    }
    
    //adds any data that can be extracted from an event sequence
    public function addRelevantAttendanceInfo(Request $request){
        try{    
        $date = $request->date;
        $employeeId = $request->employee_id;

        if(!$date || !$employeeId)
            {
                return response()->json(['success'=> false,'error'=> 'No provided Date and/or employee Id'],400);
            }

        //gets the events from the employee Id and date combination
        $events = $this->getPendingEventsFor($employeeId , $date);

        if($events->isEmpty())
            {
                return response()->json(['success'=> false,'error'=> 'No events found'],404);
            }

        $eventTypeLookups = Lookup::whereIn('name', ['Clock In', 'Clock Out', 'Break In', 'Break Out'])->get()->keyBy('name');
        $workSchedule = WorkSchedule::orderBy('created_at','desc')->first();
        
        //calls the helper method to extract data
        $data = $this->getPurposefulData($events, $eventTypeLookups, $workSchedule);

        //creates or overwrites the employeeTime record
        $employeeTime = EmployeeTime::updateOrCreate(
            ['employee_id' => $employeeId,
            'date' => $date],
            [
            'clock_in' => $data['clock_in'],
            'clock_out' => $data['clock_out'],
            'total_time' => $data['total_time'] ?? null,
            'total_leave_diff' => null,
            'total_break_diff' => $data['break_duration'] ?? null,
            'flagged'=> true, 
            'break_flag' => $data['break_flag']
        ]) ;
        
        //sets the machine record's  calculated as true and links it to the employeeTime record that was just created / updated 
        MachineRecord::whereIn('id' , $events->pluck('id'))->update(['employee_time_id'=>$employeeTime->id , 'calculated' => true]);

            return response()->json(['success' => true], 200);
        }
        catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
        }        
        }

    //add an empty EmployeeTime record and links the related machine events to it 
    public function addEmptyAttendanceRecord(Request $request){
        try{    
        $date = $request->date;
        $employeeId = $request->employee_id;
        if(!$date || !$employeeId)
            {
                return response()->json(['success'=> false,'error'=> 'No provided Date and/or employee Id'],400);
            }

        //gets the Machine events for the employeeID and date combination
        $events = $this->getPendingEventsFor($employeeId , $date);
        if($events->isEmpty())
            {
                return response()->json(['success'=> false,'error'=> 'No events found'],404);
            }


        $recordExist = $this->checkAttendance( $employeeId , $date);
        
        //check validity
        if($recordExist)
            {
                return response()->json(['success'=> false,'error'=>'Record already exists for this employee and date'],409);
            }
        

        //creates the empty Record
        $employeeTime = EmployeeTime::create(
            ['employee_id' => $employeeId,
            'date' => $date,
            'flagged'=> true, 
            'break_flag' =>null
            ]);

        //links the machine records to the employeeTime and sets calculated as true 
        MachineRecord::whereIn('id' , $events->pluck('id'))->update(['employee_time_id'=>$employeeTime->id , 'calculated' => true]);

        return response()->json(['success'=> true], 200);
        }
        catch (Exception $e){
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
        }
    }

    //sets the machine events 'calculated' as true (ignores them)
    public function ignoreEvents(Request $request){
        try{    
        $date = $request->date;
        $employeeId = $request->employee_id;
        if(!$date || !$employeeId)
            {
                return response()->json(['success'=> false,'error'=> 'No provided Date and/or employee Id'],400);
            }

        $events = $this->getPendingEventsFor($employeeId , $date);
        if($events->isEmpty())
            {
                return response()->json(['success'=> false,'error'=> 'No events found'],404);
            }

        MachineRecord::whereIn('id' , $events->pluck('id'))->update(['calculated' => true]);

        return response()->json(['success'=> true], 200);
        }
        catch (Exception $e){
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
        }
    }

    //helper method to extract purposeful data from the machine events 
    public function getPurposefulData($events ,$eventTypeLookups , $workSchedule = null ){
        if($events->isEmpty())
            return [];

        $orderedEvents = $events->sortBy('timestamp')->values();

        // classify once -> know exactly what's salvageable without rescanning
        $eventSequence = $this->getCharacters($orderedEvents);
        $classification = $this->classifySequence($eventSequence);
        $attributes = $classification['attributes'];

        $clockInEvent  = $orderedEvents->first(fn ($e) => $e->eventType->id == ($eventTypeLookups['Clock In']->id ?? null));
        $clockOutEvent = $orderedEvents->reverse()->first(fn ($e) => $e->eventType->id == ($eventTypeLookups['Clock Out']->id ?? null));

        $clockInCarbon  = $clockInEvent  ? Carbon::parse($clockInEvent->timestamp) : null;
        $clockOutCarbon = $clockOutEvent ? Carbon::parse($clockOutEvent->timestamp) : null;

        // intermediate values needed to mirror calculateTotalTime's final total_time
        $totalIntervalTime = 0;   // gaps between consecutive clock-out/clock-in pairs
        $BreakAllowedDuration = 0;
        if ($workSchedule && $workSchedule->break_duration) {
            $BreakAllowedDuration = ((int) $workSchedule->break_duration) * 60;
        }

        // only compute what classifySequence says is salvageable
        $totalTime = null;
        $totalBreakTime = 0;
        if(in_array('totalTime', $attributes) && $clockInCarbon && $clockOutCarbon){

            $breakInId  = $eventTypeLookups['Break In']->id ?? null;
            $breakOutId = $eventTypeLookups['Break Out']->id ?? null;

            $pendingBreakIn = null;
            $lastInstance = null;
            foreach($orderedEvents as $event){
                $eventTime = Carbon::parse($event->timestamp);
                $id = $event->eventType->id;
                if($id == ($eventTypeLookups['Clock In']->id ?? null)){
                    if ($lastInstance !== null) {
                        $totalIntervalTime += $eventTime->diffInSeconds($lastInstance);
                    }
                    $lastInstance = null;
                } elseif($id == ($eventTypeLookups['Clock Out']->id ?? null)){
                    $lastInstance = $eventTime;
                } elseif($id == $breakInId){
                    $pendingBreakIn = $eventTime;
                } elseif($id == $breakOutId && $pendingBreakIn){
                    $totalBreakTime += $eventTime->diffInSeconds($pendingBreakIn);
                    $pendingBreakIn = null;
                }
            }

            $breakDiff = $BreakAllowedDuration - $totalBreakTime;
            // same as calculateTotalTime : gross span - interval - over-break
            $net = $clockOutCarbon->diffInSeconds($clockInCarbon) - $totalIntervalTime + min($breakDiff, 0);
            $totalTime = gmdate('H:i:s', $net);
        }

        $breakDuration = null;
        if(in_array('breakDuration', $attributes)){
            if($totalBreakTime === 0 && !$clockInCarbon){
                // no in/out pass ran -> compute break time independently
                $totalBreakTime = 0;
                $pendingBreakIn = null;
                $breakInId  = $eventTypeLookups['Break In']->id ?? null;
                $breakOutId = $eventTypeLookups['Break Out']->id ?? null;
                foreach($orderedEvents as $event){
                    if($event->eventType->id == $breakInId){
                        $pendingBreakIn = Carbon::parse($event->timestamp);
                    } elseif($event->eventType->id == $breakOutId && $pendingBreakIn){
                        $totalBreakTime += Carbon::parse($event->timestamp)->diffInSeconds($pendingBreakIn);
                        $pendingBreakIn = null;
                    }
                }
            }
            // only the over-break deduction matters (under-break = no diff)
            $breakDuration = gmdate('H:i:s', abs(min($BreakAllowedDuration - $totalBreakTime, 0)));
        }

        // flag whenever any break is missing
        $hasBreakIn  = $orderedEvents->contains(fn ($e) => $e->eventType->id == ($eventTypeLookups['Break In']->id ?? null));
        $hasBreakOut = $orderedEvents->contains(fn ($e) => $e->eventType->id == ($eventTypeLookups['Break Out']->id ?? null));
        if($hasBreakIn && !$hasBreakOut)
            $breakFlag = 'Only In';
        elseif (!$hasBreakIn && $hasBreakOut)
            $breakFlag = 'Only Out';
        elseif (!$hasBreakIn && !$hasBreakOut)
            $breakFlag = 'No Break';
        else
            $breakFlag = 'pass';

        return [
            'clock_in' => $clockInCarbon ? $clockInCarbon->format('H:i:s') : null,
            'clock_out' => $clockOutCarbon ? $clockOutCarbon->format('H:i:s') : null,
            'break_flag' => $breakFlag,
            'total_time' => $totalTime,
            'break_duration' => $breakDuration,
            'state' => $classification['state'],
            'attributes' => $attributes
        ];

    }
    
    //saves what the user chose of the conflicting times (old/new)
    public function resolveCalculationConflicts(Request $request){
        try{
            $choices = $request->choices;

            //get the events for the employeeId and the date
            $events = $this->getPendingEventsFor($choices['employee_id'],$choices['date']);

            if($events->isEmpty())
                return response()->json(['success'=> false,'error'=> 'No machine events for this employee and date'],422);
            
            $orderedEvents = $events->sortBy('timestamp')->values();

            $eventTypeLookups = Lookup::whereIn('name', ['Clock In', 'Clock Out', 'Break In', 'Break Out'])->get()->keyBy('name');

            $clockInEvent  = $orderedEvents->first(fn($e) => $e->eventType->id == ($eventTypeLookups['Clock In']->id ?? null));
            $clockOutEvent = $orderedEvents->reverse()->first(fn($e) => $e->eventType->id == ($eventTypeLookups['Clock Out']->id ?? null));

            //if there's a clock choice of clock in and there's a clock In/Out in the events -> save the chosen clock in  / out
            if(!empty($choices['clock_in']) && $clockInEvent)
                $clockInEvent->update(['timestamp'=> $choices['date'] . ' ' . $choices['clock_in']]);
            if(!empty($choices['clock_out']) && $clockOutEvent)
                $clockOutEvent->update(['timestamp'=> $choices['date'] . ' ' . $choices['clock_out']]);
            $orderedEvents = $orderedEvents->sortBy('timestamp')->values();



            $workSchedule = WorkSchedule::orderBy('created_at','desc')->first();

            //calculates total time for this sequence with the chosen clock in and/or out
            $totalTimeResponse = $this->calculateTotalTime($orderedEvents,null,$eventTypeLookups,$workSchedule);

            // after resolving the conflict the record may now be flagged. hand it off so the
            // user can act on it in the same card through the normal flagged-record pathways
            if(!empty($totalTimeResponse['abnormality'])){
                $relatedEmployeeTime = EmployeeTime::where('employee_id', $choices['employee_id'])
                                                    ->where('date', $choices['date'])->first();
                $first = $orderedEvents->first();
                $employee = $first?->employee;
                $employeeName = $employee ? trim("{$employee->first_name} {$employee->mid_name} {$employee->last_name}") : '';

                return response()->json([
                    'success' => true,
                    'flagged' => [
                        'employee_id' => $choices['employee_id'],
                        'employee_name' => $employeeName,
                        'date' => $choices['date'],
                        'events' => $orderedEvents,
                        'relatedEmployeeTime' => $relatedEmployeeTime,
                        'abnormality' => $totalTimeResponse['abnormality'],
                        'state' => $totalTimeResponse['state'] ?? null
                    ]
                ], 200);
            }

            //if no flag -> create / overwrite the employeeTime record
            $employeeTime = EmployeeTime::updateOrCreate(
                ['employee_id' => $choices['employee_id'],
                'date' => $choices['date']],
                [
                'clock_in' => $totalTimeResponse['clock_in'],
                'clock_out' => $totalTimeResponse['clock_out'],
                'total_time' => $totalTimeResponse['totalTime'],
                'total_leave_diff' => $totalTimeResponse['total_leave_diff'],
                'total_break_diff' => $totalTimeResponse['total_break_diff'],
                'flagged'=> $totalTimeResponse['abnormality']? true : false , 
                'break_flag' => $totalTimeResponse['break_flag']?? 'pass'
            ]) ;

            //update the machine records  'calculated' to true and link to the employeeTime record
            MachineRecord::whereIn('id', $orderedEvents->pluck('id')->filter())
                ->update(['employee_time_id' => $employeeTime->id, 'calculated' => true]);

            return response()->json(['success' => true], 200);
        }
        catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
        }
    }


    //gives a string of characters for the sequence : clock in out break in out -> IOBb
    public function getCharacters($orderedEvents){
        return  $orderedEvents->map(function ($event) {
                switch ($event->eventType->name){
                    case 'Clock In':
                        return 'I';
                    case 'Clock Out':
                        return 'O';
                    case 'Break In':
                        return 'B';
                    case 'Break Out':
                        return 'b';
                    default : 
                        return '?';
                }
        })->implode('');
    }

    
    //gives 3 states : normal , derivable , abnormal
    //plus the list of attributes that can still be derived from the sequence
    public function classifySequence($eventSequence){


        if(preg_match('/^I(?:(?:OI)|(?:Bb))*O$/', $eventSequence)){
            return ['state' => 'normal',
                    'attributes' => ['totalTime', 'breakDuration']];
        }

        $chars = str_split($eventSequence);

        // what can still be salvaged from this (imperfect) sequence :
        // totalTime     -> an I appears before an O, so a positive span exists
        // breakDuration -> at least one Break In before a Break Out
        $attributes = [];
        if(preg_match('/I.*O/', $eventSequence)){
            $attributes[] = 'totalTime';
        }
        if(preg_match('/B.*b/', $eventSequence)){
            $attributes[] = 'breakDuration';
        }

        if(str_contains($eventSequence, 'BB')){
            // double break-in -> breakDuration isn't calulatable,
            // totalTime may still be salvageable
            $state = in_array('totalTime', $attributes) ? 'derivable' : 'abnormal';
            return ['state' => $state,
                    'attributes' => array_values(array_diff($attributes, ['breakDuration']))];
        }

        // double break-out (orphan) breaks the pairing -> drop breakDuration
        if(str_contains($eventSequence, 'bb')){
            $state = in_array('totalTime', $attributes) ? 'derivable' : 'abnormal';
            return ['state' => $state,
                    'attributes' => array_values(array_diff($attributes, ['breakDuration']))];
        }

        //check each I has a closing O 
        $pendingIn = 0;
        foreach($chars as $ch){
            if($ch === 'I'){
                $pendingIn++;
            } elseif($ch === 'O' && $pendingIn > 0){
                return ['state' => 'derivable',
                        'attributes' => $attributes];
            }
        }

        //check each B has a closing B 
        $pendingBreak = 0;
        $hasBreak = false;
        foreach($chars as $ch){
            if($ch === 'B'){
                $pendingBreak++;
                $hasBreak = true;
            } elseif($ch === 'b'){
                if($pendingBreak === 0){
                    return ['state' => 'abnormal',
                            'attributes' => []];         
                }
                $pendingBreak--;
                $hasBreak = true;
            }
        }
        if($pendingBreak > 0){
            return ['state' => 'abnormal',
                    'attributes' => []];                 
        }

        if($hasBreak){
            return ['state' => 'derivable',
                    'attributes' => $attributes];
        }

        return ['state' => 'abnormal',
                'attributes' => []];
    }

    // Persist any in-memory reclassification (setRelation) of event types down to the DB,
    // so other code paths that re-fetch the records see the corrected types.
    // Skips synthetic (not yet persisted) events so they don't get inserted.
    private function persistEventSwitches($orderedEvents){
        foreach($orderedEvents as $event){
            if(!$event->exists){
                continue;
            }
            if($event->event_type_id !== $event->eventType->id){
                $event->update(['event_type_id' => $event->eventType->id]);
            }
        }
    }

    //helper method to check for if the breaks are inverted and fix -> bB becomes Bb 
    public function invertBreakEvents($orderedEvents , $breakIn , $breakOut){
        $events = $orderedEvents->values();
        $chars = str_split($this->getCharacters($events));

        for ($i = 0; $i < count($chars) - 1; $i++) {
            if ($chars[$i] === 'b' && $chars[$i + 1] === 'B') {
                $events[$i]->setRelation('eventType', $breakIn);
                $events[$i + 1]->setRelation('eventType', $breakOut);
                $i++;
            }
        }
    }

    //helper method to extract data from a sequence of machine events (totalTime , clock_in , clock_out ...)
    public function calculateTotalTime($orderedEvents , $relatedEmployeeTime , $eventTypeLookups , $workSchedule){
        
        //the normal accepted pattern (clock in -> leaves and breaks -> clock out) 
        $validPattern = '/^I(?:(?:OI)|(?:Bb))*O$/';

        //inverts inverted breaks
        $this->invertBreakEvents($orderedEvents , $eventTypeLookups['Break In'] ?? null , $eventTypeLookups['Break Out'] ?? null);

        //get the events word -> IBbOIO 
        $eventSequence=$this->getCharacters($orderedEvents);
        
        $abnormality = null;
        $breakFlag = null;
        $conflicts = [];
        

        //if the event sequence doesn't match the valid pattern -> check what kind of abnormality is it  
        if(!preg_match($validPattern , $eventSequence)){
            $clockInLookup = $eventTypeLookups['Clock In'] ?? null;
            $clockOutLookup = $eventTypeLookups['Clock Out'] ?? null;
            $checkAgain=false;
            do {

                $eventSequence = $this->getCharacters($orderedEvents);
                $cases = [
                    'inverted_in_out'   => '/^O.*I$/',              //flip then continue normally
                    // 'starts_with_clock_out' => '/^O/',            // starts with clock out 
                    'break_then_shift'  => '/^[Bb]I(?:OI|Bb|bB)*O$/',   // day starts with a break 
                    'missing_first_in'  => '/^(?!I)/',              //show the user
                    'double_in_with_out'=> '/II(?=.*O)/',        //flag and calculate extremities
                    'double_in_no_out'  => '/^I(?:OI|Bb|bB)*II(?:OI|Bb|bB)*$/',   // I .. I I .. - duplicate in embedded in normal flow, no closing out -> treat second as out fix
                    'orphan_out'        => '/^I(?:OI|Bb|bB)*O(?:OI|Bb|bB)*O$/',   // e.g. I O O / I Bb O Bb O O - extra clock out inside a normal flow
                    'orphan_break_in'   => '/^I(?:OI|Bb|bB)*B(?:OI|Bb|bB)*O$/',   // e.g. I B O - stray break in inside a normal flow
                    'orphan_break_out'  => '/^I(?:OI|Bb|bB)*b(?:OI|Bb|bB)*O$/',   // e.g. I b O - stray break out inside a normal flow
                    'unclosed_shift'    => '/^I(?:OI|Bb|bB)*$/',      //continue normally
                    'still_on_break'    => '/^I(?:OI|Bb|bB)*B$/'    //continue  normally
                    ];

                    foreach ($cases as $name => $re) {
                        if (preg_match($re, $eventSequence)){
                            $abnormality = $name;
                            break;
                        } 
                    }
                    
                    //for each case take the suitable descision then exit
                    if ($abnormality=='unclosed_shift'||$abnormality=='still_on_break' ){
                        $checkAgain =false ;
                    
                    }
                    elseif ($abnormality== 'orphan_break_in'){
                        $abnormality =null;
                        $checkAgain =false ;
                        $breakFlag = 'Only In';
                        
                    }
                    elseif ($abnormality== 'orphan_break_out'){
                        $abnormality =null;
                        $checkAgain =false ;
                        $breakFlag = 'Only Out';
                        
                    }
                    elseif ($abnormality== 'orphan_out'){
                        $checkAgain =false;
                    }
                    elseif($abnormality=='inverted_in_out'){
                        //invert the types of the events 
                        $orderedEvents->first()->setRelation('eventType', $clockInLookup);
                        $orderedEvents->last()->setRelation('eventType', $clockOutLookup);
                        $abnormality = null;
                        $checkAgain=true;
                    }
                    elseif($abnormality=='double_in_with_out'){
                        // I I O -> disregard the middle in but keep the abnormality so it gets flagged
                        $checkAgain=false;
                    }
                    elseif($abnormality=='double_in_no_out'){
                        // I I with no out -> treat the second in as the clock out
                        $prevWasIn = false;
                        foreach($orderedEvents as $event){
                            $isIn = $event->eventType->name === 'Clock In';
                            if($isIn && $prevWasIn){
                                $event->setRelation('eventType', $clockOutLookup);
                                break;
                            }
                            $prevWasIn = $isIn;
                        }
                        $abnormality = null;
                        $checkAgain=true;
                    }
                    elseif($abnormality=='break_then_shift'){
                        // day starts with a stray break punch -> treat it as the clock in
                        // the original clock in becomes a duplicate, double_in_with_out will flag it
                        $orderedEvents->first()->setRelation('eventType', $clockInLookup);
                        $abnormality=null;
                        $checkAgain=true;
                    }
                    else{
                        // no known case matched -> still flag it so nothing slips through silently
                        $abnormality = $abnormality ?? 'irregular_sequence';
                        $checkAgain=false;
                    }

                    // A reclassification (e.g. O..I flipped to I..O) may have
                    // produced a fully valid sequence again. If so, treat it as
                    // normal instead of falling through to 'irregular_sequence'.
                    if ($checkAgain && preg_match($validPattern, $this->getCharacters($orderedEvents))) {
                        $abnormality = null;
                        $checkAgain = false;
                    }
            }
            while($checkAgain);
        }

        // persist any in-memory reclassification so DB reads elsewhere see corrected types
        $this->persistEventSwitches($orderedEvents);

        // classify the event sequence as -> abnormal , derivable , normal
        $eventSequence = $this->getCharacters($orderedEvents);
        $state = $this->classifySequence($eventSequence)['state'];

        // default break_flag when nothing abnormal was flagged for a break
        // 'No Break' -> no break events at all ; 'pass' -> breaks present and healthy
        if($breakFlag === null){
            $hasBreakIn  = str_contains($eventSequence, 'B');
            $hasBreakOut = str_contains($eventSequence, 'b');
            if(!$hasBreakIn && !$hasBreakOut){
                $breakFlag = 'No Break';
            } else {
                $breakFlag = 'pass';
            }
        }

        //if there's a related employeetime record -> compare to see if there's conflicts
        if($relatedEmployeeTime){

            $oldClockIn = $relatedEmployeeTime->clock_in;
            $oldClockOut= $relatedEmployeeTime->clock_out;

            // what the new punches say (after the abnormality fixes)
            $newClockInEvent  = $orderedEvents->first(fn($e) => $e->eventType->name === 'Clock In');
            $newClockOutEvent = $orderedEvents->reverse()->first(fn($e) => $e->eventType->name === 'Clock Out');

            // 1) conflict : both sides exist but disagree -> collect it so the user chooses
            if($newClockInEvent && $oldClockIn){
                $newClockIn = Carbon::parse($newClockInEvent->timestamp)->format('H:i:s');
                if($newClockIn !== $oldClockIn){
                    $conflicts['clock_in'] = ['old' => $oldClockIn, 'new' => $newClockIn];
                }
            }
            if($newClockOutEvent && $oldClockOut){
                $newClockOut = Carbon::parse($newClockOutEvent->timestamp)->format('H:i:s');
                if($newClockOut !== $oldClockOut){
                    $conflicts['clock_out'] = ['old' => $oldClockOut, 'new' => $newClockOut];
                }
            }


            if (!empty($conflicts)) {
                return ['totalTime' => null,
                        'total_leave_diff' => null,
                        'total_break_diff' => null,
                        'abnormality' => null,
                        'conflicts' => $conflicts,
                        'clock_in' => null,
                        'clock_out' => null,
                        'break_flag' => $breakFlag,
                        'state' => $state];
            } 

            

            // 2) fill : missing on events side but found in old -> inject into the events and use it in calculation
            if(!$newClockInEvent && $oldClockIn){
                $tempIn = new MachineRecord([
                        'machine_id' => 0,
                        'emp_id'     => $relatedEmployeeTime->employee_id,
                        'timestamp'  => $relatedEmployeeTime->date . ' ' . $oldClockIn,
                    ]);
                $tempIn->setRelation('eventType', $eventTypeLookups['Clock In']);
                $orderedEvents->prepend($tempIn);
                if($abnormality == 'missing_first_in'){
                    // fixed using the saved clock in -> no longer abnormal
                    $abnormality = null;
                }
            }
            if(!$newClockOutEvent && $oldClockOut){
                $tempOut = new MachineRecord([
                        'machine_id' => 0,
                        'emp_id'     => $relatedEmployeeTime->employee_id,
                        'timestamp'  => $relatedEmployeeTime->date . ' ' . $oldClockOut,
                    ]);
                $tempOut->setRelation('eventType', $eventTypeLookups['Clock Out']);
                $orderedEvents->push($tempOut);
                if($abnormality == 'unclosed_shift'){
                    $abnormality = null;
                }

            }

            $newClockIn = $orderedEvents->first()->timestamp;
            $newClockOut = $orderedEvents->last()->timestamp;
        }



        // after fixes & fills : final in/out derived here so the caller doesn't have to
        $finalInEvent  = $orderedEvents->first(fn($e) => $e->eventType->name === 'Clock In');
        $finalOutEvent = $orderedEvents->reverse()->first(fn($e) => $e->eventType->name === 'Clock Out');

        // still no clock in anywhere (and nothing in old to fill from) or -> flag it, nothing meaningful to save
        if(!$finalInEvent || ($state!=='normal' && $abnormality!=='double_in_with_out' && $abnormality!== 'double_in_no_out')){
            return ['totalTime' => null,
                    'total_leave_diff' => null,
                    'total_break_diff' => null,
                    'abnormality' => $abnormality,
                    'clock_in' => null,
                    'clock_out' => null,
                    'break_flag' => $breakFlag,
                    'state' => $state];
        }


        //formatted Clock In and Clock Out
        $clockInFinal  = Carbon::parse($finalInEvent->timestamp)->format('H:i:s');
        $clockOutFinal = $finalOutEvent ? Carbon::parse($finalOutEvent->timestamp)->format('H:i:s') : null;

        //if the shift is still in progress -> save only the available data now 
        if($abnormality == 'unclosed_shift' || $abnormality == 'still_on_break'){
            return ['totalTime' => null,
                    'total_leave_diff' => null,
                    'total_break_diff' => null,
                    'abnormality' => $abnormality,
                    'conflicts' => $conflicts,
                    'clock_in' => $clockInFinal,
                    'clock_out' => null,
                    'break_flag' => $breakFlag,
                    'state' => $state];
        }
        
        
        $firstClockIn= Carbon::parse($orderedEvents->first()->timestamp);
        $lastClockOut= Carbon::parse($orderedEvents->last()->timestamp);
        $totalTime = $lastClockOut? $lastClockOut->diffInSeconds($firstClockIn) : null;
        

        $lastInstance=null;
        $lastBreakInstance=null;
        $BreakAllowedDuration = 0;

        //Get the allowed Break Duration
        if ($workSchedule && $workSchedule->break_duration) {
            $BreakAllowedDuration = ((int) $workSchedule->break_duration) * 60;
        }
        $totalIntervalTime = 0;
        $totalBreakTime =0;
        
        //calculate total time and total break time loop
        foreach($orderedEvents as $event){
            $eventTime = Carbon::parse($event['timestamp']);
            $code = (int) $event->eventType->code;
            switch ($code){
                case 0: // Clock In

                    if ($lastInstance !==null) {
                    $totalIntervalTime += $eventTime->diffInSeconds($lastInstance);
                    }
                    $lastInstance = null;
                    break;

                case 1: // Clock Out

                    $lastInstance = $eventTime;
                    break;

                case 2: // Break In
                    
                    $lastBreakInstance = $eventTime;
                    break;

                case 3: // Break Out
                    if ($lastBreakInstance !== null) {
                    $totalBreakTime += $eventTime->diffInSeconds($lastBreakInstance);
                    }
                    $lastBreakInstance = null;
                    break;

                default:
                    break;
            }
        }

        //calculate the total break diff and total time
        $breakDiff = $BreakAllowedDuration - $totalBreakTime;
        $totalTime = $totalTime - $totalIntervalTime +min($breakDiff,0);
        
        //format the total time and diffs
        $formattedTotalTime = gmdate('H:i:s', $totalTime);        
        $formattedBreakDiff = gmdate('H:i:s', abs(min($breakDiff, 0)));
        $formattedIntervalDiff = gmdate('H:i:s', $totalIntervalTime);
        return ['totalTime' => $formattedTotalTime,
                'total_leave_diff' => $formattedIntervalDiff,
                'total_break_diff'=>$formattedBreakDiff,
                'abnormality' => $abnormality,
                'conflicts' => $conflicts,
                'clock_in' => $clockInFinal,
                'clock_out' => $clockOutFinal,
                'break_flag' => $breakFlag,
                'state' => $state];
    }

   public function testMachineConnection(Request $request){
        try {
            $validated = $request->validate([
                'ip' => 'required|ip',
                'port' => 'nullable|integer|min:1|max:65535',
            ]);

            $ip = $validated['ip'];
            $port = $validated['port'] ?? config('zkteco.port');
            $timeout = ['sec' => (int) config('zkteco.timeout', 2), 'usec' => 0];

            $machine = new ZKTeco($ip, $port);
            socket_set_option($machine->_zkclient, SOL_SOCKET, SO_RCVTIMEO, $timeout);
            socket_set_option($machine->_zkclient, SOL_SOCKET, SO_SNDTIMEO, $timeout);

            if ($machine->connect()) {
                $machine->disconnect();
                return response()->json(['status' => 'connected']);
            }

            $machine->disconnect();
            return response()->json(['status' => 'unreachable'], 503);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
   }

}
