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
            <label for="mid_name" class="form-label">Middle Name</label>
            <input type="text" name="mid_name" class="form-control @error('mid_name') is-invalid @enderror" value="{{ old('mid_name', $employee->mid_name) }}">
            @error('mid_name')
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
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->email) }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="city" class="form-label">City</label>
            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $employee->city) }}">
            @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="province" class="form-label">Province</label>
            <input type="text" name="province" class="form-control @error('province') is-invalid @enderror" value="{{ old('province', $employee->province) }}">
            @error('province')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="building_name" class="form-label">Building Name</label>
            <input type="text" name="building_name" class="form-control @error('building_name') is-invalid @enderror" value="{{ old('building_name', $employee->building_name) }}">
            @error('building_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="floor" class="form-label">Floor</label>
            <input type="text" name="floor" class="form-control @error('floor') is-invalid @enderror" value="{{ old('floor', $employee->floor) }}">
            @error('floor')
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
            <label for="housing_type" class="form-label">Housing Type</label>
            <select name="housing_type" id="housing_type" class="form-control @error('housing_type') is-invalid @enderror">
                <option value="">Select Housing Type</option>
                <option value="rent" {{ old('housing_type', $employee->housing_type) == 'rent' ? 'selected' : '' }}>Rent</option>
                <option value="own" {{ old('housing_type', $employee->housing_type) == 'own' ? 'selected' : '' }}>Own</option>
            </select>
            @error('housing_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div id="owner_name_field" class="mb-3 rent-field" style="display: none;">
            <label for="owner_name" class="form-label">Owner Name <span class="text-danger">*</span></label>
            <input type="text" name="owner_name" id="owner_name" class="form-control @error('owner_name') is-invalid @enderror" value="{{ old('owner_name', $employee->owner_name) }}">
            @error('owner_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div id="owner_mobile_field" class="mb-3 rent-field" style="display: none;">
            <label for="owner_mobile_number" class="form-label">Owner Mobile Number <span class="text-danger">*</span></label>
            <input type="text" name="owner_mobile_number" id="owner_mobile_number" class="form-control @error('owner_mobile_number') is-invalid @enderror" value="{{ old('owner_mobile_number', $employee->owner_mobile_number) }}">
            @error('owner_mobile_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="acc_number" class="form-label">Account Number</label>
            <input type="number" name="acc_number" class="form-control @error('acc_number') is-invalid @enderror" value="{{ old('acc_number', $employee->acc_number) }}">
            @error('acc_number')
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

        <div class="mb-3">
            <label class="form-label">Employee Documents</label>
            <div id="attachments-container">
                @if(isset($employee->attachments) && count($employee->attachments))
                    @foreach($employee->attachments as $i => $attachment)
                        <div class="attachment-item border p-3 mb-3" style="border-radius: 8px; background-color: #f8f9fa;">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Document Type</label>
                                    <select name="attachments[{{ $i }}][type]" class="form-control attachment-type">
                                        <option value="">Select Document Type</option>
                                        <option value="CV" {{ $attachment->type == 'CV' ? 'selected' : '' }}>CV</option>
                                        <option value="Cover Letter" {{ $attachment->type == 'Cover Letter' ? 'selected' : '' }}>Cover Letter</option>
                                        <option value="ID" {{ $attachment->type == 'ID' ? 'selected' : '' }}>ID</option>
                                        <option value="Passport" {{ $attachment->type == 'Passport' ? 'selected' : '' }}>Passport</option>
                                        <option value="Probation Contract" {{ $attachment->type == 'Probation Contract' ? 'selected' : '' }}>Probation Contract</option>
                                        <option value="Employment Contract" {{ $attachment->type == 'Employment Contract' ? 'selected' : '' }}>Employment Contract</option>
                                        <option value="ID Papers" {{ $attachment->type == 'ID Papers' ? 'selected' : '' }}>ID Papers</option>
                                        <option value="Salary Slip" {{ $attachment->type == 'Salary Slip' ? 'selected' : '' }}>Salary Slip</option>
                                        <option value="Others" {{ $attachment->type == 'Others' ? 'selected' : '' }}>Others</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Document File</label>
                                    <a href="{{ $attachment->image }}" target="_blank">View File</a>
                                </div>
                                <div class="col-md-2 mb-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger btn-sm remove-attachment" style="display: none;">Remove</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <button type="button" id="add-attachment" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-plus"></i> Add Another Document
            </button>
            <small class="text-muted d-block mt-2">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG, GIF (Max: 10MB)</small>
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
    // Attachment dynamic add/remove (edit page)
    document.addEventListener('DOMContentLoaded', function() {
        let attachmentIndex = {{ isset($employee->attachments) ? count($employee->attachments) : 0 }};
        const attachmentsContainer = document.getElementById('attachments-container');
        const addAttachmentBtn = document.getElementById('add-attachment');
        function updateRemoveButtons() {
            const attachmentItems = document.querySelectorAll('.attachment-item');
            attachmentItems.forEach((item, index) => {
                const removeBtn = item.querySelector('.remove-attachment');
                if (attachmentItems.length > 1) {
                    removeBtn.style.display = 'block';
                } else {
                    removeBtn.style.display = 'none';
                }
            });
        }
        function createAttachmentItem(index) {
            return `
                <div class="attachment-item border p-3 mb-3" style="border-radius: 8px; background-color: #f8f9fa;">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Document Type</label>
                            <select name="attachments[${index}][type]" class="form-control attachment-type">
                                <option value="">Select Document Type</option>
                                <option value="CV">CV</option>
                                <option value="Cover Letter">Cover Letter</option>
                                <option value="ID">ID</option>
                                <option value="Passport">Passport</option>
                                <option value="Probation Contract">Probation Contract</option>
                                <option value="Employment Contract">Employment Contract</option>
                                <option value="ID Papers">ID Papers</option>
                                <option value="Salary Slip">Salary Slip</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Document File</label>
                            <input type="file" name="attachments[${index}][file]" class="form-control attachment-file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                        </div>
                        <div class="col-md-2 mb-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm remove-attachment">Remove</button>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <small class="text-muted">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG, GIF (Max: 10MB)</small>
                        </div>
                    </div>
                </div>
            `;
        }
        addAttachmentBtn.addEventListener('click', function() {
            const newAttachmentHTML = createAttachmentItem(attachmentIndex);
            attachmentsContainer.insertAdjacentHTML('beforeend', newAttachmentHTML);
            attachmentIndex++;
            updateRemoveButtons();
        });
        attachmentsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-attachment')) {
                e.target.closest('.attachment-item').remove();
                updateRemoveButtons();
            }
        });
        attachmentsContainer.addEventListener('change', function(e) {
            if (e.target.classList.contains('attachment-file')) {
                const file = e.target.files[0];
                if (file && file.size > 10 * 1024 * 1024) {
                    alert('File size must be less than 10MB');
                    e.target.value = '';
                }
            }
        });
        updateRemoveButtons();
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Image file to base64
        const imageInput = document.getElementById('imageInput');
        const imageBase64 = document.getElementById('imageBase64');
        const imagePreview = document.getElementById('imagePreview');
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
            const lookup_employee_type_id = form.lookup_employee_type_id.value.trim();
            const housing_type = form.housing_type.value.trim();
            const owner_name = form.owner_name ? form.owner_name.value.trim() : '';
            const owner_mobile_number = form.owner_mobile_number ? form.owner_mobile_number.value.trim() : '';

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
            // Image (base64) - not required for edit
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
            // Employment Type
            if (!lookup_employee_type_id) errors.push('Employment Type is required.');
            // Housing Type validation
            if (housing_type === 'rent') {
                if (!owner_name) errors.push('Owner Name is required when housing type is rent.');
                if (!owner_mobile_number) errors.push('Owner Mobile Number is required when housing type is rent.');
            }

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

        // Handle housing type change
        const housingTypeSelect = document.getElementById('housing_type');
        const ownerNameField = document.getElementById('owner_name_field');
        const ownerMobileField = document.getElementById('owner_mobile_field');
        const ownerNameInput = document.getElementById('owner_name');
        const ownerMobileInput = document.getElementById('owner_mobile_number');

        function toggleRentFields() {
            if (housingTypeSelect.value === 'rent') {
                ownerNameField.style.display = 'block';
                ownerMobileField.style.display = 'block';
                ownerNameInput.required = true;
                ownerMobileInput.required = true;
            } else {
                ownerNameField.style.display = 'none';
                ownerMobileField.style.display = 'none';
                ownerNameInput.required = false;
                ownerMobileInput.required = false;
                // Don't clear values on edit form in case user changes mind
            }
        }

        // Initialize on page load
        toggleRentFields();

        // Handle change event
        housingTypeSelect.addEventListener('change', toggleRentFields);
    });
    </script>
</div>
@endsection
