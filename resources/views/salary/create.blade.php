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
    <h1>Add Salary</h1>
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
    <form action="{{ route('salary.store') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('salary.index') }}">
        <div class="formContainer">
            <div class="mb-3">
                <label for="position_improvement_id" class="form-label">Position Improvement</label>
                <select name="position_improvement_id" class="form-control @error('position_improvement_id') is-invalid @enderror" 
                        {{ isset($lockPositionImprovement) && $lockPositionImprovement ? 'disabled' : '' }} required>
                    <option value="">Select Position Improvement</option>
                    @foreach(App\Models\PositionImprovement::where('is_active', true)->get() as $pi)
                        <option value="{{ $pi->id }}" 
                            {{ (old('position_improvement_id') == $pi->id) || (isset($selectedPositionImprovementId) && $selectedPositionImprovementId == $pi->id) ? 'selected' : '' }}>
                            {{ optional($pi->employee)->first_name }} {{ optional($pi->employee)->mid_name }} {{ optional($pi->employee)->last_name }} - {{ optional($pi->position)->name }}
                        </option>
                    @endforeach
                </select>
                @if(isset($lockPositionImprovement) && $lockPositionImprovement && isset($selectedPositionImprovementId))
                    <input type="hidden" name="position_improvement_id" value="{{ $selectedPositionImprovementId }}">
                    <div class="locked-field-notice">
                        <i class="fas fa-lock"></i> This field is locked because it was pre-selected from the employee page.
                    </div>
                @endif
                @error('position_improvement_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="salary" class="form-label">Salary</label>
                <input type="number" name="salary" class="form-control @error('salary') is-invalid @enderror" value="{{ old('salary') }}" required>
                @error('salary')
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
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    
        <div class="formContainer" style="margin-top:30px;">
            <a href="{{ $returnUrl ?? route('salary.index') }}" class="btn btn-secondary mb-3">Back</a>
            <button type="submit" class="btn btn-primary mb-3" id="addSalaryBtn">Add</button>
        </div>
    </form>
</div>
@endsection
