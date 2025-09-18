@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>Employees</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div style="display: flex; justify-content: flex-end; margin-bottom: 18px;">
        <a href="{{ route('employees.create') }}" class="btn btn-primary">Add Employee</a>
    </div>
    <div style="overflow-x:auto;">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Image</th>
                    <th>Position</th>
                    <th>Birthdate</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Employment Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                <tr>
                    <td>{{ $employee->id }}</td>
                    <td>{{ $employee->name }}</td>
                    <td><img src="{{ $employee->image }}" alt="Image"></td>
                    <td>{{ optional($employee->position)->name }}</td>
                    <td>{{ $employee->birthdate }}</td>
                    <td>{{ $employee->start_date }}</td>
                    <td>{{ $employee->end_date }}</td>
                    <td>{{ $employee->employment_type }}</td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-info btn-sm" title="View"><span>&#128065;</span></a>
                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning btn-sm" title="Edit"><span>&#9998;</span></a>
                        <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')" title="Delete"><span>&#128465;</span></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; color:#888;">No employees found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
