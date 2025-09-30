@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add Vacation Date</h2>
    <form method="POST" action="{{ route('vacation-dates.store') }}">
        @csrf
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date" value="{{ old('date') }}" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Add</button>
        <a href="{{ route('vacation-dates.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
