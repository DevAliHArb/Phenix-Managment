<?php
namespace App\Http\Controllers;

use App\Models\EmployeeTime;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class EmployeeTimeController extends Controller
{

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
            $employee = \App\Models\Employee::with(['position'])->find($employeeId);
            if (!$employee) continue;
            $department = $employee->position ? $employee->position->name : '';
            $times = \App\Models\EmployeeTime::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->orderBy('date')->get();

            $timesheet = $times->map(function ($row) {
                $isWeekend = false;
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
                    'name' => $employee->first_name . ' ' . $employee->last_name,
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
        $employee = \App\Models\Employee::with(['position'])->findOrFail($employeeId);
        $department = $employee->position ? $employee->position->name : '';
        $query = \App\Models\EmployeeTime::where('employee_id', $employeeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month);
        $times = $query->orderBy('date')->get();

        $timesheet = $times->map(function ($row) {
            $isWeekend = false; // You can enhance this logic if you have weekend info
            $isVacation = $row->off_day && $row->reason === 'vacation';
            $status = $row->off_day ? 'Off' : 'Attended';
            // Parse total_time as H:i:s string to decimal hours
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
                'name' => $employee->first_name . ' ' . $employee->last_name,
            ],
            'timesheet' => $timesheet,
            'times' => $times,
            'month' => $month,
            'year' => $year,
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
        $employees = \App\Models\Employee::all();
        return view('employee_times.create', compact('employees'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'acc_number' => 'required|string',
                'date' => 'required|date',
                'clock_in' => 'required',
                'clock_out' => 'nullable',
                'total_time' => 'nullable|integer',
                'off_day' => 'nullable|boolean',
                'reason' => 'nullable|string',
            ]);
            $validated['off_day'] = $request->has('off_day');
            \App\Models\EmployeeTime::create($validated);
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
        $employees = \App\Models\Employee::all();
        return view('employee_times.edit', compact('employeeTime', 'employees'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'acc_number' => 'sometimes|string',
                'date' => 'sometimes|date',
                'clock_in' => 'sometimes',
                'clock_out' => 'nullable',
                'total_time' => 'nullable|integer',
                'off_day' => 'nullable|boolean',
                'reason' => 'nullable|string',
            ]);
            $validated['off_day'] = $request->has('off_day');
            $model = \App\Models\EmployeeTime::findOrFail($id);
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
            $import = new \App\Imports\EmployeeTimeImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('excel_file'));

            // You can extend EmployeeTimeImport to collect results if needed
            return response()->json([
                'status' => 'success',
                'message' => 'Employee times import completed',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }
}
