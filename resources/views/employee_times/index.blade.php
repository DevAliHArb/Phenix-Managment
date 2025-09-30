@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div style="width:100%">
    <div class="headerContainer">
        <h1>Employee Time Logs</h1>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
                <div style="display: flex; justify-content: flex-end; margin-bottom: 18px; gap: 10px;">
                        <!-- Import Button triggers modal -->
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">Import</button>
                        <!-- Export All Button triggers modal -->
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportAllModal">Export All</button>
                        <a href="{{ route('employee_times.create') }}" class="btn btn-primary">Add Time Log</a>
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
                                                {{ $emp->first_name }} {{ $emp->last_name }}
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
                    employee: `{{ optional($item->employee)->first_name }} {{ optional($item->employee)->last_name }}`,
                    date: `{{ $item->date }}`,
                    time_in: `{{ $item->clock_in }}`,
                    time_out: `{{ $item->clock_out }}`,
                    total_time: `{{ $item->total_time ?? '' }}`,
                    status: `{{ $item->off_day ? 'Yes' : 'No' }}`,
                    reason: `{{ $item->reason ?? '' }}`,
                    editUrl: `{{ route('employee_times.edit', $item->id) }}`,
                    deleteUrl: `{{ route('employee_times.destroy', $item->id) }}`
                },
                @endforeach
            ];

            $(function() {
                $("#employeeTimesGrid").dxDataGrid({
                    dataSource: employeeTimesData,
                    columns: [
                        { dataField: "id", caption: "ID", width: 60, allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
                        { dataField: "employee", caption: "Employee", allowFiltering: true, headerFilter: { allowSearch: true } },
                        { dataField: "date", caption: "Date", allowFiltering: true, headerFilter: { allowSearch: true }, sortOrder: "desc" },
                        { dataField: "time_in", caption: "Time In", allowFiltering: true, headerFilter: { allowSearch: true }, cellTemplate: function(container, options) { $(container).text(formatTime(options.data.time_in)); } },
                        { dataField: "time_out", caption: "Time Out", allowFiltering: true, headerFilter: { allowSearch: true }, cellTemplate: function(container, options) { $(container).text(formatTime(options.data.time_out)); } },
                        { dataField: "total_time", caption: "Total Time", allowFiltering: true, headerFilter: { allowSearch: true } },
                        { dataField: "status", caption: "Off Day", allowFiltering: true, headerFilter: { allowSearch: true } },
                        { dataField: "reason", caption: "Notes", allowFiltering: true, headerFilter: { allowSearch: true } },
                        {
                            caption: "Actions",
                            cellTemplate: function(container, options) {
                                const editLink = `<a href="${options.data.editUrl}" style="color: #0d6efd; text-decoration: underline; margin-right: 10px;">Edit</a>`;
                                const deleteLink = `<a href="#" style="color: #dc3545; text-decoration: underline;" onclick="event.preventDefault(); if(confirm('Are you sure?')) { var f = document.createElement('form'); f.style.display='none'; f.method='POST'; f.action='${options.data.deleteUrl}'; f.innerHTML='<input type=\'hidden\' name=\'_token\' value=\'{{ csrf_token() }}\'><input type=\'hidden\' name=\'_method\' value=\'DELETE\'>'; document.body.appendChild(f); f.submit(); }">Delete</a>`;
                                $(container).append(editLink + deleteLink);
                            },
                            width: 180,
                            allowFiltering: false
                        }
                    ],
                    showBorders: true,
                    sorting: {
                        mode: "multiple"
                    },
                    onRowPrepared: function(e) {
                        if (e.rowType === 'data' && e.data.status === 'Yes') {
                            // Highlight off-day rows with a light orange/amber background
                            e.rowElement.css('background-color', '#fff3cd');
                            e.rowElement.css('color', '#856404');
                        }
                    },
                    paging: { pageSize: 10 },
                    pager: {
                        showPageSizeSelector: true,
                        allowedPageSizes: [5, 10, 20],
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
                    noDataText: 'No employee times found.'
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
</script>

@endsection
