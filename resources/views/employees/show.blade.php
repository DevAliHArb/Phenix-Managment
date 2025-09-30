@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Employee Details</h1>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary mb-3">Back</a>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $employee->first_name }} {{ $employee->mid_name }} {{ $employee->last_name }}</h5>
            <img src="{{ $employee->image }}" alt="Image" width="100">
            <p><strong>Position:</strong> {{ optional($employee->position)->name }}</p>
            <p><strong>Account Number:</strong> {{ $employee->acc_number }}</p>
            <p><strong>Birthdate:</strong> {{ $employee->birthdate }}</p>
            <p><strong>Start Date:</strong> {{ $employee->start_date }}</p>
            <p><strong>End Date:</strong> {{ $employee->end_date }}</p>
            <p><strong>Employment Type:</strong> {{ optional($employee->EmployeeType)->name }}</p>
        </div>
    </div>
</div>
@endsection
