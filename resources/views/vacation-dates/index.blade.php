@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Vacation Dates</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <a href="{{ route('vacation-dates.create') }}" class="btn btn-primary mb-3">Add Vacation Date</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vacations as $vacation)
                <tr>
                    <td>{{ $vacation->date }}</td>
                    <td>{{ $vacation->name }}</td>
                    <td>
                        <a href="{{ route('vacation-dates.edit', $vacation) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('vacation-dates.destroy', $vacation) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this vacation date?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
