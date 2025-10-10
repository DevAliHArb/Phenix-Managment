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
        <h1>Edit Punch Time</h1>
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
    <form action="{{ route('employee_times.update', $employeeTime->id) }}" method="POST" novalidate>
        @csrf
        @method('PUT')
        <div class="formContainer">
            <div class="mb-3">
                <label for="employee_id" class="form-label">Employee</label>
                <select name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @if($employeeTime->employee_id == $employee->id) selected @endif>{{ $employee->first_name }} {{ $employee->mid_name }} {{ $employee->last_name }}</option>
                    @endforeach
                </select>
                @error('employee_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ $employeeTime->date }}" required>
                @error('date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="clock_in" class="form-label">Clock In</label>
                <input type="time" name="clock_in" class="form-control @error('clock_in') is-invalid @enderror" value="{{ $employeeTime->clock_in }}" required>
                @error('clock_in')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="clock_out" class="form-label">Clock Out</label>
                <input type="time" name="clock_out" class="form-control @error('clock_out') is-invalid @enderror" value="{{ $employeeTime->clock_out }}">
                @error('clock_out')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="total_time" class="form-label">Total Time</label>
                <input type="text" name="total_time" class="form-control @error('total_time') is-invalid @enderror" value="{{ $employeeTime->total_time }}" readonly>
                @error('total_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="vacation_type" class="form-label">Status</label>
                <select name="vacation_type" class="form-control @error('vacation_type') is-invalid @enderror">
                    <option value="">Select Status</option>
                    <option value="Attended" {{ $employeeTime->vacation_type == 'Attended' ? 'selected' : '' }}>Attended</option>
                    <option value="Off" {{ $employeeTime->vacation_type == 'Off' ? 'selected' : '' }}>Off</option>
                    <option value="Vacation" {{ $employeeTime->vacation_type == 'Vacation' ? 'selected' : '' }}>Vacation</option>
                    <option value="Holiday" {{ $employeeTime->vacation_type == 'Holiday' ? 'selected' : '' }}>Holiday</option>
                    <option value="Sick Leave" {{ $employeeTime->vacation_type == 'Sick Leave' ? 'selected' : '' }}>Sick Leave</option>
                    <option value="UnPaid" {{ $employeeTime->vacation_type == 'UnPaid' ? 'selected' : '' }}>UnPaid</option>
                </select>
                @error('vacation_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="reason" class="form-label">Reason</label>
                <!-- Text input for normal reasons -->
                <input type="text" id="reason-input" name="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ $employeeTime->reason }}">
                <!-- Dropdown for holiday reasons (hidden by default) -->
                <select id="reason-select" name="reason_select" class="form-control @error('reason') is-invalid @enderror" style="display: none;" required>
                    <option value="">Select Holiday</option>
                    @if(isset($vacationDates))
                        @foreach($vacationDates as $vacationDate)
                            <option value="{{ $vacationDate->name }}" data-date="{{ $vacationDate->date }}" {{ $employeeTime->reason == $vacationDate->name ? 'selected' : '' }}>
                                {{ $vacationDate->name }} ({{ \Carbon\Carbon::parse($vacationDate->date)->format('M d, Y') }})
                            </option>
                        @endforeach
                    @endif
                </select>
                @error('reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="formContainer" style="margin-top:30px;">
            <a href="{{ route('employee_times.index') }}" class="btn btn-secondary" style="margin-left:10px;">Back</a>
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.querySelector('input[name="date"]');
    const vacationTypeSelect = document.querySelector('select[name="vacation_type"]');
    const clockInInput = document.querySelector('input[name="clock_in"]');
    const clockOutInput = document.querySelector('input[name="clock_out"]');
    const totalTimeInput = document.querySelector('input[name="total_time"]');
    
    // Function to normalize time format (handles various database formats)
    function normalizeTimeFormat(timeValue) {
        if (!timeValue) return '';
        
        // Remove any extra whitespace
        timeValue = timeValue.trim();
        
        // If it's already in HH:MM format, return as is
        if (/^\d{2}:\d{2}$/.test(timeValue)) {
            return timeValue;
        }
        
        // If it's in HH:MM:SS format, extract HH:MM
        if (/^\d{2}:\d{2}:\d{2}$/.test(timeValue)) {
            return timeValue.substring(0, 5);
        }
        
        // If it's in H:MM format (single digit hour), pad with zero
        if (/^\d:\d{2}$/.test(timeValue)) {
            return '0' + timeValue;
        }
        
        // If it's in H:MM:SS format, pad hour and extract HH:MM
        if (/^\d:\d{2}:\d{2}$/.test(timeValue)) {
            return '0' + timeValue.substring(0, 4);
        }
        
        return timeValue;
    }
    
    // Store initial values to use as fallback
    let initialClockIn = '';
    let initialClockOut = '';
    
    if (clockInInput) {
        const rawClockIn = clockInInput.value || clockInInput.defaultValue || clockInInput.getAttribute('value') || '';
        initialClockIn = normalizeTimeFormat(rawClockIn);
        console.log('Initial Clock In - Raw:', rawClockIn, 'Normalized:', initialClockIn);
    }
    if (clockOutInput) {
        const rawClockOut = clockOutInput.value || clockOutInput.defaultValue || clockOutInput.getAttribute('value') || '';
        initialClockOut = normalizeTimeFormat(rawClockOut);
        console.log('Initial Clock Out - Raw:', rawClockOut, 'Normalized:', initialClockOut);
    }
    

    
    // Function to calculate time difference in HH:MM format
    function calculateTotalTime() {
        // Get current values - normalize and use current input value first, then fall back to initial values
        let clockInValue = clockInInput.value ? normalizeTimeFormat(clockInInput.value) : initialClockIn;
        let clockOutValue = clockOutInput.value ? normalizeTimeFormat(clockOutInput.value) : initialClockOut;
        
        // Check if current input values are empty (user deleted them)
        if (!clockInInput.value || !clockOutInput.value) {
            totalTimeInput.value = '00:00';
            console.log('Empty field detected - setting total time to 00:00'); // Debug log
            return;
        }
        
        console.log('Clock In Value:', clockInValue, 'Clock Out Value:', clockOutValue); // Debug log
        
        if (clockInValue && clockOutValue) {
            try {
                const clockIn = new Date('1970-01-01T' + clockInValue + ':00');
                const clockOut = new Date('1970-01-01T' + clockOutValue + ':00');
                
                // Check if dates are valid
                if (isNaN(clockIn.getTime()) || isNaN(clockOut.getTime())) {
                    console.error('Invalid time format - Clock In:', clockInValue, 'Clock Out:', clockOutValue);
                    totalTimeInput.value = '00:00';
                    return;
                }
                
                // Handle case where clock out is next day (past midnight)
                if (clockOut < clockIn) {
                    clockOut.setDate(clockOut.getDate() + 1);
                }
                
                const diffInMs = clockOut - clockIn;
                const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
                
                if (diffInMinutes >= 0) {
                    const hours = Math.floor(diffInMinutes / 60);
                    const minutes = diffInMinutes % 60;
                    const formattedTime = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
                    totalTimeInput.value = formattedTime;
                    console.log('Calculated Total Time:', formattedTime); // Debug log
                } else {
                    totalTimeInput.value = '00:00';
                }
            } catch (error) {
                console.error('Error calculating time:', error);
                totalTimeInput.value = '00:00';
            }
        } else {
            totalTimeInput.value = '00:00';
            console.log('Missing values - Clock In:', clockInValue, 'Clock Out:', clockOutValue); // Debug log
        }
    }
    
    // Add event listeners for time calculation
    if (clockInInput && clockOutInput && totalTimeInput) {
        // Add multiple event listeners to ensure calculation triggers
        clockInInput.addEventListener('change', calculateTotalTime);
        clockInInput.addEventListener('input', calculateTotalTime);
        clockOutInput.addEventListener('change', calculateTotalTime);
        clockOutInput.addEventListener('input', calculateTotalTime);
        
        // Use setTimeout to ensure DOM values are properly set
        setTimeout(function() {
            // Ensure time inputs have normalized values
            if (initialClockIn && clockInInput.value !== initialClockIn) {
                clockInInput.value = initialClockIn;
            }
            if (initialClockOut && clockOutInput.value !== initialClockOut) {
                clockOutInput.value = initialClockOut;
            }
            
            // Initialize total time field with existing value converted to HH:MM format
            const existingTotalTime = totalTimeInput.value || totalTimeInput.getAttribute('value') || '';
            if (existingTotalTime && existingTotalTime !== '') {
                // Check if it's already in time format or if it's in minutes
                if (existingTotalTime.includes(':')) {
                    // If it's in HH:MM:SS format, convert to HH:MM
                    if (existingTotalTime.split(':').length === 3) {
                        const timeParts = existingTotalTime.split(':');
                        totalTimeInput.value = `${timeParts[0]}:${timeParts[1]}`;
                    } else {
                        // Already in HH:MM format, keep as is
                        totalTimeInput.value = existingTotalTime;
                    }
                } else {
                    // Convert from minutes to HH:MM format
                    const totalMinutes = parseInt(existingTotalTime);
                    if (!isNaN(totalMinutes) && totalMinutes >= 0) {
                        const hours = Math.floor(totalMinutes / 60);
                        const mins = totalMinutes % 60;
                        totalTimeInput.value = `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
                    } else {
                        totalTimeInput.value = '00:00';
                    }
                }
            } else {
                // Calculate initial value if both times are present but no total_time exists
                calculateTotalTime();
            }
        }, 100);
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
        } else {
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
