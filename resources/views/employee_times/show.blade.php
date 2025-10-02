@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Punch Time Details</h2>
    <div class="card">
        <div class="card-body">
            <p><strong>Employee:</strong> {{ $employeeTime->employee->first_name ?? '' }} {{ $employeeTime->employee->last_name ?? '' }}</p>
            <p><strong>Date:</strong> {{ $employeeTime->date }}</p>
            <p><strong>Clock In:</strong> {{ $employeeTime->clock_in }}</p>
            <p><strong>Clock Out:</strong> {{ $employeeTime->clock_out }}</p>
            <p><strong>Total Time:</strong> {{ $employeeTime->total_time }}</p>
            <p><strong>Off Day:</strong> {{ $employeeTime->off_day ? 'Yes' : 'No' }}</p>
            <p><strong>Reason:</strong> {{ $employeeTime->reason }}</p>
            <p><strong>Vacation Type:</strong> {{ $employeeTime->vacation_type }}</p>
            <a href="{{ route('employee_times.edit', $employeeTime->id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('employee_times.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
