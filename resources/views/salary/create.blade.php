@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
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
        <div class="formContainer">
            <div class="mb-3">
                <label for="position_improvement_id" class="form-label">Position Improvement</label>
                <select name="position_improvement_id" class="form-control @error('position_improvement_id') is-invalid @enderror" required>
                    <option value="">Select Position Improvement</option>
                    @foreach(App\Models\PositionImprovement::all() as $pi)
                        <option value="{{ $pi->id }}" {{ old('position_improvement_id') == $pi->id ? 'selected' : '' }}>
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
                <input type="number" name="salary" class="form-control @error('salary') is-invalid @enderror" value="{{ old('salary') }}" required>
                @error('salary')
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
            <button type="submit" class="btn btn-primary">Submit</button>

        </div>
    </form>
</div>
@endsection
