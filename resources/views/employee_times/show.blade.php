@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Employee Time Log Details</h1>
    <a href="{{ route('employee_times.index') }}" class="btn btn-secondary mb-3">Back</a>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ optional($employeeTime->employee)->name }}</h5>
            <p><strong>Account Number:</strong> {{ $employeeTime->acc_number }}</p>
            <p><strong>Date:</strong> {{ $employeeTime->date }}</p>
            <p><strong>Clock In:</strong> {{ $employeeTime->clock_in }}</p>
            <p><strong>Clock Out:</strong> {{ $employeeTime->clock_out }}</p>
            <p><strong>Total Time (min):</strong> {{ $employeeTime->total_time }}</p>
            <p><strong>Off Day:</strong> {{ $employeeTime->off_day ? 'Yes' : 'No' }}</p>
            <p><strong>Reason:</strong> {{ $employeeTime->reason }}</p>
        </div>
    </div>
</div>
@endsection
