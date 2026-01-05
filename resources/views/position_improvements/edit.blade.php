@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
    <style>
        .form-control[readonly] {
            border: 2px dotted #dddddd !important;
            background-color: #fff !important;
            color: #acacac !important;
        }
    </style>
@endsection

@section('content')
<div class="container">
    <div class="headerContainer" >
    <h1>Edit Position Improvement</h1>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin-bottom:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('position-improvements.update', $positionImprovement->id) }}" method="POST" novalidate>
        @csrf
        @method('PUT')
        <div class="formContainer">
            <div class="mb-3">
                <label for="position_id" class="form-label">Position</label>
                <select name="position_id" class="form-control @error('position_id') is-invalid @enderror" required>
                    <option value="">Select Position</option>
                    @foreach($positions as $position)
                        <option value="{{ $position->id }}" {{ old('position_id', $positionImprovement->position_id) == $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
                    @endforeach
                </select>
                @error('position_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="employee_id" class="form-label">Employee</label>
                <select name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('employee_id', $positionImprovement->employee_id) == $employee->id ? 'selected' : '' }}>{{ $employee->first_name }} {{ $employee->mid_name }} {{ $employee->last_name }}</option>
                    @endforeach
                </select>
                @error('employee_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $positionImprovement->start_date) }}" required>
                @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $positionImprovement->end_date) }}">
                @error('end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="formContainer" style="margin-top:30px;">
             <a href="{{ route('position-improvements.index') }}" class="btn btn-secondary mb-3">Back</a>
            <button type="submit" class="btn btn-primary mb-3" id="updatePositionImprovementBtn">Update</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const endDateInput = document.querySelector('input[name="end_date"]');
    const updateBtn = document.getElementById('updatePositionImprovementBtn');
    const form = document.querySelector('form');
    
    if (endDateInput && updateBtn && form) {
        let originalEndDate = endDateInput.value;
        
        // Handle form submission
        form.addEventListener('submit', function(e) {
            const currentEndDate = endDateInput.value.trim();
            
            // Check if end date was cleared
            if (originalEndDate && originalEndDate.trim() !== '' && currentEndDate === '') {
                e.preventDefault();
                if (confirm('Clearing the end date will make this position improvement active and may affect other active records for this employee. Continue?')) {
                    form.submit();
                }
                return;
            }
            
            // Check if end date was added
            if ((!originalEndDate || originalEndDate.trim() === '') && currentEndDate !== '') {
                e.preventDefault();
                if (confirm('Adding an end date will make this position improvement inactive. Continue?')) {
                    form.submit();
                }
                return;
            }
        });
    }
});
</script>

@endsection
