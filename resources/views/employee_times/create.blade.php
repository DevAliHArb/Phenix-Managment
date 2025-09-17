@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Add Employee Time Log</h1>
    <a href="{{ route('employee_times.index') }}" class="btn btn-secondary mb-3">Back</a>
    <form action="{{ route('employee_times.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="employee_id" class="form-label">Employee</label>
            <select name="employee_id" class="form-control" required>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="acc_number" class="form-label">Account Number</label>
            <input type="text" name="acc_number" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" name="date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="clock_in" class="form-label">Clock In</label>
            <input type="time" name="clock_in" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="clock_out" class="form-label">Clock Out</label>
            <input type="time" name="clock_out" class="form-control">
        </div>
        <div class="mb-3">
            <label for="total_time" class="form-label">Total Time (min)</label>
            <input type="number" name="total_time" class="form-control">
        </div>
        <div class="mb-3">
            <label for="off_day" class="form-label">Off Day</label>
            <input type="checkbox" name="off_day" value="1">
        </div>
        <div class="mb-3">
            <label for="reason" class="form-label">Reason</label>
            <input type="text" name="reason" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Add</button>
    </form>
</div>
@endsection
