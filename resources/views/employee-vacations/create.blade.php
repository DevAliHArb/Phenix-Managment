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
        <h1>Add Transaction Day</h1>
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
    <form action="{{ route('employee-vacations.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="return_url" value="{{ $returnUrl ?? route('employee-vacations.index') }}">
        <div class="formContainer">
            <div class="mb-3">
                <label for="employee_id" class="form-label">Employee</label>
                <select name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" 
                        {{ isset($lockEmployee) && $lockEmployee ? 'disabled' : '' }} required>
                    <option value="">Select Employee</option>
                    @if(isset($employees) && count($employees) > 0)
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" 
                                {{ (old('employee_id') == $employee->id || (isset($selectedEmployeeId) && $selectedEmployeeId == $employee->id)) ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->mid_name }} {{ $employee->last_name }}
                            </option>
                        @endforeach
                    @else
                        @foreach(App\Models\Employee::all() as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->first_name }} {{ $employee->mid_name }} {{ $employee->last_name }}</option>
                        @endforeach
                    @endif
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
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date') }}" required>
                @error('date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="reason" class="form-label">Reason</label>
                <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason') }}" required>
                @error('reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="lookup_type_id" class="form-label">Type</label>
                <select name="lookup_type_id" id="lookup_type_id" class="form-control @error('lookup_type_id') is-invalid @enderror" required>
                    <option value="">Select Type</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ old('lookup_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('lookup_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3" id="attachment-field" style="display: none;">
                <label for="attachment" class="form-label">Attachment</label>
                <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror">
                @error('attachment')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ $returnUrl ?? route('employee-vacations.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('lookup_type_id');
    const attachmentField = document.getElementById('attachment-field');
    const attachmentInput = attachmentField.querySelector('input[type="file"]');

    function toggleAttachmentField() {
        const selectedValue = typeSelect.value;
        if (selectedValue === '32') {
            attachmentField.style.display = 'block';
        } else {
            attachmentField.style.display = 'none';
            // Clear the file input when hiding the field
            attachmentInput.value = '';
        }
    }

    // Check initial state (for old input or pre-selected values)
    toggleAttachmentField();

    // Listen for changes
    typeSelect.addEventListener('change', toggleAttachmentField);
});
</script>
@endsection
