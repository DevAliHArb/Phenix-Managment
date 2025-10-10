@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="headerContainer" >
    <h1>Add Yearly Day Truncations</h1>
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

    <form action="{{ route('yearly-vacations.store') }}" method="POST" novalidate id="yearlyVacationForm">
        @csrf
        <div class="formContainer">
        <div class="mb-3">
            <label for="employee_id" class="form-label">Employee</label>
            <select name="employee_id" class="form-control @error('employee_id') is-invalid @enderror" required>
                <option value="">Select Employee</option>
                @if(isset($employees) && count($employees) > 0)
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" 
                            {{ (old('employee_id') == $employee->id || (isset($selectedEmployeeId) && $selectedEmployeeId == $employee->id)) ? 'selected' : '' }}>
                            {{ $employee->first_name }} {{ $employee->mid_name }} {{ $employee->last_name }}
                        </option>
                    @endforeach
                @endif
            </select>
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
        </div>
        <div class="formContainer" style="margin-top:30px;">
             <a href="{{ route('yearly-vacations.index') }}" class="btn btn-secondary mb-3">Back</a>
            <button type="submit" class="btn btn-primary mb-3" id="addYearlyVacationBtn">Add</button>
        </div>
    </form>

    <!-- Error Popup Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorModalBody"></div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('yearlyVacationForm');
        const addBtn = document.getElementById('addYearlyVacationBtn');
        addBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
                // Log all form data key-value pairs for debugging
                for (let [key, value] of formData.entries()) {
                    console.log(key + ':', value);
                }
            const csrfToken = form.querySelector('input[name="_token"]').value;
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(async response => {
                console.log(response);
                if (response.ok) {
                    window.location.href = "{{ route('yearly-vacations.index') }}";
                } else if (response.status === 422) {
                    const data = await response.json();
                    showErrorModal(data.errors || ['Validation error.']);
                } else {
                    showErrorModal(['An unexpected error occurred.']);
                }
            })
            .catch(() => {
                showErrorModal(['An unexpected error occurred.']);
            });
        });

        function showErrorModal(errors) {
            const modalBody = document.getElementById('errorModalBody');
            modalBody.innerHTML = '<ul>' + errors.map(e => `<li>${e}</li>`).join('') + '</ul>';
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        }
    });
    </script>
</div>
@endsection
