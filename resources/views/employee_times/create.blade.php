
@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
    <style>
        .form-control[readonly] {
            border: 2px dotted #dddddd !important;
            background-color: #fff !important;
            color: #acacac !important;
        }
    </style>
@endsection

@section('content')
<div class="container">
    <div class="headerContainer">
        <h1>Add Punch Time</h1>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin-bottom:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('employee_times.store') }}" method="POST" novalidate>
        @csrf
        <div class="formContainer">
            <div class="mb-3">
                <label for="employee_ids" class="form-label">Employees (multi-select)</label>
                @php
                    $selectedEmployees = old('employee_ids', []);
                    if (!is_array($selectedEmployees)) {
                        $selectedEmployees = [$selectedEmployees];
                    }
                @endphp
                <select name="employee_ids[]" id="employee_ids" multiple class="form-control @error('employee_ids') is-invalid @enderror" required>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ in_array($employee->id, $selectedEmployees) ? 'selected' : '' }}>{{ $employee->first_name }} {{ $employee->mid_name }} {{ $employee->last_name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select more than one.</small>
                @error('employee_ids')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date') }}" required>
                @error('date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="clock_in" class="form-label">Clock In</label>
                <input type="time" name="clock_in" class="form-control @error('clock_in') is-invalid @enderror" value="{{ old('clock_in') }}" required>
                @error('clock_in')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="clock_out" class="form-label">Clock Out</label>
                <input type="time" name="clock_out" class="form-control @error('clock_out') is-invalid @enderror" value="{{ old('clock_out') }}">
                @error('clock_out')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="total_time" class="form-label">Total Time</label>
                <input type="text" name="total_time" class="form-control @error('total_time') is-invalid @enderror" value="{{ old('total_time') }}" readonly>
                @error('total_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="vacation_type" class="form-label">Status</label>
                <select name="vacation_type" class="form-control @error('vacation_type') is-invalid @enderror">
                    <option value="">Select Status</option>
                    <option value="Attended" {{ old('vacation_type') == 'Attended' ? 'selected' : '' }}>Attended</option>
                    <option value="Off" {{ old('vacation_type') == 'Off' ? 'selected' : '' }}>Off</option>
                    <option value="Vacation" {{ old('vacation_type') == 'Vacation' ? 'selected' : '' }}>Vacation</option>
                    <option value="Holiday" {{ old('vacation_type') == 'Holiday' ? 'selected' : '' }}>Holiday</option>
                    <option value="Sick Leave" {{ old('vacation_type') == 'Sick Leave' ? 'selected' : '' }}>Sick Leave</option>
                    <option value="Unpaid" {{ old('vacation_type') == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="Half Day Vacation" {{ old('vacation_type') == 'Half Day Vacation' ? 'selected' : '' }}>Half Day Vacation</option>
                </select>
                @error('vacation_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="reason" class="form-label">Reason</label>
                <!-- Text input for normal reasons -->
                <input type="text" id="reason-input" name="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason') }}">
                <!-- Dropdown for holiday reasons (hidden by default) -->
                <select id="reason-select" name="reason_select" class="form-control @error('reason') is-invalid @enderror" style="display: none;" required>
                    <option value="">Select Holiday</option>
                    @if(isset($vacationDates))
                        @foreach($vacationDates as $vacationDate)
                            <option value="{{ $vacationDate->name }}" data-date="{{ $vacationDate->date }}">
                                {{ $vacationDate->name }} ({{ \Carbon\Carbon::parse($vacationDate->date)->format('M d, Y') }})
                            </option>
                        @endforeach
                    @endif
                </select>
                @error('reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

         <div class="mb-3">
    <label class="form-label">Flagged</label>
    <input type="hidden" name="flagged" value="0">
    <div>
        <input type="checkbox" name="flagged" id="flagged" value="1" {{ old('flagged') ? 'checked' : '' }} style="width: 25px; height: 25px;">
    </div>
    @error('flagged')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

         <div class="mb-3">
    <label for="total_leave_diff" class="form-label">Total Leave Diff</label>
   <input type="text" name="total_leave_diff" class="form-control @error('total_leave_diff') is-invalid @enderror" value="{{ old('total_leave_diff', '00:00:00') }}">
    @error('total_leave_diff')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
             <div class="mb-3">
                <label for="total_break_diff" class="form-label">Total Break Diff</label>
                <input type="text" name="total_break_diff" class="form-control @error('total_break_diff') is-invalid @enderror" value="{{ old('total_break_diff', '00:00:00') }}">
                @error('total_break_diff')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="break_flag" class="form-label">Break Flag </label>
                <select name="break_flag" id="break_flag" class="form-control @error('break_flag') is-invalid @enderror">
                    <option value="" disabled>select Break Flag</option>

                    <option value="Only In" {{ old('break_flag') == 'Only In' ? 'selected' : '' }}>Only In</option>
                    <option value="Only Out" {{ old('break_flag') == 'Only Out' ? 'selected' : '' }}>Only Out</option>
                    <option value="pass" {{ old('break_flag', 'pass') == 'pass' ? 'selected' : '' }}>Pass</option>
                    <option value="No Break" {{ old('break_flag') == 'No Break' ? 'selected' : '' }}>No Break</option>
                </select>
                @error('break_flag')
                <div class="invalid_feedback">{{$message }}</div>
                @enderror
            </div>
        <div class="formContainer" style="margin-top:30px;">
            <a href="{{ route('employee_times.index') }}" class="btn btn-secondary" style="margin-left:10px;">Back</a>
            <button type="submit" class="btn btn-primary">Add</button>
        </div>
    </form>
</div>
 </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.querySelector('input[name="date"]');
    const vacationTypeSelect = document.querySelector('select[name="vacation_type"]');
    const clockInInput = document.querySelector('input[name="clock_in"]');
    const clockOutInput = document.querySelector('input[name="clock_out"]');
    const totalTimeInput = document.querySelector('input[name="total_time"]');

    // Store initial values to use as fallback
    let initialClockIn = '';
    let initialClockOut = '';

    if (clockInInput) {
        initialClockIn = clockInInput.value || clockInInput.defaultValue || clockInInput.getAttribute('value') || '';
    }
    if (clockOutInput) {
        initialClockOut = clockOutInput.value || clockOutInput.defaultValue || clockOutInput.getAttribute('value') || '';
    }

    // Allowed break duration in seconds (from work schedule, only when break_flag is 'pass')
    const allowedBreakMinutes = {{ isset($workSchedule) && $workSchedule->break_duration ? (int) $workSchedule->break_duration : 60 }};

    // Convert HH:MM or HH:MM:SS duration to seconds
    function hmsToSeconds(value) {
        if (!value) return 0;
        const parts = String(value).trim().split(':');
        const h = parseInt(parts[0], 10) || 0;
        const m = parseInt(parts[1], 10) || 0;
        const s = parseInt(parts[2], 10) || 0;
        return h * 3600 + m * 60 + s;
    }

    // Function to calculate total time in HH:MM:SS format
    function calculateTotalTime() {
        // Get current values - use current input value first, then fall back to initial values
        let clockInValue = clockInInput.value || initialClockIn;
        let clockOutValue = clockOutInput.value || initialClockOut;

        if (clockInValue && clockOutValue) {
            const clockIn = new Date('1970-01-01T' + clockInValue + ':00');
            const clockOut = new Date('1970-01-01T' + clockOutValue + ':00');

            // Handle case where clock out is next day (past midnight)
            if (clockOut < clockIn) {
                clockOut.setDate(clockOut.getDate() + 1);
            }

            let grossSeconds = Math.floor((clockOut - clockIn) / 1000);

            // Allowed break deducted only when break_flag is 'pass'
            let allowedBreakSeconds = 0;
            const breakFlag = document.querySelector('select[name="break_flag"]');
            if (breakFlag && breakFlag.value === 'pass') {
                allowedBreakSeconds = allowedBreakMinutes * 60;
            }

            const breakDiffSec = hmsToSeconds(document.querySelector('input[name="total_break_diff"]')?.value);
            const leaveDiffSec = hmsToSeconds(document.querySelector('input[name="total_leave_diff"]')?.value);

            const totalSeconds = Math.max(0, grossSeconds - allowedBreakSeconds - breakDiffSec - leaveDiffSec);

            const pad = (n) => String(n).padStart(2, '0');
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            totalTimeInput.value = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
        } else {
            totalTimeInput.value = '';
        }
    }

    // Add event listeners for time calculation
    if (clockInInput && clockOutInput && totalTimeInput) {
        // Add multiple event listeners to ensure calculation triggers
        clockInInput.addEventListener('change', calculateTotalTime);
        clockInInput.addEventListener('input', calculateTotalTime);
        clockOutInput.addEventListener('change', calculateTotalTime);
        clockOutInput.addEventListener('input', calculateTotalTime);

        // Recalculate when break/leave diff changes or break_flag changes
        document.querySelectorAll('input[name="total_break_diff"], input[name="total_leave_diff"]').forEach(function (input) {
            input.addEventListener('input', calculateTotalTime);
            input.addEventListener('change', calculateTotalTime);
            input.addEventListener('blur', calculateTotalTime);
        });
        const breakFlagSelect = document.querySelector('select[name="break_flag"]');
        if (breakFlagSelect) {
            breakFlagSelect.addEventListener('change', calculateTotalTime);
        }

        // Use setTimeout to ensure DOM values are properly set
        setTimeout(function() {
            calculateTotalTime();
        }, 100);
    }

    // Normalize a diff value to HH:MM:SS: plain numbers become hours (3 -> 03:00:00),
    // H:MM/H:MM:SS get zero-padded, existing HH:MM:SS stays as is.
    function normalizeDiff(value) {
        if (!value) return value;
        value = value.trim();
        if (/^\d{2}:\d{2}:\d{2}$/.test(value)) return value;
        const parts = String(value).split(':');
        const pad = (n) => (isNaN(n) ? '00' : String(n).padStart(2, '0'));
        return `${pad(parseInt(parts[0], 10))}:${pad(parseInt(parts[1], 10))}:${pad(parseInt(parts[2], 10))}`;
    }

    // Ensure total_break_diff and total_leave_diff never submit empty - fall back to 00:00:00
    const diffInputs = document.querySelectorAll('input[name="total_break_diff"], input[name="total_leave_diff"]');
    diffInputs.forEach(function (input) {
// Do NOT auto-format while typing - editing stays free. Only normalize when leaving the field.
        input.addEventListener('blur', function () {
            input.value = input.value.trim() ? normalizeDiff(input.value) : '00:00:00';
            calculateTotalTime();
        });
        input.addEventListener('change', function () {
            input.value = input.value.trim() ? normalizeDiff(input.value) : '00:00:00';
            calculateTotalTime();
        });
        // On leaving the field, pad each part to 2 digits and never allow empty.
        input.addEventListener('blur', function () {
            input.value = input.value.trim() ? normalizeDiff(input.value) : '00:00:00';
            calculateTotalTime();
        });
    });
    const createForm = document.querySelector('form');
    if (createForm) {
        createForm.addEventListener('submit', function () {
            diffInputs.forEach(function (input) {
                if (!input.value.trim()) {
                    input.value = '00:00:00';
                }
            });
        });
    }

    // Reason field handling
    const reasonInput = document.getElementById('reason-input');
    const reasonSelect = document.getElementById('reason-select');

    function handleVacationTypeChange() {
        const selectedType = vacationTypeSelect.value;
        const currentReason = reasonInput.value;

        if (selectedType === 'Off') {
            // Show text input, make it readonly with value "Weekend"
            reasonInput.style.display = 'block';
            reasonSelect.style.display = 'none';
            reasonInput.value = 'Weekend';
            reasonInput.readOnly = true;
            reasonInput.classList.add('form-control[readonly]');
            reasonInput.name = 'reason'; // Ensure input name is correct
            reasonSelect.name = 'reason_select'; // Change dropdown name so it doesn't submit
            reasonSelect.removeAttribute('required');
            reasonInput.removeAttribute('required');
        } else if (selectedType === 'Holiday') {
            // Show dropdown, hide text input, make it required
            reasonInput.style.display = 'none';
            reasonSelect.style.display = 'block';
            reasonSelect.name = 'reason'; // Change name to submit the dropdown value
            reasonInput.name = 'reason_temp'; // Change input name so it doesn't submit
            reasonSelect.setAttribute('required', 'required');
            reasonInput.removeAttribute('required');

        }
        else if (selectedType ==='Attended'){
        // Show text input, make it readonly with value "Weekend"
            reasonInput.style.display = 'block';
            reasonSelect.style.display = 'none';
            reasonInput.value = '';
            reasonInput.readOnly = true;
            reasonInput.classList.add('form-control[readonly]');
            reasonInput.name = 'reason'; // Ensure input name is correct
            reasonSelect.name = 'reason_select'; // Change dropdown name so it doesn't submit
            reasonSelect.removeAttribute('required');
            reasonInput.removeAttribute('required');

        }
        else {
            // Show normal text input
            reasonInput.style.display = 'block';
            reasonSelect.style.display = 'none';
            reasonInput.readOnly = false;
            reasonInput.classList.remove('form-control[readonly]');
            reasonInput.name = 'reason'; // Ensure input name is correct
            reasonSelect.name = 'reason_select'; // Change dropdown name so it doesn't submit
            reasonSelect.removeAttribute('required');
            reasonInput.removeAttribute('required');

            // Reset reason if it was "Weekend" and we're changing from "Off" to another type
            if (currentReason === 'Weekend') {
                reasonInput.value = '';
            }
        }
    }

    // Add event listener for vacation type changes
    if (vacationTypeSelect) {
        vacationTypeSelect.addEventListener('change', handleVacationTypeChange);
        // Initialize on page load
        handleVacationTypeChange();
    }

    // Handle dropdown selection for holidays
    if (reasonSelect) {
        reasonSelect.addEventListener('change', function() {
            // The selected value will be automatically submitted as the reason
        });
    }

    // Weekend/weekday vacation type logic
    if (dateInput && vacationTypeSelect) {
        dateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 6 = Saturday

            // If it's Saturday (6) or Sunday (0), set vacation type to "Off"
            if (dayOfWeek === 0 || dayOfWeek === 6) {
                vacationTypeSelect.value = 'Off';
                handleVacationTypeChange(); // Update reason field
            } else {
                // If it's a weekday (Monday-Friday), set vacation type to "Attended"
                vacationTypeSelect.value = 'Attended';
                handleVacationTypeChange(); // Update reason field
            }
        });
    }
});
</script>

@endsection
