@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="headerContainer" >
        <h1>Edit Employee Vacation</h1>
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
    <form action="{{ route('employee-vacations.update', $item->id) }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        <div class="formContainer">
            <div class="mb-3">
                <label for="employee_id" class="form-label">Employee</label>
                <select name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
                    <option value="">Select Employee</option>
                    @if(isset($employees) && count($employees) > 0)
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id', $item->employee_id) == $employee->id ? 'selected' : '' }}>{{ $employee->first_name }} {{ $employee->last_name }}</option>
                        @endforeach
                    @else
                        @foreach(App\Models\Employee::all() as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id', $item->employee_id) == $employee->id ? 'selected' : '' }}>{{ $employee->first_name }} {{ $employee->last_name }}</option>
                        @endforeach
                    @endif
                </select>
                @error('employee_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $item->date) }}" required>
                @error('date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="reason" class="form-label">Reason</label>
                <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason', $item->reason) }}" required>
                @error('reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="lookup_type_id" class="form-label">Type</label>
                <select name="lookup_type_id" class="form-control @error('lookup_type_id') is-invalid @enderror" required>
                    <option value="">Select Type</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ old('lookup_type_id', $item->lookup_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('lookup_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="attachment" class="form-label">Attachment</label>
                <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror">
                @if($item->attachment)
                    <a href="{{ asset('attachments/'.$item->attachment) }}" target="_blank">Current Attachment</a>
                @endif
                @error('attachment')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('employee-vacations.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
