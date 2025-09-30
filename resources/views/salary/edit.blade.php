@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="headerContainer" >
    <h1>Edit Salary</h1>
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
    <form action="{{ route('salary.update', $item->id) }}" method="POST" novalidate>
        @csrf
        @method('PUT')
        <div class="formContainer">
            <div class="mb-3">
                <label for="position_improvement_id" class="form-label">Position Improvement</label>
                <select name="position_improvement_id" class="form-control @error('position_improvement_id') is-invalid @enderror" required>
                    <option value="">Select Position Improvement</option>
                    @foreach(App\Models\PositionImprovement::all() as $pi)
                        <option value="{{ $pi->id }}" {{ old('position_improvement_id', $item->position_improvement_id) == $pi->id ? 'selected' : '' }}>
                            {{ optional($pi->employee)->first_name }} {{ optional($pi->employee)->last_name }} - {{ optional($pi->position)->name }}
                        </option>
                    @endforeach
                </select>
                @error('position_improvement_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="salary" class="form-label">Salary</label>
                <input type="number" name="salary" class="form-control @error('salary') is-invalid @enderror" value="{{ old('salary', $item->salary) }}" required>
                @error('salary')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                    <option value="1" {{ old('status', $item->status) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', $item->status) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="formContainer" style="margin-top:30px;">
             <a href="{{ route('position-improvements.index') }}" class="btn btn-secondary mb-3">Back</a>
            <button type="submit" class="btn btn-primary mb-3" id="updateSalaryBtn">Update</button>
        </div>
    </form>
</div>
@endsection
