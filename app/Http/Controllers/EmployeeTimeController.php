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
     * Export multiple employees' timesheets as individual PDFs in a zip file
     */
    public function exportMultipleTimesheets(Request $request)
    {
        $ids = $request->input('ids', []);
        $month = $request->input('month');
        $year = $request->input('year');
        if (!$month || !$year) {
            $now = Carbon::now();
            $month = $month ?: $now->month;
            $year = $year ?: $now->year;
        }

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'No employee IDs provided'], 400);
        }

        $zip = new \ZipArchive();
        $zipFile = tempnam(sys_get_temp_dir(), 'timesheets_') . '.zip';
        $zip->open($zipFile, \ZipArchive::CREATE);

        foreach ($ids as $employeeId) {
            $employee = Employee::with(['position'])->find($employeeId);
            if (!$employee) continue;
            $department = $employee->position ? $employee->position->name : '';
            $times = EmployeeTime::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->orderBy('date')->get();

            $timesheet = $times->map(function ($row) {
                $isWeekend = $row->vacation_type === 'Off' && $row->reason === 'Weekend';
                $isVacation = $row->off_day && $row->reason === 'vacation';
                $status = $row->off_day ? 'Off' : 'Attended';
                $totalHours = 0;
                if ($row->total_time) {
                    $parts = explode(':', $row->total_time);
                    $h = isset($parts[0]) ? (int)$parts[0] : 0;
                    $m = isset($parts[1]) ? (int)$parts[1] : 0;
                    $s = isset($parts[2]) ? (int)$parts[2] : 0;
                    $totalHours = round($h + ($m / 60) + ($s / 3600), 2);
                }
                $extra = $totalHours - 9;
                $extraFormatted = ($extra >= 0 ? '+' : '') . number_format($extra, 2);
                $notes = $row->off_day ? ($row->reason ?: 'Off') : ($row->reason ?: '');
                return [
                    'date' => $row->date,
                    'timein' => $row->clock_in,
                    'timeout' => $row->clock_out,
                    'totalhours' => $totalHours,
                    'status' => $status,
                    'extra' => $extraFormatted,
                    'notes' => $notes,
                    'is_weekend' => $isWeekend,
                    'vacation' => $isVacation,
                    'dayoff' => $row->off_day,
                ];
            });

            $data = [
                'department' => $department,
                'employee' => (object)[
                    'name' => $employee->first_name . ' ' . $employee->mid_name . ' ' . $employee->last_name,
                ],
                'timesheet' => $timesheet,
                'times' => $times,
            ];

            $pdf = Pdf::loadView('export.timesheet', $data);
            $filename = 'timesheet_' . $employee->id . '_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf';
            $zip->addFromString($filename, $pdf->output());
        }

        $zip->close();
        return response()->download($zipFile, 'timesheets_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.zip')->deleteFileAfterSend(true);
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
        ];

        $filename = 'timesheet_' . $employee->id . '_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf';
        $pdf = Pdf::loadView('export.timesheet', $data);
        return $pdf->download($filename);
    }
    public function index()
    {
        $employeeTimes = EmployeeTime::with('employee')->get();
        return view('employee_times.index', compact('employeeTimes'));
    }

    public function show($id)
    {
        $employeeTime = EmployeeTime::with('employee')->findOrFail($id);
        return view('employee_times.show', compact('employeeTime'));
    }

    public function create()
    {
        $employees = Employee::all();
        $vacationDates = VacationDate::orderBy('date')->get();
        return view('employee_times.create', compact('employees', 'vacationDates'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'date' => 'required|date',
                'clock_in' => 'nullable',
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
            EmployeeTime::create($validated);
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
}
