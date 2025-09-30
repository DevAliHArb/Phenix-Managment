@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Edit Employee Vacation</h2>
    <form action="{{ route('employee-vacations.update', $item->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="employee_id" class="form-label">Employee</label>
            <select name="employee_id" id="employee_id" class="form-control" required>
                <option value="">Select Employee</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ old('employee_id', $item->employee_id) == $employee->id ? 'selected' : '' }}>
                        {{ $employee->first_name }} {{ $employee->last_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" name="date" id="date" class="form-control" value="{{ old('date', $item->date) }}" required>
        </div>
        <div class="mb-3">
            <label for="reason" class="form-label">Reason</label>
            <input type="text" name="reason" id="reason" class="form-control" value="{{ old('reason', $item->reason) }}" required>
        </div>
        <div class="mb-3">
            <label for="lookup_type_id" class="form-label">Type</label>
            <select name="lookup_type_id" id="lookup_type_id" class="form-control" required>
                <option value="">Select Type</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ old('lookup_type_id', $item->lookup_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="attachment" class="form-label">Attachment</label>
            <input type="file" name="attachment" id="attachment" class="form-control">
            @if($item->attachment)
                <a href="{{ asset('attachments/'.$item->attachment) }}" target="_blank">Current Attachment</a>
            @endif
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('employee-vacations.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
