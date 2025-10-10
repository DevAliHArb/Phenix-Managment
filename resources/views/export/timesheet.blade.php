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
    .unpaid { background: #bcd6bc !important; }
    .sickleave { background: #ffe6e6; }
    .holiday { background: #e6ffe6; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('optimumLogo1.jpeg') }}" class="logo" alt="Logo">
        <span class="title">Time Sheet</span>
    </div>
    <table style="width:100%; border:none; margin-bottom:10px;">
        <tr>
            <td style="width:80%; text-align:left; border:none; padding:0 0 2px 0;"><strong>Employee Name:</strong> {{ $employee->name }}</td>
            <td style="width:20%; text-align:left; border:none; padding:0 0 2px 0;"><strong>Manager Name:</strong> Ali Harb</td>
        </tr>
        <tr>
            <td style="width:80%; text-align:left; border:none; padding:0 0 2px 0;"><strong>Position:</strong> {{ $department }}</td>
            <td style="width:20%; text-align:left; border:none; padding:0 0 2px 0;">
                <strong>Date:</strong>
                {{ isset($month) && isset($year) 
                    ? (\Carbon\Carbon::create($year, $month, 1)->addMonth()->format('d/m/Y')) 
                    : (\Carbon\Carbon::now()->addMonth()->startOfMonth()->format('d/m/Y')) 
                }}
            </td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th style="width: 24px;"></th>
                <th>Date</th>
                <th style="background:#ffe599;">&nbsp;</th>
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
                $unpaidArr = isset($unpaid) ? $unpaid : [];
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
                } elseif (in_array($dateStr, $unpaidArr)) {
                    $status = 'Unpaid';
                    $vacationType = 'Unpaid';
                    $reason = isset($row['notes']) && $row['notes'] ? $row['notes'] : (isset($row['reason']) ? $row['reason'] : '');
                    $rowClass = 'unpaid';
                } elseif ($isWeekend) {
                    $status = 'Off';
                    $vacationType = 'Off';
                    $reason = 'Weekend';
                    $rowClass = 'weekend';
                } elseif(empty($row['timein']) && empty($row['timeout'])) {
                    $status = 'Unknown';
                    $vacationType = null;
                    $reason = '';
                    $rowClass = 'unknown';
                } else {
                    $status = 'Attended';
                    $vacationType = null;
                    $reason = '';
                    $rowClass = '';
                }
                // If dayoff, extra is 0
                if (!empty($row['dayoff']) || (empty($row['timein']) && empty($row['timeout']))) {
                    $extra = 0;
                } else {
                    $extra = isset($row['totalhourscalc']) ? (float)$row['totalhourscalc'] - 9 : 0;
                }
                // Format extra as +H:MM or -H:MM, but keep empty for off days
                if (!empty($row['dayoff']) || (empty($row['timein']) && empty($row['timeout'])) || $rowClass === 'unknown') {
                    $extraFormatted = '';
                } else {
                    $extraSign = $extra >= 0 ? '+' : '-';
                    $extraMinutes = (int)round(abs($extra * 60));
                    $extraH = floor($extraMinutes / 60);
                    $extraM = $extraMinutes % 60;
                    $extraFormatted = $extraSign . $extraH . ':' . str_pad($extraM, 2, '0', STR_PAD_LEFT);
                }
            @endphp
            <tr class="{{ $rowClass }}">
                <td>{{ $d }}</td>
                <td>{{ $dateObj->format('d/m/Y') }}</td>
                <td style="background:#ffe599; width: 6px;"></td>
                <td>
                    @if(!empty($row['timein']))
                        {{ \Carbon\Carbon::parse($row['timein'])->format('g:i a') }}
                    @endif
                </td>
                <td>
                    @if(!empty($row['timeout']))
                        {{ \Carbon\Carbon::parse($row['timeout'])->format('g:i a') }}
                    @endif
                </td>
                <td>
                    @if(!empty($row['totalhours']))
                        @php
                            $parts = explode(':', $row['totalhours']);
                            $h = isset($parts[0]) ? (int)$parts[0] : 0;
                            $m = isset($parts[1]) ? (int)$parts[1] : 0;
                        @endphp
                        {{ $h }}:{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                    @endif
                </td>
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
        $attendanceTotal = collect($timesheet)->filter(function($row) use ($vacations, $sickleave, $offdays, $unpaid) {
            $date = isset($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('Y-m-d') : null;
            if (!$date) return false;
            if (in_array($date, $vacations ?? [])) return false;
            if (in_array($date, $sickleave ?? [])) return false;
            if (in_array($date, $offdays ?? [])) return false;
            if (in_array($date, $unpaid ?? [])) return false;
            $isWeekend = isset($row['is_weekend']) ? $row['is_weekend'] : false;
            if ($isWeekend) return false;
            // Exclude Unknown status
            if (empty($row['timein']) && empty($row['timeout'])) return false;
            return true;
        })->count();
        $vacationOffTotal = $timesheet->where('dayoff', true)->filter(function($row){
            // Not weekend (assuming is_weekend is set)
            return empty($row['is_weekend']);
        })->count();
        $leaveDays = $vacationOffTotal; // If you want to separate leave/vacation, adjust here
        $timeRequired = $attendanceTotal * $dailyHoursRequired;

        $extraTimeFormatted = 0;
        $totalLoggedTime = sumTimes($timesheet->pluck('totalhours_raw') ?? []);
        // If totalhours_raw is not set, fallback to total_time from $times
        if ($totalLoggedTime == '0:00:00') {
            $totalLoggedTime = sumTimes($times->pluck('total_time') ?? []);
        }
        // Format totalLoggedTime as H:i (00:00)
        list($h, $m, $s) = explode(':', $totalLoggedTime);
        $totalLoggedTimeFormatted = sprintf('%02d:%02d', $h, $m);

    // Calculate total extra-minus time (sum of all daily $extra values)
    $totalExtraMinutes = 0;
    foreach ($timesheet as $row) {
        if (!empty($row['dayoff'])) {
            $extra = 0;
        } else {
            $extra = isset($row['totalhourscalc']) ? (float)$row['totalhourscalc'] - 9 : 0;
        }
        $totalExtraMinutes += (int)round($extra * 60);
    }
    $extraSign = $totalExtraMinutes >= 0 ? '+' : '-';
    $absMinutes = abs($totalExtraMinutes);
    $extraH = floor($absMinutes / 60);
    $extraM = $absMinutes % 60;
    $extraTimeFormatted = $extraSign . $extraH . ':' . str_pad($extraM, 2, '0', STR_PAD_LEFT);
    @endphp

    <div style="margin-top:10px;">
        <table style="width:100%; border:none; border-collapse:collapse;">
            <tr>
                <td style="width:20%; vertical-align:top; padding-right:10px; border:none; text-align:left;">
                    <div style="margin-bottom:10px;">Employee Signature:</div>
                    <div style="border-bottom:1px solid #333; width:90%; margin-bottom:13px;height: 13px;"></div>
                    <div style="margin-bottom:10px;">Manager Signature:</div>
                    <div style="border-bottom:1px solid #333; width:90%; margin-bottom:0; height: 13px;"></div>
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
                    Holidays Total<br>
                    Vacations Total<br>
                    Sick Leaves Total<br>
                    Time logged Total<br>
                </td>
                <td style="width:10%; background:#fbe4d5; text-align:center; font-weight:normal; border:none; row-gap: 10px;">
                    {{-- Attendance Total: count of status Attended (exclude Unknown and Unpaid) --}}
                    {{ collect($timesheet)->filter(function($row) use ($vacations, $sickleave, $offdays, $unpaid) {
                        $date = isset($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('Y-m-d') : null;
                        if (!$date) return false;
                        if (in_array($date, $vacations ?? [])) return false;
                        if (in_array($date, $sickleave ?? [])) return false;
                        if (in_array($date, $offdays ?? [])) return false;
                        if (in_array($date, $unpaid ?? [])) return false;
                        $isWeekend = isset($row['is_weekend']) ? $row['is_weekend'] : false;
                        if ($isWeekend) return false;
                        // Exclude Unknown status
                        if (empty($row['timein']) && empty($row['timeout'])) return false;
                        return true;
                    })->count() }}<br>
                    {{-- Off Days Total: length of offdays --}}
                    {{ isset($offdays) ? count($offdays) : 0 }}<br>
                    {{-- Vacations Total: length of vacations + unpaid --}}
                    {{ (isset($vacations) ? count($vacations) : 0) + (isset($unpaid) ? count($unpaid) : 0) }}<br>
                    {{-- Sick Leaves Total: length of sickleave --}}
                    {{ isset($sickleave) ? count($sickleave) : 0 }}<br>
                    {{ $totalLoggedTimeFormatted }}<br>
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
