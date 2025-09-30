@php
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Time Sheet</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { display: flex; align-items: center; }
        .logo { height: 50px; margin-right: 20px; }
        .title { position: absolute; top: 5px; left: 50%; transform: translateX(-50%); font-size: 16px; font-weight: bold; text-align: center; justify-content: center; }
    /* .info { margin: 0px 0 10px 0; font-size: 12px; position: relative; display: flex; flex-direction: row;} */
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 5px 8px; text-align: center; font-size: 11px; }
        th { background: #f2f2f2; }
    .weekend { background: #fbe4d5; }
    .vacation { background: #daeef3; }
    .sickleave { background: #ffe6e6; }
    .holiday { background: #e6ffe6; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('optimumLogo.jpeg') }}" class="logo" alt="Logo">
        <span class="title">Time Sheet</span>
    </div>
    <table style="width:100%; border:none; margin-bottom:10px;">
        <tr>
            <td style="width:70%; text-align:left; border:none; padding:0 0 2px 0;"><strong>Manager Name:</strong> Ali Harb</td>
            <td style="width:30%; text-align:left; border:none; padding:0 0 2px 0;"><strong>Employee Name:</strong> {{ $employee->name }}</td>
        </tr>
        <tr>
            <td style="width:70%; text-align:left; border:none; padding:0 0 2px 0;">
                <strong>Date:</strong>
                {{ isset($month) && isset($year) 
                    ? (\Carbon\Carbon::create($year, $month, 1)->addMonth()->format('d/m/Y')) 
                    : (\Carbon\Carbon::now()->addMonth()->startOfMonth()->format('d/m/Y')) 
                }}
            </td>
            <td style="width:30%; text-align:left; border:none; padding:0 0 2px 0;"><strong>Position:</strong> {{ $department }}</td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Total Hours</th>
                <th>Status</th>
                <th>Extra-Minus</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
        @php
            // Build a map of date (Y-m-d) => row for fast lookup
            $rowMap = collect($timesheet)->keyBy(function($row) {
                return isset($row['date']) ? Carbon::parse($row['date'])->format('Y-m-d') : null;
            });
            $selectedMonth = isset($month) ? $month : Carbon::now()->month;
            $selectedYear = isset($year) ? $year : Carbon::now()->year;
            $daysInMonth = Carbon::create($selectedYear, $selectedMonth, 1)->daysInMonth;
        @endphp
        @for($d = 1; $d <= $daysInMonth; $d++)
            @php
                $dateObj = Carbon::create($selectedYear, $selectedMonth, $d);
                $dateStr = $dateObj->format('Y-m-d');
                $row = $rowMap->get($dateStr, []);
                $isWeekend = isset($row['is_weekend']) ? $row['is_weekend'] : (in_array($dateObj->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]));
                $vacationsArr = isset($vacations) ? $vacations : [];
                $sickleaveArr = isset($sickleave) ? $sickleave : [];
                $offdaysArr = isset($offdays) ? $offdays : [];
                $vacationType = null;
                $reason = '';
                $rowClass = '';
                // Find if this date is a vacation or sickleave and get the type
                if (in_array($dateStr, $vacationsArr)) {
                    $status = 'Vacation';
                    $vacationType = 'Vacation';
                    $reason = isset($row['notes']) && $row['notes'] ? $row['notes'] : (isset($row['reason']) ? $row['reason'] : '');
                    $rowClass = 'vacation';
                } elseif (in_array($dateStr, $sickleaveArr)) {
                    $status = 'Sick Leave';
                    $vacationType = 'Sick Leave';
                    $reason = isset($row['notes']) && $row['notes'] ? $row['notes'] : (isset($row['reason']) ? $row['reason'] : '');
                    $rowClass = 'sickleave';
                } elseif (in_array($dateStr, $offdaysArr)) {
                    $status = 'Holiday';
                    $vacationType = 'Holiday';
                    $reason = isset($row['notes']) && $row['notes'] ? $row['notes'] : (isset($row['name']) ? $row['name'] : '');
                    $rowClass = 'holiday';
                } elseif ($isWeekend) {
                    $status = 'Off';
                    $vacationType = 'Off';
                    $reason = 'Weekend';
                    $rowClass = 'weekend';
                } else {
                    $status = 'Attended';
                    $vacationType = null;
                    $reason = '';
                    $rowClass = '';
                }
                $extra = isset($row['totalhours']) ? (float)$row['totalhours'] - 9 : 0;
                $extraFormatted = ($extra >= 0 ? '+' : '') . number_format($extra, 2);
            @endphp
            <tr class="{{ $rowClass }}">
                <td>{{ $dateObj->format('d/m/Y') }}</td>
                <td>{{ $row['timein'] ?? '' }}</td>
                <td>{{ $row['timeout'] ?? '' }}</td>
                <td>{{ $row['totalhours'] ?? '' }}</td>
                <td>{{ $status }}</td>
                <td>{{ $extraFormatted }}</td>
                <td>{{ $reason }}</td>
            </tr>
        @endfor
        </tbody>
    </table>

    @php
        // Helper to sum time in H:i:s format
        function sumTimes($times) {
            $totalSeconds = 0;
            foreach ($times as $t) {
                if (!$t) continue;
                $parts = explode(':', $t);
                $h = isset($parts[0]) ? (int)$parts[0] : 0;
                $m = isset($parts[1]) ? (int)$parts[1] : 0;
                $s = isset($parts[2]) ? (int)$parts[2] : 0;
                $totalSeconds += $h * 3600 + $m * 60 + $s;
            }
            $h = floor($totalSeconds / 3600);
            $m = floor(($totalSeconds % 3600) / 60);
            $s = $totalSeconds % 60;
            return sprintf('%d:%02d:%02d', $h, $m, $s);
        }

    // $attendanceRequired is now passed from the controller
        $dailyHoursRequired = 9;
        $attendanceTotal = $timesheet->where('dayoff', false)->count();
        $vacationOffTotal = $timesheet->where('dayoff', true)->filter(function($row){
            // Not weekend (assuming is_weekend is set)
            return empty($row['is_weekend']);
        })->count();
        $leaveDays = $vacationOffTotal; // If you want to separate leave/vacation, adjust here
        $timeRequired = $attendanceTotal * $dailyHoursRequired;
        $extraTime = 0;
        $extraTimeSeconds = 0;
        foreach ($timesheet as $row) {
            $extra = isset($row['totalhours']) ? ($row['totalhours'] - 9) : 0;
            $extraTime += $extra;
            $extraTimeSeconds += $extra * 3600;
        }
        $extraSign = $extraTimeSeconds < 0 ? '-' : '';
        $extraTimeSeconds = abs($extraTimeSeconds);
        $extraH = floor($extraTimeSeconds / 3600);
        $extraM = floor(($extraTimeSeconds % 3600) / 60);
        $extraS = $extraTimeSeconds % 60;
        $extraTimeFormatted = $extraSign . sprintf('%d:%02d', $extraH, $extraM);

        $totalLoggedTime = sumTimes($timesheet->pluck('totalhours_raw') ?? []);
        // If totalhours_raw is not set, fallback to total_time from $times
        if ($totalLoggedTime == '0:00:00') {
            $totalLoggedTime = sumTimes($times->pluck('total_time') ?? []);
        }
    @endphp

    <div style="margin-top:10px;">
        <table style="width:100%; border:none; border-collapse:collapse;">
            <tr>
                <td style="width:20%; vertical-align:top; padding-right:10px; border:none; text-align:left;">
                    <div style="margin-bottom:10px;">Employee Signature:</div>
                    <div style="border-bottom:1px solid #333; width:90%; margin-bottom:18px;"></div>
                    <div style="margin-bottom:10px;">Manager Signature:</div>
                    <div style="border-bottom:1px solid #333; width:90%; margin-bottom:0;"></div>
                </td>
                <td style="width:23%; text-align:left; font-weight:bold; border:none;row-gap: 10px;">
                    Attendance Required<br>
                    Daily Hours Required<br>
                    Extra Time<br>
                    Time Required<br>
                </td>
                <td style="width:10%; background:#fbe4d5; text-align:center; font-weight:normal; border:none;row-gap: 10px;">
                    {{ $attendanceRequired }}<br>
                    {{ $dailyHoursRequired }}<br>
                    {{ $extraTimeFormatted }}<br>
                    {{ sprintf('%d:00', $timeRequired) }}<br>
                </td>
                <td style="width:20%; text-align:left; font-weight:bold; border:none;row-gap: 10px;">
                    Attendance Total<br>
                    Vacation Off Total<br>
                    Leave Days<br>
                    Time logged Total<br>
                </td>
                <td style="width:10%; background:#fbe4d5; text-align:center; font-weight:normal; border:none; row-gap: 10px;">
                    {{ $attendanceTotal }}<br>
                    {{ $vacationOffTotal }}<br>
                    {{ $leaveDays }}<br>
                    {{ $totalLoggedTime }}<br>
                </td>
            </tr>
        </table>
        <div style="margin-top:0px;">
            <p style="font-size: 12px">Managers Notes:</p>
            <hr>
        </div>
    </div>
</body>
</html>
