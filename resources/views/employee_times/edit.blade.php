@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="headerContainer">
        <h1>Edit Employee Time Log</h1>
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
                        <option value="{{ $employee->id }}" @if($employeeTime->employee_id == $employee->id) selected @endif>{{ $employee->first_name }} {{ $employee->last_name }}</option>
                    @endforeach
                </select>
                @error('employee_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="acc_number" class="form-label">Account Number</label>
                <input type="text" name="acc_number" class="form-control @error('acc_number') is-invalid @enderror" value="{{ $employeeTime->acc_number }}" required>
                @error('acc_number')
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
                <label for="total_time" class="form-label">Total Time (min)</label>
                <input type="number" name="total_time" class="form-control @error('total_time') is-invalid @enderror" value="{{ $employeeTime->total_time }}">
                @error('total_time')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="off_day" class="form-label">Off Day</label>
                <input type="checkbox" name="off_day" value="1" @if($employeeTime->off_day) checked @endif>
            </div>
            <div class="mb-3">
                <label for="reason" class="form-label">Reason</label>
                <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ $employeeTime->reason }}">
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
@endsection
