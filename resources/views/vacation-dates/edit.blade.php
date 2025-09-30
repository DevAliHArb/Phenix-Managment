@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Vacation Date</h2>
    <form method="POST" action="{{ route('vacation-dates.update', $vacation_date) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date" value="{{ old('date', $vacation_date->date) }}" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $vacation_date->name) }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('vacation-dates.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
