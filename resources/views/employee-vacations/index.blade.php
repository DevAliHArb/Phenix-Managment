@extends('layouts.app')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Employee Vacations</h2>
        <a href="{{ route('employee-vacations.create') }}" class="btn btn-primary">Add Vacation</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee</th>
                <th>Date</th>
                <th>Reason</th>
                <th>Type</th>
                <th>Attachment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->employee->first_name ?? '' }} {{ $item->employee->last_name ?? '' }}</td>
                    <td>{{ $item->date }}</td>
                    <td>{{ $item->reason }}</td>
                    <td>{{ $item->type->name ?? '' }}</td>
                    <td>
                        @if($item->attachment)
                            <a href="{{ asset('attachments/'.$item->attachment) }}" target="_blank">View</a>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('employee-vacations.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('employee-vacations.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
