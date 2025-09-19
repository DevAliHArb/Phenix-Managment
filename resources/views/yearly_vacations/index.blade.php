@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="headerContainer" >
    <h1>Yearly Vacations</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div style="display: flex; justify-content: flex-end; margin-bottom: 18px;">
        <a href="{{ route('yearly-vacations.create') }}" class="btn btn-primary">Add Yearly Vacation</a>
    </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Reason</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($yearlyVacations as $vacation)
                <tr>
                    <td>{{ $vacation->id }}</td>
                    <td>{{ optional($vacation->employee)->first_name }} {{ optional($vacation->employee)->last_name }}</td>
                    <td>{{ $vacation->date }}</td>
                    <td>{{ $vacation->reason }}</td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('yearly-vacations.show', $vacation->id) }}" class="btn btn-info btn-sm" title="View"><span>&#128065;</span></a>
                        <a href="{{ route('yearly-vacations.edit', $vacation->id) }}" class="btn btn-warning btn-sm" title="Edit"><span>&#9998;</span></a>
                        <form action="{{ route('yearly-vacations.destroy', $vacation->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')" title="Delete"><span>&#128465;</span></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#888;">No yearly vacations found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
