@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="headerContainer">
        <h1>Employee Time Logs</h1>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
                <div style="display: flex; justify-content: flex-end; margin-bottom: 18px; gap: 10px;">
                        <!-- Import Button triggers modal -->
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">Import</button>
                        <a href="{{ route('employee_times.create') }}" class="btn btn-primary">Add Time Log</a>
                </div>
        </div>
        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Employee Times (Excel)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="importForm" action="{{ route('employee_times.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="excel_file" class="form-label">Excel File (.xlsx, .xls, .csv)</label>
                                <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                            </div>
                            <div id="import-errors" class="alert alert-danger d-none"></div>
                            <div id="import-success" class="alert alert-success d-none"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const importForm = document.getElementById('importForm');
    if(importForm) {
        importForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(importForm);
            const errorsDiv = document.getElementById('import-errors');
            const successDiv = document.getElementById('import-success');
            errorsDiv.classList.add('d-none');
            successDiv.classList.add('d-none');
            fetch(importForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    successDiv.textContent = data.message;
                    successDiv.classList.remove('d-none');
                    errorsDiv.classList.add('d-none');
                    setTimeout(() => { window.location.reload(); }, 1200);
                } else {
                    let msg = data.message || '';
                    if(data.errors) {
                        msg += Object.values(data.errors).flat().join(' ');
                    }
                    errorsDiv.textContent = msg;
                    errorsDiv.classList.remove('d-none');
                }
            })
            .catch(err => {
                errorsDiv.textContent = 'Import failed. Please try again.';
                errorsDiv.classList.remove('d-none');
            });
        });
    }
});
</script>
@endsection
    <div style="overflow-x:auto;">
        <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee</th>
                <th>Account Number</th>
                <th>Date</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Total Time (min)</th>
                <th>Off Day</th>
                <th>Reason</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employeeTimes as $time)
            <tr>
                <td>{{ $time->id }}</td>
                <td>{{ optional($time->employee)->first_name }} {{ optional($time->employee)->last_name }}</td>
                <td>{{ $time->acc_number }}</td>
                <td>{{ $time->date }}</td>
                <td>{{ $time->clock_in }}</td>
                <td>{{ $time->clock_out }}</td>
                <td>{{ $time->total_time }}</td>
                <td>{{ $time->off_day ? 'Yes' : 'No' }}</td>
                <td>{{ $time->reason }}</td>
                <td>
                    <a href="{{ route('employee_times.edit', $time->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('employee_times.destroy', $time->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10">No employee time logs found.</td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    </div>
</div>
@endsection
