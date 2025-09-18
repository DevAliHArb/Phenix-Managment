@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div class="container" style="max-width: 600px;">
    <div class="headerContainer" style="max-width: 600px;">
    <h1>Add Employee</h1>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary mb-3">Back</a>

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

    <form action="{{ route('employees.store') }}" method="POST" novalidate id="employeeForm">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Image URL</label>
            <input type="text" name="image" class="form-control @error('image') is-invalid @enderror" value="{{ old('image') }}">
            @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="position_id" class="form-label">Position ID</label>
            <input type="number" name="position_id" class="form-control @error('position_id') is-invalid @enderror" value="{{ old('position_id') }}" required>
            @error('position_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="birthdate" class="form-label">Birthdate</label>
            <input type="date" name="birthdate" class="form-control @error('birthdate') is-invalid @enderror" value="{{ old('birthdate') }}" required>
            @error('birthdate')
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
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
            @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="employment_type" class="form-label">Employment Type</label>
            <input type="text" name="employment_type" class="form-control @error('employment_type') is-invalid @enderror" value="{{ old('employment_type') }}" required>
            @error('employment_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
            <button type="submit" class="btn btn-primary w-100" id="addEmployeeBtn">Add</button>
        </form>

        <!-- Error Popup Modal -->
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="errorModalLabel">Validation Error</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="errorModalBody">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('employeeForm');
            const addBtn = document.getElementById('addEmployeeBtn');
            addBtn.addEventListener('click', function(e) {
                let errors = [];
                const name = form.name.value.trim();
                const image = form.image.value.trim();
                const position_id = form.position_id.value.trim();
                const birthdate = form.birthdate.value;
                const start_date = form.start_date.value;
                const end_date = form.end_date.value;
                const employment_type = form.employment_type.value.trim();

                // Name
                if (!name) errors.push('Name is required.');
                // Image URL
                if (!image) {
                    errors.push('Image URL is required.');
                } else {
                    try {
                        new URL(image);
                    } catch {
                        errors.push('Image URL must be a valid URL.');
                    }
                }
                // Position ID
                if (!position_id) errors.push('Position ID is required.');
                else if (isNaN(position_id) || parseInt(position_id) <= 0) errors.push('Position ID must be a positive number.');
                // Birthdate
                if (!birthdate) errors.push('Birthdate is required.');
                else if (new Date(birthdate) >= new Date()) errors.push('Birthdate must be before today.');
                // Start Date
                if (!start_date) errors.push('Start Date is required.');
                // End Date
                if (!end_date) errors.push('End Date is required.');
                else if (start_date && new Date(end_date) < new Date(start_date)) errors.push('End Date must be after or equal to Start Date.');
                // Employment Type
                if (!employment_type) errors.push('Employment Type is required.');

                if (errors.length > 0) {
                    e.preventDefault();
                    showErrorModal(errors);
                    return;
                }

                // AJAX submit
                e.preventDefault();
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': form.querySelector('[name=_token]').value
                    },
                    body: formData
                })
                .then(async response => {
                    if (response.ok) {
                        const data = await response.json();
                        if (data.success && data.redirect) {
                            window.location.href = data.redirect;
                        }
                    } else if (response.status === 422) {
                        const data = await response.json();
                        showErrorModal(data.errors || ['Validation failed.']);
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
    </form>
</div>
@endsection
