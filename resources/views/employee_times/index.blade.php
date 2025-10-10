@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
    <style>
        .weekend { background: #fbe4d5 !important; }
        .vacation { background: #daeef3 !important; }
        .sickleave { background: #ffe6e6 !important; }
        .holiday { background: #e6ffe6 !important; }
    </style>
@endsection

@section('content')
<div style="width:100%">
    <div class="headerContainer">
        <h1> Punch Time Logs</h1>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
                <div style="display: flex; justify-content: flex-end; margin-bottom: 18px; gap: 10px;">
                        <!-- Import Button triggers modal -->
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">Import</button>
                        <!-- Export All Button triggers modal -->
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportAllModal">Export All</button>
                        <a href="{{ route('employee_times.create') }}" class="btn btn-primary">Add Punch Time</a>
                </div>

        <!-- Export All Modal -->
        <div class="modal fade" id="exportAllModal" tabindex="-1" aria-labelledby="exportAllModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportAllModalLabel">Export Employees Timesheets</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="exportAllForm">
                            <div class="mb-3">
                                <label for="exportAllMonth" class="form-label">Month</label>
                                <select class="form-select" id="exportAllMonth" name="month" required>
                                    <option value="">Select Month</option>
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="exportAllYear" class="form-label">Year</label>
                                <input type="number" class="form-control" id="exportAllYear" name="year" min="2000" max="2100" value="{{ now()->year }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Employees</label>
                                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 10px;">
                                    <div class="mb-2">
                                        <input type="checkbox" id="selectAllEmployees" checked>
                                        <label for="selectAllEmployees" style="font-weight: bold;">Select All</label>
                                    </div>
                                    <hr style="margin: 8px 0;">
                                    @foreach(\App\Models\Employee::all() as $emp)
                                        <div class="form-check">
                                            <input class="form-check-input employee-checkbox" type="checkbox" value="{{ $emp->id }}" id="emp_{{ $emp->id }}" checked>
                                            <label class="form-check-label" for="emp_{{ $emp->id }}">
                                                {{ $emp->first_name }} {{ $emp->mid_name }} {{ $emp->last_name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="form-text text-muted">Select employees to export. All are selected by default.</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmExportAllBtn" class="btn btn-success">Export</button>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Punch Time (Excel)</h5>
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
                            <!-- Progress bar -->
                            <div id="import-progress-container" class="d-none mt-3">
                                <div class="progress">
                                    <div id="import-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="importSubmitBtn" class="btn btn-primary">
                                <span id="importBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span id="importBtnText">Import</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    
        <div id="employeeTimesGrid"></div>
        @push('scripts')
        <script>
            // Import form logic
            document.addEventListener('DOMContentLoaded', function() {
                const importForm = document.getElementById('importForm');
                if(importForm) {
                    importForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(importForm);
                        const errorsDiv = document.getElementById('import-errors');
                        const successDiv = document.getElementById('import-success');
                        const progressContainer = document.getElementById('import-progress-container');
                        const progressBar = document.getElementById('import-progress-bar');
                        const importBtnSpinner = document.getElementById('importBtnSpinner');
                        const importBtnText = document.getElementById('importBtnText');
                        const importSubmitBtn = document.getElementById('importSubmitBtn');
                        errorsDiv.classList.add('d-none');
                        successDiv.classList.add('d-none');
                        progressContainer.classList.remove('d-none');
                        progressBar.style.width = '0%';
                        progressBar.textContent = '0%';
                        importBtnSpinner.classList.remove('d-none');
                        importBtnText.textContent = 'Importing...';
                        importSubmitBtn.disabled = true;

                        let progressKey = null;
                        let pollInterval = null;

                        // Use XMLHttpRequest for progress
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', importForm.action, true);
                        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('input[name="_token"]').value);
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4) {
                                importBtnSpinner.classList.add('d-none');
                                importBtnText.textContent = 'Import';
                                importSubmitBtn.disabled = false;
                                setTimeout(() => {
                                    progressContainer.classList.add('d-none');
                                    progressBar.style.width = '0%';
                                    progressBar.textContent = '0%';
                                }, 1000);
                                let data;
                                try {
                                    data = JSON.parse(xhr.responseText);
                                } catch (e) {
                                    data = { status: 'error', message: 'Import failed. Please try again.' };
                                }
                                if (pollInterval) clearInterval(pollInterval);
                                progressBar.style.width = '100%';
                                progressBar.textContent = '100%';
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
                            }
                        };
                        xhr.onerror = function() {
                            importBtnSpinner.classList.add('d-none');
                            importBtnText.textContent = 'Import';
                            importSubmitBtn.disabled = false;
                            errorsDiv.textContent = 'Import failed. Please try again.';
                            errorsDiv.classList.remove('d-none');
                            progressContainer.classList.add('d-none');
                            if (pollInterval) clearInterval(pollInterval);
                        };

                        xhr.send(formData);

                        // Poll progress endpoint every 500ms
                        pollInterval = setInterval(function() {
                            if (!progressKey) {
                                // Try to get progressKey from session (first poll)
                                progressKey = window.sessionStorage.getItem('import_progress_key');
                            }
                            fetch('/employee_times/import/progress' + (progressKey ? ('?progress_key=' + encodeURIComponent(progressKey)) : ''), {
                                method: 'GET',
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (typeof data.progress === 'number') {
                                    progressBar.style.width = data.progress + '%';
                                    progressBar.textContent = data.progress + '%';
                                }
                                if (data.progress >= 100 && pollInterval) {
                                    clearInterval(pollInterval);
                                }
                            })
                            .catch(() => {});
                        }, 500);

                        // After upload, store progressKey from response
                        xhr.onload = function() {
                            try {
                                const data = JSON.parse(xhr.responseText);
                                if (data.progress_key) {
                                    window.sessionStorage.setItem('import_progress_key', data.progress_key);
                                    progressKey = data.progress_key;
                                }
                            } catch (e) {}
                        };
                    });
                }

                // Export All logic
                // Use jQuery event delegation to ensure handler is attached
                $(document).on('click', '#confirmExportAllBtn', function() {
                    const exportAllBtn = this;
                    const month = document.getElementById('exportAllMonth').value;
                    const year = document.getElementById('exportAllYear').value;
                    const checkedEmployees = document.querySelectorAll('.employee-checkbox:checked');
                    let employeeIds = Array.from(checkedEmployees).map(checkbox => checkbox.value);
                    
                    if (!month || !year) {
                        alert('Please select both month and year.');
                        return;
                    }
                    if (employeeIds.length === 0) {
                        alert('Please select at least one employee.');
                        return;
                    }
                    
                    exportAllBtn.disabled = true;
                    exportAllBtn.textContent = 'Exporting...';
                    
                    // Export each employee individually
                    const exportPromises = employeeIds.map(employeeId => {
                        const employeeName = document.querySelector(`label[for="emp_${employeeId}"]`).textContent.trim().replace(/\s+/g, '_');
                        const url = `/employee_times/${employeeId}/export?month=${month}&year=${year}`;
                        
                        return fetch(url, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error(`Export failed for employee ${employeeName}: ${response.status} ${response.statusText}`);
                            return response.blob();
                        })
                        .then(blob => {
                            if (blob.size === 0) {
                                console.warn(`Exported file is empty for employee ${employeeName}`);
                                return null;
                            }
                            const fileName = `timesheet_${employeeName}_${year}_${month.padStart ? month.padStart(2, '0') : ('0'+month).slice(-2)}.pdf`;
                            return { blob, fileName, employeeName };
                        })
                        .catch(error => {
                            console.error(`Export error for employee ${employeeName}:`, error);
                            return { error: error.message, employeeName };
                        });
                    });
                    
                    Promise.allSettled(exportPromises)
                    .then(results => {
                        let successCount = 0;
                        let errorCount = 0;
                        const errors = [];
                        
                        results.forEach(result => {
                            if (result.status === 'fulfilled' && result.value) {
                                if (result.value.error) {
                                    errorCount++;
                                    errors.push(`${result.value.employeeName}: ${result.value.error}`);
                                } else if (result.value.blob) {
                                    successCount++;
                                    // Download the individual PDF
                                    const url = window.URL.createObjectURL(result.value.blob);
                                    const a = document.createElement('a');
                                    a.href = url;
                                    a.download = result.value.fileName;
                                    document.body.appendChild(a);
                                    a.click();
                                    setTimeout(() => {
                                        window.URL.revokeObjectURL(url);
                                        document.body.removeChild(a);
                                    }, 100);
                                }
                            } else {
                                errorCount++;
                                errors.push('Unknown error occurred');
                            }
                        });
                        
                        // Show summary message
                        let message = '';
                        if (successCount > 0) {
                            message += `Successfully exported ${successCount} timesheet(s).`;
                        }
                        if (errorCount > 0) {
                            message += ` Failed to export ${errorCount} timesheet(s).`;
                            if (errors.length > 0) {
                                message += `\nErrors:\n${errors.join('\n')}`;
                            }
                        }
                        
                        if (errorCount > 0) {
                            alert(message);
                        } else {
                            console.log(message);
                        }
                    })
                    .catch((err) => {
                        console.error('Export error:', err);
                        alert('Failed to export timesheets. ' + (err && err.message ? err.message : ''));
                    })
                    .finally(() => {
                        exportAllBtn.disabled = false;
                        exportAllBtn.textContent = 'Export';
                        const modal = bootstrap.Modal.getInstance(document.getElementById('exportAllModal'));
                        if(modal) modal.hide();
                    });
                });
                
                // Set default month/year when modal is shown
                $(document).on('show.bs.modal', '#exportAllModal', function() {
                    const now = new Date();
                    document.getElementById('exportAllMonth').value = (now.getMonth() + 1).toString();
                    document.getElementById('exportAllYear').value = now.getFullYear();
                });
                
                // Handle Select All checkbox
                $(document).on('change', '#selectAllEmployees', function() {
                    const isChecked = this.checked;
                    document.querySelectorAll('.employee-checkbox').forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                });
                
                // Handle individual checkboxes
                $(document).on('change', '.employee-checkbox', function() {
                    const allCheckboxes = document.querySelectorAll('.employee-checkbox');
                    const checkedCheckboxes = document.querySelectorAll('.employee-checkbox:checked');
                    const selectAllCheckbox = document.getElementById('selectAllEmployees');
                    
                    if (checkedCheckboxes.length === allCheckboxes.length) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else if (checkedCheckboxes.length === 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    }
                });
            });

            const employeeTimesData = [
                @foreach($employeeTimes as $item)
                {
                    id: {{ $item->id }},
                    employee: `{{ optional($item->employee)->first_name }} {{ optional($item->employee)->mid_name }} {{ optional($item->employee)->last_name }}`,
                    date: `{{ $item->date }}`,
                    time_in: `{{ $item->clock_in }}`,
                    time_out: `{{ $item->clock_out }}`,
                    total_time: `{{ $item->total_time ?? '' }}`,
                    status: `{{ $item->off_day ? 'Yes' : 'No' }}`,
                    vacation_type: `{{ $item->vacation_type ?? '' }}`,
                    reason: `{{ $item->reason ?? '' }}`,
                    editUrl: `{{ route('employee_times.edit', $item->id) }}`,
                    deleteUrl: `{{ route('employee_times.destroy', $item->id) }}`
                },
                @endforeach
            ];

            // Function to handle delete confirmation and submission
            function deleteItem(deleteUrl, csrfToken) {
                if (confirm('Are you sure you want to delete this item?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = deleteUrl;
                    form.style.display = 'none';
                    
                    // Add CSRF token
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);
                    
                    // Add DELETE method
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            }

            $(function() {
                $("#employeeTimesGrid").dxDataGrid({
                    dataSource: employeeTimesData,
                    columns: [
                        { dataField: "id", caption: "ID", width: 60, allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
                        { dataField: "employee", caption: "Employee", width: 200, allowFiltering: true, headerFilter: { allowSearch: true } },
                        { dataField: "date", caption: "Date", allowFiltering: true, headerFilter: { allowSearch: true }, sortOrder: "desc" },
                        { dataField: "time_in", caption: "Time In", allowFiltering: true, headerFilter: { allowSearch: true }, cellTemplate: function(container, options) { $(container).text(formatTime(options.data.time_in)); } },
                        { dataField: "time_out", caption: "Time Out", allowFiltering: true, headerFilter: { allowSearch: true }, cellTemplate: function(container, options) { $(container).text(formatTime(options.data.time_out)); } },
                        { dataField: "total_time", caption: "Total Time", allowFiltering: true, headerFilter: { allowSearch: true }, cellTemplate: function(container, options) { $(container).text(formatTotalTime(options.data.total_time)); } },
                        { 
                            dataField: "extra_minus", 
                            caption: "Extra-Minus", 
                            allowFiltering: true, 
                            headerFilter: { allowSearch: true },
                            cellTemplate: function(container, options) {
                                const extraMinusTime = calculateExtraMinus(options.data.total_time);
                                $(container).text(extraMinusTime);
                            }
                        },
                        { dataField: "status", caption: "Off Day", allowFiltering: true, headerFilter: { allowSearch: true } },
                        { dataField: "vacation_type", caption: "Status", allowFiltering: true, headerFilter: { allowSearch: true } },
                        { dataField: "reason", caption: "Reason", allowFiltering: true, headerFilter: { allowSearch: true } },
                        {
                            caption: "Actions",
                            cellTemplate: function(container, options) {
                                const editLink = `<a href="${options.data.editUrl}" style="color: #0d6efd; text-decoration: underline; margin-right: 10px;">Edit</a>`;
                                const deleteLink = `<a href="#" style="color: #dc3545; text-decoration: underline;" onclick="deleteItem('${options.data.deleteUrl}', '{{ csrf_token() }}')">Delete</a>`;
                                $(container).append(editLink + deleteLink);
                            },
                            width: 180,
                            allowFiltering: false
                        }
                    ],
                    allowColumnResizing: true,
                    columnResizingMode: "widget",
                    showBorders: true,
                    sorting: {
                        mode: "multiple"
                    },
                    onRowPrepared: function(e) {
                        if (e.rowType === 'data') {
                            // Apply colors based on vacation_type
                            const vacationType = e.data.vacation_type ? e.data.vacation_type.toLowerCase() : '';
                            
                            if (vacationType === 'off') {
                                e.rowElement.addClass('weekend');
                            } else if (vacationType === 'vacation') {
                                e.rowElement.addClass('vacation');
                            } else if (vacationType === 'holiday') {
                                e.rowElement.addClass('holiday');
                            } else if (vacationType === 'sick leave') {
                                e.rowElement.addClass('sickleave');
                            }
                        }
                    },
                    paging: { pageSize: 30 },
                    pager: {
                        showPageSizeSelector: true,
                        allowedPageSizes: [5, 10, 30, 60, 100],
                        showInfo: false,
                        showNavigationButtons: true,
                        visible: true
                    },
                    searchPanel: {
                        visible: true,
                        width: 240,
                        placeholder: 'Search...'
                    },
                    filterRow: {
                        visible: true,
                        applyFilter: 'auto'
                    },
                    headerFilter: {
                        visible: true
                    },
                    columnChooser: {
                        enabled: true,
                        mode: 'dragAndDrop',
                        title: 'Column Chooser',
                        emptyPanelText: 'Drag a column here to hide it'
                    },
                    allowColumnReordering: true,
                    summary: {
                        totalItems: [
                            {
                                column: 'id',
                                summaryType: 'count',
                                displayFormat: 'Total: {0} rows',
                                showInColumn: 'employee',
                                alignByColumn: true
                            },
                            {
                                summaryType: 'count',
                                displayFormat: 'Total: {0} rows'
                            }
                        ]
                    },
                    noDataText: 'No Punch Time found.'
                });
            });
        </script>
        @endpush
    </div>
</div>

<script>
// Function to format time from 24-hour to 12-hour AM/PM format
function formatTime(timeString) {
    if (!timeString || timeString === '') return '';
    
    // Parse the time string (assuming format like "11:30" or "14:30")
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours, 10);
    const minute = minutes || '00';
    
    // Convert to 12-hour format
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
    
    return `${displayHour}:${minute} ${ampm}`;
}

// Function to format total time to hh:mm format (remove seconds)
function formatTotalTime(timeString) {
    if (!timeString || timeString === '') return '';
    
    // If the time string has seconds (hh:mm:ss), remove them
    const timeParts = timeString.split(':');
    if (timeParts.length >= 2) {
        const hours = timeParts[0].padStart(2, '0');
        const minutes = timeParts[1].padStart(2, '0');
        return `${hours}:${minutes}`;
    }
    
    return timeString;
}
// Function to calculate extra/minus time compared to 9 hours
function calculateExtraMinus(totalTimeString) {
    if (!totalTimeString || totalTimeString === '') return '';
    
    // Parse total time string (format: "hh:mm:ss" or "hh:mm")
    const timeParts = totalTimeString.split(':');
    if (timeParts.length < 2) return '';
    
    const hours = parseInt(timeParts[0], 10) || 0;
    const minutes = parseInt(timeParts[1], 10) || 0;
    const seconds = parseInt(timeParts[2], 10) || 0;
    
    // Convert total time to minutes
    const totalMinutes = (hours * 60) + minutes + (seconds / 60);
    
    // 9 hours in minutes
    const standardMinutes = 9 * 60;
    
    // Calculate difference
    const diffMinutes = totalMinutes - standardMinutes;
    
    // Convert back to hours, minutes
    const absMinutes = Math.abs(diffMinutes);
    const diffHours = Math.floor(absMinutes / 60);
    const diffMins = Math.floor(absMinutes % 60);
    
    // Format with leading zeros
    const formattedHours = diffHours.toString().padStart(2, '0');
    const formattedMins = diffMins.toString().padStart(2, '0');
    
    // Add sign (always show + for zero or positive, - for negative)
    const sign = diffMinutes >= 0 ? '+' : '-';
    
    return `${sign}${formattedHours}:${formattedMins}`;
}
</script>

@endsection
