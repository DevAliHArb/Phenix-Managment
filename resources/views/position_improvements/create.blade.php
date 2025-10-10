@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
    <style>
        .form-control:disabled {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            opacity: 0.8;
            cursor: not-allowed;
        }
        .locked-field-notice {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
@endsection

@section('content')
<div class="container">
    <div class="headerContainer" >
    <h1>Add Position Improvement</h1>
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
    <form action="{{ route('position-improvements.store') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('position-improvements.index') }}">
        <div class="formContainer">
            <div class="mb-3">
                <label for="position_id" class="form-label">Position</label>
                <select name="position_id" class="form-control @error('position_id') is-invalid @enderror" required>
                    <option value="">Select Position</option>
                    @foreach($positions as $position)
                        <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
                    @endforeach
                </select>
                @error('position_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="employee_id" class="form-label">Employee</label>
                <select name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" 
                        {{ isset($lockEmployee) && $lockEmployee ? 'disabled' : '' }} required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" 
                            {{ (old('employee_id') == $employee->id) || ($selectedEmployeeId == $employee->id) ? 'selected' : '' }}>
                            {{ $employee->first_name }} {{ $employee->mid_name }} {{ $employee->last_name }}
                        </option>
                    @endforeach
                </select>
                @if(isset($lockEmployee) && $lockEmployee && isset($selectedEmployeeId))
                    <input type="hidden" name="employee_id" value="{{ $selectedEmployeeId }}">
                    <div class="locked-field-notice">
                        <i class="fas fa-lock"></i> This field is locked because it was pre-selected from the employee page.
                    </div>
                @endif
                @error('employee_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="formContainer" style="margin-top:30px;">
             <a href="{{ $returnUrl ?? route('position-improvements.index') }}" class="btn btn-secondary mb-3">Back</a>
            <button type="submit" class="btn btn-primary mb-3" >Add</button>
        </div>
    </form>
</div>
@endsection
