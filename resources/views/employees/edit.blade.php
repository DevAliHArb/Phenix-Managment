@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Edit Employee</h1>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary mb-3">Back</a>
    <form action="{{ route('employees.update', $employee->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ $employee->name }}" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Image URL</label>
            <input type="text" name="image" class="form-control" value="{{ $employee->image }}">
        </div>
        <div class="mb-3">
            <label for="position_id" class="form-label">Position</label>
            <input type="number" name="position_id" class="form-control" value="{{ $employee->position_id }}" required>
        </div>
        <div class="mb-3">
            <label for="birthdate" class="form-label">Birthdate</label>
            <input type="date" name="birthdate" class="form-control" value="{{ $employee->birthdate }}" required>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ $employee->start_date }}" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ $employee->end_date }}">
        </div>
        <div class="mb-3">
            <label for="employment_type" class="form-label">Employment Type</label>
            <input type="text" name="employment_type" class="form-control" value="{{ $employee->employment_type }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
