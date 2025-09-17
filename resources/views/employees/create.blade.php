@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Add Employee</h1>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary mb-3">Back</a>
    <form action="{{ route('employees.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Image URL</label>
            <input type="text" name="image" class="form-control">
        </div>
        <div class="mb-3">
            <label for="position_id" class="form-label">Position</label>
            <input type="number" name="position_id" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="birthdate" class="form-label">Birthdate</label>
            <input type="date" name="birthdate" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control">
        </div>
        <div class="mb-3">
            <label for="employment_type" class="form-label">Employment Type</label>
            <input type="text" name="employment_type" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Add</button>
    </form>
</div>
@endsection
