@extends('layouts.app')

@section('content')
<div class="container">
    <div>
        <!-- Vacation Modal and Button (added, does not modify existing code) -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="mb-0">Edit Work Schedule</h2>
    <button type="button" class="btn btn-success" id="openVacationModal">Add Employee Vacation</button>
</div>
<div class="modal fade" id="vacationModal" tabindex="-1" aria-labelledby="vacationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vacationModalLabel">Edit Employee Vacation Total</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="vacationForm">
                    <div class="mb-3">
                        <label for="vacationEmployee" class="form-label">Employee</label>
                        <select class="form-select" id="vacationEmployee" required>
                            <option value="">Select Employee</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="yearly_vacations_total" class="form-label">Yearly Vacations Total</label>
                        <input type="number" class="form-control" id="yearly_vacations_total" name="yearly_vacations_total" required step="0.1" min="0" max="9999999999.99" pattern="^\d{1,10}(\.\d{1,2})?$">
                    </div>
                    <div class="mb-3">
                        <label for="yearly_vacations_used" class="form-label">Yearly Vacations Used</label>
                        <input type="number" class="form-control" id="yearly_vacations_used" name="yearly_vacations_used" disabled step="0.1" min="0" max="9999999999.99" pattern="^\d{1,10}(\.\d{1,2})?$">
                    </div>
                    <div class="mb-3">
                        <label for="yearly_vacations_left" class="form-label">Yearly Vacations Left</label>
                        <input type="number" class="form-control" id="yearly_vacations_left" name="yearly_vacations_left" disabled step="0.1" min="0" max="9999999999.99" pattern="^\d{1,10}(\.\d{1,2})?$">
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Employees data for dropdown (from blade)
const employeesData = [
    @foreach(\App\Models\Employee::all() as $emp)
    {
        id: {{ $emp->id }},
        name: `{{ $emp->first_name }} {{ $emp->mid_name }} {{ $emp->last_name }}`,
        yearly_vacations_total: {{ $emp->yearly_vacations_total ?? 0 }},
        yearly_vacations_used: {{ $emp->yearly_vacations_used ?? 0 }},
        yearly_vacations_left: {{ $emp->yearly_vacations_left ?? 0 }},
    },
    @endforeach
];

document.getElementById('openVacationModal').addEventListener('click', function() {
    // Populate dropdown
    const select = document.getElementById('vacationEmployee');
    select.innerHTML = '<option value="">Select Employee</option>';
    employeesData.forEach(emp => {
        const opt = document.createElement('option');
        opt.value = emp.id;
        opt.textContent = emp.name;
        select.appendChild(opt);
    });
    // Reset fields
    document.getElementById('yearly_vacations_total').value = '';
    document.getElementById('yearly_vacations_used').value = '';
    document.getElementById('yearly_vacations_left').value = '';
    var modal = new bootstrap.Modal(document.getElementById('vacationModal'));
    modal.show();
});

document.getElementById('vacationEmployee').addEventListener('change', function() {
    const emp = employeesData.find(e => e.id == this.value);
    if (emp) {
        document.getElementById('yearly_vacations_total').value = emp.yearly_vacations_total;
        document.getElementById('yearly_vacations_used').value = emp.yearly_vacations_used;
        document.getElementById('yearly_vacations_left').value = emp.yearly_vacations_left;
    } else {
        document.getElementById('yearly_vacations_total').value = '';
        document.getElementById('yearly_vacations_used').value = '';
        document.getElementById('yearly_vacations_left').value = '';
    }
});

