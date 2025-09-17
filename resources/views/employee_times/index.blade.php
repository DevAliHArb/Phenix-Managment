@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Employee Time Logs</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <a href="{{ route('employee_times.create') }}" class="btn btn-primary mb-3">Add Time Log</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee</th>
                <th>Account Number</th>
                <th>Date</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Total Time (min)</th>
                <th>Off Day</th>
                <th>Reason</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employeeTimes as $time)
            <tr>
                <td>{{ $time->id }}</td>
                <td>{{ optional($time->employee)->name }}</td>
                <td>{{ $time->acc_number }}</td>
                <td>{{ $time->date }}</td>
                <td>{{ $time->clock_in }}</td>
                <td>{{ $time->clock_out }}</td>
                <td>{{ $time->total_time }}</td>
                <td>{{ $time->off_day ? 'Yes' : 'No' }}</td>
                <td>{{ $time->reason }}</td>
                <td>
                    <a href="{{ route('employee_times.show', $time->id) }}" class="btn btn-info btn-sm">View</a>
                    <a href="{{ route('employee_times.edit', $time->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('employee_times.destroy', $time->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
