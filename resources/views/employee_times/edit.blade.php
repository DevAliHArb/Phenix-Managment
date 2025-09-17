@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Edit Employee Time Log</h1>
    <a href="{{ route('employee_times.index') }}" class="btn btn-secondary mb-3">Back</a>
    <form action="{{ route('employee_times.update', $employeeTime->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="employee_id" class="form-label">Employee</label>
            <select name="employee_id" class="form-control" required>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" @if($employeeTime->employee_id == $employee->id) selected @endif>{{ $employee->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="acc_number" class="form-label">Account Number</label>
            <input type="text" name="acc_number" class="form-control" value="{{ $employeeTime->acc_number }}" required>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" name="date" class="form-control" value="{{ $employeeTime->date }}" required>
        </div>
        <div class="mb-3">
            <label for="clock_in" class="form-label">Clock In</label>
            <input type="time" name="clock_in" class="form-control" value="{{ $employeeTime->clock_in }}" required>
        </div>
        <div class="mb-3">
            <label for="clock_out" class="form-label">Clock Out</label>
            <input type="time" name="clock_out" class="form-control" value="{{ $employeeTime->clock_out }}">
        </div>
        <div class="mb-3">
            <label for="total_time" class="form-label">Total Time (min)</label>
            <input type="number" name="total_time" class="form-control" value="{{ $employeeTime->total_time }}">
        </div>
        <div class="mb-3">
            <label for="off_day" class="form-label">Off Day</label>
            <input type="checkbox" name="off_day" value="1" @if($employeeTime->off_day) checked @endif>
        </div>
        <div class="mb-3">
            <label for="reason" class="form-label">Reason</label>
            <input type="text" name="reason" class="form-control" value="{{ $employeeTime->reason }}">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