document.getElementById('vacationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const empId = document.getElementById('vacationEmployee').value;
    const total = document.getElementById('yearly_vacations_total').value;
    if (!empId || total === '') {
        alert('Please select employee and enter total.');
        return;
    }
    // Find the selected employee and clone their data
    const emp = employeesData.find(e => e.id == empId);
    if (!emp) {
        alert('Employee not found.');
        return;
    }
    // Prepare payload with all existing data, updating only yearly_vacations_total
    let first_name = '', mid_name = '', last_name = '';
    const nameParts = emp.name.split(' ');
    if (nameParts.length === 2) {
        [first_name, last_name] = nameParts;
    } else if (nameParts.length === 3) {
        [first_name, mid_name, last_name] = nameParts;
    } else if (nameParts.length > 3) {
        first_name = nameParts[0];
        last_name = nameParts[nameParts.length - 1];
        mid_name = nameParts.slice(1, -1).join(' ');
    }
    const payload = {
        ...emp,
        yearly_vacations_total: total,
        first_name,
        mid_name,
        last_name,
        image: emp.image ? emp.image : ''
    };
    fetch(`/employees/${empId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-HTTP-Method-Override': 'PUT'
        },
        body: JSON.stringify(payload)
    })
    .then(async response => {
        let data, text;
        try { text = await response.text(); data = JSON.parse(text); } catch { data = null; }
        if (response.ok && data) {
            var modal = bootstrap.Modal.getInstance(document.getElementById('vacationModal'));
            modal.hide();
            // Show notification (Bootstrap Toast or alert)
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3';
            toast.role = 'alert';
            toast.ariaLive = 'assertive';
            toast.ariaAtomic = 'true';
            toast.style.zIndex = 9999;
            toast.innerHTML = `<div class='d-flex'><div class='toast-body'>Vacation total updated successfully.</div><button type='button' class='btn-close btn-close-white me-2 m-auto' data-bs-dismiss='toast' aria-label='Close'></button></div>`;
            document.body.appendChild(toast);
            var bsToast = new bootstrap.Toast(toast, { delay: 2500 });
            bsToast.show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        } else {
            let msg = 'An error occurred.';
            if (data && data.errors) {
                msg = Object.values(data.errors).flat().join('<br>');
            } else if (data && data.message) {
                msg = data.message;
            } else if (text) {
                msg = `<pre style=\"white-space:pre-wrap;\">${text}</pre>`;
            }
            alert(msg);
        }
    })
    .catch((err) => {
        alert('An unexpected error occurred. ' + (err && err.message ? err.message : ''));
    });
});
</script>
    </div>
    <form id="workScheduleForm" method="POST" action="{{ route('work-schedule.update') }}">
        @csrf
        @method('PUT')
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Work Days</label>
            <div class="col-sm-10">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="monday" id="monday" value="1" {{ old('monday', $schedule->monday ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="monday">Monday</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="tuesday" id="tuesday" value="1" {{ old('tuesday', $schedule->tuesday ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="tuesday">Tuesday</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="wednesday" id="wednesday" value="1" {{ old('wednesday', $schedule->wednesday ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="wednesday">Wednesday</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="thursday" id="thursday" value="1" {{ old('thursday', $schedule->thursday ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="thursday">Thursday</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="friday" id="friday" value="1" {{ old('friday', $schedule->friday ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="friday">Friday</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="saturday" id="saturday" value="1" {{ old('saturday', $schedule->saturday ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="saturday">Saturday</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="sunday" id="sunday" value="1" {{ old('sunday', $schedule->sunday ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="sunday">Sunday</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <label for="start_time" class="col-sm-2 col-form-label">Start Time</label>
            <div class="col-sm-10">
                <input type="time" class="form-control" id="start_time" name="start_time" value="{{ old('start_time', $schedule->start_time ?? '') }}">
            </div>
        </div>
        <div class="row mb-3">
            <label for="end_time" class="col-sm-2 col-form-label">End Time</label>
            <div class="col-sm-10">
                <input type="time" class="form-control" id="end_time" name="end_time" value="{{ old('end_time', $schedule->end_time ?? '') }}">
            </div>
        </div>
        <div class="row mb-3">
            <label for="total_hours_per_day" class="col-sm-2 col-form-label">Total Hours/Day</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="total_hours_per_day" name="total_hours_per_day" value="{{ old('total_hours_per_day', $schedule->total_hours_per_day ?? '') }}" disabled>
            </div>
        </div>
        <div class="row mb-3">
            <label for="late_arrival" class="col-sm-2 col-form-label">Late Arrival (min)</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="late_arrival" name="late_arrival" value="{{ old('late_arrival', $schedule->late_arrival ?? 0) }}">
            </div>
        </div>
        <div class="row mb-3">
            <label for="early_leave" class="col-sm-2 col-form-label">Early Leave (min)</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="early_leave" name="early_leave" value="{{ old('early_leave', $schedule->early_leave ?? 0) }}">
            </div>
        </div>
        <div class="row mb-3">
            <label for="vacation_days_per_month" class="col-sm-2 col-form-label">Vacation Days/Month</label>
            <div class="col-sm-10">
                <input type="number" step="0.01" min="0" class="form-control" id="vacation_days_per_month" name="vacation_days_per_month" value="{{ old('vacation_days_per_month', isset($schedule) ? number_format($schedule->vacation_days_per_month ?? 0, 2, '.', '') : '0.00') }}">
            </div>
        </div>
                <button type="submit" class="btn btn-primary">Update</button>
        </form>

        <!-- Modal -->
        <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resultModalLabel">Result</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="resultModalBody">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <script>
    function pad(num) {
        return num.toString().padStart(2, '0');
    }
    function calcTotalHours() {
        const start = document.getElementById('start_time').value;
        const end = document.getElementById('end_time').value;
        if (start && end) {
            const [sh, sm] = start.split(':').map(Number);
            const [eh, em] = end.split(':').map(Number);
            let startMins = sh * 60 + sm;
            let endMins = eh * 60 + em;
            let diff = endMins - startMins;
            if (diff < 0) diff += 24 * 60; // handle overnight
            const hours = Math.floor(diff / 60);
            const mins = diff % 60;
            document.getElementById('total_hours_per_day').value = pad(hours) + ':' + pad(mins);
        } else {
            document.getElementById('total_hours_per_day').value = '';
        }
    }
    document.getElementById('start_time').addEventListener('input', calcTotalHours);
    document.getElementById('end_time').addEventListener('input', calcTotalHours);
    window.addEventListener('DOMContentLoaded', calcTotalHours);

    // AJAX form submit
    document.getElementById('workScheduleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        // Enable total_hours_per_day for submission
        formData.set('total_hours_per_day', document.getElementById('total_hours_per_day').value);
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData
        })
        .then(async response => {
            let data, text;
            try { text = await response.text(); data = JSON.parse(text); } catch { data = null; }
            if (response.ok && data) {
                showResultModal('Success', data.message || 'Work schedule updated successfully.');
            } else {
                let msg = 'An error occurred.';
                if (data && data.errors) {
                    msg = Object.values(data.errors).flat().join('<br>');
                } else if (data && data.message) {
                    msg = data.message;
                } else if (text) {
                    msg = `<pre style="white-space:pre-wrap;">${text}</pre>`;
                }
                msg = `Status: ${response.status} ${response.statusText}<br>` + msg;
                showResultModal('Error', msg);
            }
        })
        .catch((err) => {
            showResultModal('Error', 'An unexpected error occurred.<br>' + (err && err.message ? err.message : ''));
        });
    });

    function showResultModal(title, message) {
        document.getElementById('resultModalLabel').textContent = title;
        document.getElementById('resultModalBody').innerHTML = message;
        var modal = new bootstrap.Modal(document.getElementById('resultModal'));
        modal.show();
    }
    </script>
    </form>
</div>
@endsection
