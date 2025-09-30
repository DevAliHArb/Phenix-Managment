@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Work Schedule</h2>
    <form method="POST" action="{{ route('work-schedule.update') }}">
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
                <input type="time" class="form-control" id="total_hours_per_day" name="total_hours_per_day" value="{{ old('total_hours_per_day', $schedule->total_hours_per_day ?? '') }}">
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
</div>
@endsection
