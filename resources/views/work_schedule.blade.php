@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Work Schedule</h2>
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
                <input type="time" class="form-control" id="total_hours_per_day" name="total_hours_per_day" value="{{ old('total_hours_per_day', $schedule->total_hours_per_day ?? '') }}" disabled>
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
