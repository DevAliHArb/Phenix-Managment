@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="headerContainer" >
    <h1>Edit Employee</h1>
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

    <form action="{{ route('employees.update', $employee->id) }}" method="POST" novalidate id="employeeForm">
        @csrf
        @method('PUT')
        <div class="formContainer">
        <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $employee->first_name) }}" required>
            @error('first_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $employee->last_name) }}" required>
            @error('last_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $employee->address) }}" required>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="date_of_birth" class="form-label">Date of Birth</label>
            <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $employee->date_of_birth) }}" required>
            @error('date_of_birth')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->phone) }}" required>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="position_id" class="form-label">Current Position</label>
            <select name="position_id" class="form-control @error('position_id') is-invalid @enderror" required>
                <option value="">Select Position</option>
                @if(isset($positions) && count($positions) > 0)
                    @foreach($positions as $position)
                        <option value="{{ $position->id }}" {{ old('position_id', $employee->position_id) == $position->id ? 'selected' : '' }}>{{ $position->name ?? ($position->title ?? 'Position') }}</option>
                    @endforeach
                @endif
            </select>
            @error('position_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="lookup_employee_type_id" class="form-label">Employment Type</label>
            <select name="lookup_employee_type_id" class="form-control @error('lookup_employee_type_id') is-invalid @enderror" required>
                <option value="">Select Employment Type</option>
                @if(isset($employmentTypes) && count($employmentTypes) > 0)
                    @foreach($employmentTypes as $type)
                        <option value="{{ $type->id }}" {{ old('lookup_employee_type_id', $employee->lookup_employee_type_id ?? $employee->lookup_employee_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name ?? ($type->title ?? 'Type') }}</option>
                    @endforeach
                @endif
            </select>
            @error('lookup_employee_type_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                <option value="">Select Status</option>
                <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Image</label>
            <input type="file" accept="image/*" id="imageInput" class="form-control @error('image') is-invalid @enderror">
            <input type="hidden" name="image" id="imageBase64" value="{{ old('image', $employee->image) }}">
            @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div id="imagePreview" style="margin-top:10px;">
                @if($employee->image)
                    <img src="{{ $employee->image }}" alt="Preview" style="max-width:120px;max-height:120px;border-radius:8px;">
                @endif
            </div>
        </div>
        <div class="form-group mb-3">
            <label for="working_days"><strong>Working Days</strong></label>
            <div id="working_days">
                <div class="row mb-2">
                    <div class="col-md-4 col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="working_days[]" id="day-sunday" value="sunday" {{ (is_array(old('working_days')) ? in_array('sunday', old('working_days', [])) : ($employee->sunday ?? false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="day-sunday">Sunday</label>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="working_days[]" id="day-monday" value="monday" {{ (is_array(old('working_days')) ? in_array('monday', old('working_days', [])) : ($employee->monday ?? false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="day-monday">Monday</label>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="working_days[]" id="day-tuesday" value="tuesday" {{ (is_array(old('working_days')) ? in_array('tuesday', old('working_days', [])) : ($employee->tuesday ?? false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="day-tuesday">Tuesday</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="working_days[]" id="day-wednesday" value="wednesday" {{ (is_array(old('working_days')) ? in_array('wednesday', old('working_days', [])) : ($employee->wednesday ?? false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="day-wednesday">Wednesday</label>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="working_days[]" id="day-thursday" value="thursday" {{ (is_array(old('working_days')) ? in_array('thursday', old('working_days', [])) : ($employee->thursday ?? false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="day-thursday">Thursday</label>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="working_days[]" id="day-friday" value="friday" {{ (is_array(old('working_days')) ? in_array('friday', old('working_days', [])) : ($employee->friday ?? false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="day-friday">Friday</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="working_days[]" id="day-saturday" value="saturday" {{ (is_array(old('working_days')) ? in_array('saturday', old('working_days', [])) : ($employee->saturday ?? false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="day-saturday">Saturday</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $employee->start_date) }}" required>
            @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $employee->end_date) }}" required>
            @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="working_hours_from" class="form-label">Working Hours From</label>
            <input type="time" name="working_hours_from" class="form-control @error('working_hours_from') is-invalid @enderror" value="{{ old('working_hours_from', $employee->working_hours_from) }}" required>
            @error('working_hours_from')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="working_hours_to" class="form-label">Working Hours To</label>
            <input type="time" name="working_hours_to" class="form-control @error('working_hours_to') is-invalid @enderror" value="{{ old('working_hours_to', $employee->working_hours_to) }}" required>
            @error('working_hours_to')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="yearly_vacations_total" class="form-label">Yearly Vacations Total</label>
            <input type="number" name="yearly_vacations_total" class="form-control @error('yearly_vacations_total') is-invalid @enderror" value="{{ old('yearly_vacations_total', $employee->yearly_vacations_total) }}" required>
            @error('yearly_vacations_total')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="yearly_vacations_used" class="form-label">Yearly Vacations Used</label>
            <input type="number" name="yearly_vacations_used" class="form-control @error('yearly_vacations_used') is-invalid @enderror" value="{{ old('yearly_vacations_used', $employee->yearly_vacations_used) }}" required>
            @error('yearly_vacations_used')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="yearly_vacations_left" class="form-label">Yearly Vacations Left</label>
            <input type="number" name="yearly_vacations_left" class="form-control @error('yearly_vacations_left') is-invalid @enderror" value="{{ old('yearly_vacations_left', $employee->yearly_vacations_left) }}" required>
            @error('yearly_vacations_left')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="sick_leave_used" class="form-label">Sick Leave Used</label>
            <input type="number" name="sick_leave_used" class="form-control @error('sick_leave_used') is-invalid @enderror" value="{{ old('sick_leave_used', $employee->sick_leave_used) }}" required>
            @error('sick_leave_used')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="last_salary" class="form-label">Last Salary</label>
            <input type="number" step="0.01" name="last_salary" class="form-control @error('last_salary') is-invalid @enderror" value="{{ old('last_salary', $employee->last_salary) }}" required>
            @error('last_salary')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        </div>
        <div class="formContainer" style="margin-top:30px;">
            <a href="{{ route('employees.index') }}" class="btn btn-secondary mb-3">Back</a>
            <button type="submit" class="btn btn-primary mb-3" id="addEmployeeBtn">Update</button>
        </div>
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
        // Image file to base64
        const imageInput = document.getElementById('imageInput');
        const imageBase64 = document.getElementById('imageBase64');
        const imagePreview = document.getElementById('imagePreview');
        if(imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        imageBase64.value = evt.target.result;
                        imagePreview.innerHTML = '<img src="' + evt.target.result + '" alt="Preview" style="max-width:120px;max-height:120px;border-radius:8px;">';
                    };
                    reader.readAsDataURL(file);
                } else {
                    imageBase64.value = '';
                    imagePreview.innerHTML = '';
                }
            });
        }
        const form = document.getElementById('employeeForm');
        const addBtn = document.getElementById('addEmployeeBtn');
        addBtn.addEventListener('click', function(e) {
            let errors = [];
            const first_name = form.first_name.value.trim();
            const last_name = form.last_name.value.trim();
            const address = form.address.value.trim();
            const date_of_birth = form.date_of_birth.value;
            const phone = form.phone.value.trim();
            const image = form.image.value.trim();
            const position_id = form.position_id.value.trim();
            const working_days = form['working_days[]'];
            const start_date = form.start_date.value;
            const end_date = form.end_date.value;
            const status = form.status.value;
            const working_hours_from = form.working_hours_from.value;
            const working_hours_to = form.working_hours_to.value;
            const yearly_vacations_total = form.yearly_vacations_total.value;
            const yearly_vacations_used = form.yearly_vacations_used.value;
            const yearly_vacations_left = form.yearly_vacations_left.value;
            const sick_leave_used = form.sick_leave_used.value;
            const last_salary = form.last_salary.value;

            // First Name
            if (!first_name) errors.push('First Name is required.');
            // Last Name
            if (!last_name) errors.push('Last Name is required.');
            // Address
            if (!address) errors.push('Address is required.');
            // Date of Birth
            if (!date_of_birth) errors.push('Date of Birth is required.');
            else if (new Date(date_of_birth) >= new Date()) errors.push('Date of Birth must be before today.');
            // Phone
            if (!phone) errors.push('Phone is required.');
            // Image (base64)
            // if (!image) {
            //     errors.push('Image is required.');
            // } else if (!image.startsWith('data:image/')) {
            //     errors.push('Image must be a valid image file.');
            // }
            // Position ID
            if (!position_id) errors.push('Position ID is required.');
            else if (isNaN(position_id) || parseInt(position_id) <= 0) errors.push('Position ID must be a positive number.');
            // Working Days
            if (!working_days || (working_days.length === 0)) errors.push('At least one working day must be selected.');
            // Start Date
            if (!start_date) errors.push('Start Date is required.');
            // End Date
            if (!end_date) errors.push('End Date is required.');
            else if (start_date && new Date(end_date) < new Date(start_date)) errors.push('End Date must be after or equal to Start Date.');
            // Status
            if (!status) errors.push('Status is required.');
            // Working Hours From
            if (!working_hours_from) errors.push('Working Hours From is required.');
            // Working Hours To
            if (!working_hours_to) errors.push('Working Hours To is required.');
            // Yearly Vacations Total
            if (!yearly_vacations_total) errors.push('Yearly Vacations Total is required.');
            // Yearly Vacations Used
            if (!yearly_vacations_used) errors.push('Yearly Vacations Used is required.');
            // Yearly Vacations Left
            if (!yearly_vacations_left) errors.push('Yearly Vacations Left is required.');
            // Sick Leave Used
            if (!sick_leave_used) errors.push('Sick Leave Used is required.');
            // Last Salary
            if (!last_salary) errors.push('Last Salary is required.');

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
                    const data = await response.json();
                    if (data.success && data.redirect) {
                        window.location.href = data.redirect;
                    }
            })
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
