@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
    <style>
        .weekend { background: #fbe4d5 !important; }
        .vacation { background: #daeef3 !important; }
        .sickleave { background: #ffe6e6 !important; }
        .holiday { background: #e6ffe6 !important; }
        .unpaid { background: #bcd6bc !important; }
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
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportAllModalLabel">Export Employees Timesheets</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="exportAllForm">
                            <div class="row">
                                <!-- Year Selection - Left Column -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Years</label>
                                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 10px;">
                                            <div class="mb-2">
                                                <input type="checkbox" id="selectAllYears" checked>
                                                <label for="selectAllYears" style="font-weight: bold;">Select All</label>
                                            </div>
                                            <hr style="margin: 8px 0;">
                                            @php
                                                $currentYear = now()->year;
                                                $startYear = 2023;
                                                $endYear = $currentYear + 1;
                                            @endphp
                                            @for($year = $startYear; $year <= $endYear; $year++)
                                                <div class="form-check">
                                                    <input class="form-check-input year-checkbox" type="checkbox" value="{{ $year }}" id="year_{{ $year }}" {{ $year == $currentYear ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="year_{{ $year }}">{{ $year }}</label>
                                                </div>
                                            @endfor
                                        </div>
                                        <small class="form-text text-muted">Select years to export. Current year is selected by default.</small>
                                    </div>
                                </div>
                                <!-- Month Selection - Right Column -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Months</label>
                                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 10px;">
                                            <div class="mb-2">
                                                <input type="checkbox" id="selectAllMonths" checked>
                                                <label for="selectAllMonths" style="font-weight: bold;">Select All</label>
                                            </div>
                                            <hr style="margin: 8px 0;">
                                            <div class="form-check">
                                                <input class="form-check-input month-checkbox" type="checkbox" value="1" id="month_1" checked>
                                                <label class="form-check-label" for="month_1">January</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input month-checkbox" type="checkbox" value="2" id="month_2" checked>
                                                <label class="form-check-label" for="month_2">February</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input month-checkbox" type="checkbox" value="3" id="month_3" checked>
                                                <label class="form-check-label" for="month_3">March</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input month-checkbox" type="checkbox" value="4" id="month_4" checked>
                                                <label class="form-check-label" for="month_4">April</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input month-checkbox" type="checkbox" value="5" id="month_5" checked>
                                                <label class="form-check-label" for="month_5">May</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input month-checkbox" type="checkbox" value="6" id="month_6" checked>
                                                <label class="form-check-label" for="month_6">June</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input month-checkbox" type="checkbox" value="7" id="month_7" checked>
                                                <label class="form-check-label" for="month_7">July</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input month-checkbox" type="checkbox" value="8" id="month_8" checked>
                                                <label class="form-check-label" for="month_8">August</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input month-checkbox" type="checkbox" value="9" id="month_9" checked>
                                                <label class="form-check-label" for="month_9">September</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input month-checkbox" type="checkbox" value="10" id="month_10" checked>
                                                <label class="form-check-label" for="month_10">October</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input month-checkbox" type="checkbox" value="11" id="month_11" checked>
                                                <label class="form-check-label" for="month_11">November</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input month-checkbox" type="checkbox" value="12" id="month_12" checked>
                                                <label class="form-check-label" for="month_12">December</label>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Select months to export. Current month is selected by default.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Export Type</label>
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="exportType" id="exportTypeSingle" value="single" checked>
                                        <label class="form-check-label" for="exportTypeSingle">
                                            <strong>One PDF</strong> - All employees in one combined PDF file
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="exportType" id="exportTypeSeparate" value="separate">
                                        <label class="form-check-label" for="exportTypeSeparate">
                                            <strong>Separate PDFs</strong> - Each employee in their own PDF file
                                        </label>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Choose whether to export all employees in one PDF or create separate PDF files for each employee.</small>
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
    
        <!-- Bulk Edit Modal -->
        <div class="modal fade" id="bulkEditModal" tabindex="-1" aria-labelledby="bulkEditModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkEditModalLabel">Bulk Edit Selected Records</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="bulkEditForm">
                        @csrf
                        <div class="modal-body">
                            <p class="text-muted">Selected records: <strong><span id="selectedCount">0</span></strong></p>
                            <div class="mb-3">
                                <label class="form-label">Time In</label>
                                <input type="time" class="form-control" id="bulk_clock_in" name="clock_in">
                                <small class="form-text text-muted">Leave empty to keep current values</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Time Out</label>
                                <input type="time" class="form-control" id="bulk_clock_out" name="clock_out">
                                <small class="form-text text-muted">Leave empty to keep current values. Total Time will be auto-calculated.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status (Vacation Type)</label>
                                <select class="form-control" id="bulk_vacation_type" name="vacation_type">
                                    <option value="">-- Keep Current --</option>
                                    <option value="Attended">Attended</option>
                                    <option value="Off">Off</option>
                                    <option value="Vacation">Vacation</option>
                                    <option value="Sick Leave">Sick Leave</option>
                                    <option value="Holiday">Holiday</option>
                                    <option value="Unpaid">Unpaid</option>
                                    <option value="Half Day">Half Day</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason</label>
                                <input type="text" class="form-control" id="bulk_reason" name="reason">
                                <small class="form-text text-muted">Leave empty to keep current values</small>
                            </div>
                            <div id="bulk-edit-errors" class="alert alert-danger d-none"></div>
                            <div id="bulk-edit-success" class="alert alert-success d-none"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="bulkEditSubmitBtn" class="btn btn-primary">Apply Changes</button>
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

                // Export All logic - Updated to handle both single and separate PDF exports
                // Use jQuery event delegation to ensure handler is attached
                $(document).on('click', '#confirmExportAllBtn', function() {
                    const exportAllBtn = this;
                    const checkedMonths = document.querySelectorAll('.month-checkbox:checked');
                    const months = Array.from(checkedMonths).map(checkbox => checkbox.value);
                    const checkedYears = document.querySelectorAll('.year-checkbox:checked');
                    const years = Array.from(checkedYears).map(checkbox => checkbox.value);
                    const checkedEmployees = document.querySelectorAll('.employee-checkbox:checked');
                    let employeeIds = Array.from(checkedEmployees).map(checkbox => checkbox.value);
                    const exportType = document.querySelector('input[name="exportType"]:checked').value;
                    
                    if (months.length === 0 || years.length === 0) {
                        alert('Please select at least one month and one year.');
                        return;
                    }
                    if (employeeIds.length === 0) {
                        alert('Please select at least one employee.');
                        return;
                    }
                    
                    exportAllBtn.disabled = true;
                    exportAllBtn.textContent = 'Exporting...';
                    
                    if (exportType === 'separate') {
                        // Export each employee as a separate PDF
                        exportSeparatePDFs(employeeIds, months, years, exportAllBtn);
                    } else {
                        // Export all employees in one PDF
                        exportSinglePDF(employeeIds, months, years, exportAllBtn);
                    }
                });
                
                // Function to export all employees in one combined PDF
                function exportSinglePDF(employeeIds, months, years, exportAllBtn) {
                    // Build URL with query parameters for the multiple export request
                    const params = new URLSearchParams();
                    months.forEach(month => {
                        params.append('months[]', month);
                    });
                    years.forEach(year => {
                        params.append('years[]', year);
                    });
                    
                    // Add all selected employee IDs
                    employeeIds.forEach(id => {
                        params.append('ids[]', id);
                    });
                    
                    // Use the existing exportMultipleTimesheets endpoint
                    fetch('/employee_times/export-multiple?' + params.toString(), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Export failed: ${response.status} ${response.statusText}`);
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        if (blob.size === 0) {
                            throw new Error('Exported file is empty');
                        }
                        
                        // Generate filename based on selection
                        const employeeCount = employeeIds.length;
                        const monthCount = months.length;
                        const yearCount = years.length;
                        const monthsText = monthCount === 12 ? 'all_months' : months.join('_');
                        const yearsText = yearCount === 1 ? years[0] : years.join('_');
                        const fileName = `combined_timesheets_${employeeCount}_employees_${monthsText}_${yearsText}.pdf`;
                        
                        // Download the combined PDF
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = fileName;
                        document.body.appendChild(a);
                        a.click();
                        
                        // Clean up
                        setTimeout(() => {
                            window.URL.revokeObjectURL(url);
                            document.body.removeChild(a);
                        }, 100);
                        
                        console.log(`Successfully exported ${employeeCount} employees for ${monthCount} months and ${yearCount} years in one PDF`);
                        
                    })
                    .catch((error) => {
                        console.error('Export error:', error);
                        alert('Failed to export timesheets. ' + (error && error.message ? error.message : 'Please try again.'));
                    })
                    .finally(() => {
                        exportAllBtn.disabled = false;
                        exportAllBtn.textContent = 'Export';
                        const modal = bootstrap.Modal.getInstance(document.getElementById('exportAllModal'));
                        if(modal) modal.hide();
                    });
                }
                
                // Function to export each employee as a separate PDF
                function exportSeparatePDFs(employeeIds, months, years, exportAllBtn) {
                    let completedDownloads = 0;
                    let totalDownloads = employeeIds.length;
                    let hasErrors = false;
                    
                    // Update button to show progress
                    exportAllBtn.textContent = `Exporting... (0/${totalDownloads})`;
                    
                    // Create downloads sequentially to avoid overwhelming the server
                    async function downloadEmployeePDF(employeeId, index) {
                        try {
                            // Build URL for single employee export with ALL selected months and years
                            const params = new URLSearchParams();
                            months.forEach(month => {
                                params.append('months[]', month);
                            });
                            years.forEach(year => {
                                params.append('years[]', year);
                            });
                            params.append('ids[]', employeeId);
                            
                            const response = await fetch('/employee_times/export-multiple?' + params.toString(), {
                                method: 'GET',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            
                            if (!response.ok) {
                                throw new Error(`Export failed: ${response.status} ${response.statusText}`);
                            }
                            
                            const blob = await response.blob();
                            
                            if (blob.size === 0) {
                                throw new Error('Exported file is empty');
                            }
                            
                            // Get employee name for filename
                            const employeeCheckbox = document.getElementById(`emp_${employeeId}`);
                            const employeeName = employeeCheckbox ? 
                                employeeCheckbox.nextElementSibling.textContent.trim().replace(/\s+/g, '_') : 
                                `Employee_${employeeId}`;
                            
                            const monthCount = months.length;
                            const yearCount = years.length;
                            const monthsText = monthCount === 12 ? 'all_months' : months.join('_');
                            const yearsText = yearCount === 1 ? years[0] : years.join('_');
                            const fileName = `${employeeName}_timesheet_${monthsText}_${yearsText}.pdf`;
                            
                            // Download the PDF
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = fileName;
                            document.body.appendChild(a);
                            a.click();
                            
                            // Clean up
                            setTimeout(() => {
                                window.URL.revokeObjectURL(url);
                                document.body.removeChild(a);
                            }, 100);
                            
                            completedDownloads++;
                            exportAllBtn.textContent = `Exporting... (${completedDownloads}/${totalDownloads})`;
                            
                            console.log(`Successfully exported timesheet for ${employeeName} covering ${yearCount} years and ${monthCount} months`);
                            
                        } catch (error) {
                            hasErrors = true;
                            console.error(`Error exporting employee ${employeeId}:`, error);
                        }
                    }
                    
                    // Create promises for each employee (one PDF per employee with all their selected months/years)
                    let downloadPromises = [];
                    
                    employeeIds.forEach((employeeId, index) => {
                        downloadPromises.push(
                            new Promise(resolve => 
                                setTimeout(() => resolve(downloadEmployeePDF(employeeId, index)), index * 500)
                            )
                        );
                    });
                    
                    // Download all employee PDFs with staggered timing
                    Promise.all(downloadPromises).then(() => {
                        if (hasErrors) {
                            alert(`Export completed with some errors. ${completedDownloads} of ${totalDownloads} files were downloaded successfully.`);
                        } else {
                            console.log(`Successfully exported ${completedDownloads} separate PDF files (one per employee)`);
                        }
                    }).finally(() => {
                        exportAllBtn.disabled = false;
                        exportAllBtn.textContent = 'Export';
                        const modal = bootstrap.Modal.getInstance(document.getElementById('exportAllModal'));
                        if(modal) modal.hide();
                    });
                }
                
                // Set default month/year when modal is shown
                $(document).on('show.bs.modal', '#exportAllModal', function() {
                    const now = new Date();
                    
                    // Set current month as checked, uncheck all others
                    document.querySelectorAll('.month-checkbox').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    document.getElementById('month_' + (now.getMonth() + 1)).checked = true;
                    
                    // Set current year as checked, uncheck all others
                    document.querySelectorAll('.year-checkbox').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    document.getElementById('year_' + now.getFullYear()).checked = true;
                    
                    // Update Select All checkboxes state
                    updateSelectAllMonthsState();
                    updateSelectAllYearsState();
                });
                
                // Handle Select All checkbox for years
                $(document).on('change', '#selectAllYears', function() {
                    const isChecked = this.checked;
                    document.querySelectorAll('.year-checkbox').forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                });
                
                // Handle individual year checkboxes
                $(document).on('change', '.year-checkbox', function() {
                    updateSelectAllYearsState();
                });
                
                // Function to update Select All years state
                function updateSelectAllYearsState() {
                    const allYearCheckboxes = document.querySelectorAll('.year-checkbox');
                    const checkedYearCheckboxes = document.querySelectorAll('.year-checkbox:checked');
                    const selectAllYearsCheckbox = document.getElementById('selectAllYears');
                    
                    if (checkedYearCheckboxes.length === allYearCheckboxes.length) {
                        selectAllYearsCheckbox.checked = true;
                        selectAllYearsCheckbox.indeterminate = false;
                    } else if (checkedYearCheckboxes.length === 0) {
                        selectAllYearsCheckbox.checked = false;
                        selectAllYearsCheckbox.indeterminate = false;
                    } else {
                        selectAllYearsCheckbox.checked = false;
                        selectAllYearsCheckbox.indeterminate = true;
                    }
                }
                
                // Handle Select All checkbox for months
                $(document).on('change', '#selectAllMonths', function() {
                    const isChecked = this.checked;
                    document.querySelectorAll('.month-checkbox').forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                });
                
                // Handle individual month checkboxes
                $(document).on('change', '.month-checkbox', function() {
                    updateSelectAllMonthsState();
                });
                
                // Function to update Select All months state
                function updateSelectAllMonthsState() {
                    const allMonthCheckboxes = document.querySelectorAll('.month-checkbox');
                    const checkedMonthCheckboxes = document.querySelectorAll('.month-checkbox:checked');
                    const selectAllMonthsCheckbox = document.getElementById('selectAllMonths');
                    
                    if (checkedMonthCheckboxes.length === allMonthCheckboxes.length) {
                        selectAllMonthsCheckbox.checked = true;
                        selectAllMonthsCheckbox.indeterminate = false;
                    } else if (checkedMonthCheckboxes.length === 0) {
                        selectAllMonthsCheckbox.checked = false;
                        selectAllMonthsCheckbox.indeterminate = false;
                    } else {
                        selectAllMonthsCheckbox.checked = false;
                        selectAllMonthsCheckbox.indeterminate = true;
                    }
                }
                
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
                const dataGridInstance = $("#employeeTimesGrid").dxDataGrid({
                    dataSource: employeeTimesData,
                    selection: {
                        mode: 'multiple',
                        showCheckBoxesMode: 'always'
                    },
                    columns: [
                        { dataField: "id", caption: "ID", width: 60, allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
                        { dataField: "employee", caption: "Employee", width: 200, allowFiltering: true, headerFilter: { allowSearch: true } },
                        { 
                            dataField: "date", 
                            caption: "Date", 
                            dataType: "date",
                            allowFiltering: true, 
                            headerFilter: { allowSearch: true }, 
                            sortOrder: "desc",
                            format: "dd/MM/yyyy",
                            filterOperations: ['between', '=', '<>', '<', '<=', '>', '>='],
                            selectedFilterOperation: 'between'
                        },
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
                            } else if (vacationType === 'unpaid') {
                                e.rowElement.addClass('unpaid');
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
                    toolbar: {
                        items: [
                            {
                                location: 'after',
                                widget: 'dxButton',
                                options: {
                                    icon: 'edit',
                                    text: 'Bulk Edit',
                                    hint: 'Edit selected records',
                                    disabled: true,
                                    elementAttr: {
                                        id: 'bulkEditBtn'
                                    },
                                    onClick: function() {
                                        const selectedRows = dataGridInstance.getSelectedRowsData();
                                        if (selectedRows.length > 0) {
                                            $('#selectedCount').text(selectedRows.length);
                                            $('#bulkEditModal').modal('show');
                                        }
                                    }
                                }
                            },
                            {
                                location: 'after',
                                widget: 'dxButton',
                                options: {
                                    icon: 'clearformat',
                                    text: 'Reset Filters',
                                    hint: 'Clear all filters and sorting',
                                    onClick: function() {
                                        dataGridInstance.clearFilter();
                                        dataGridInstance.clearSorting();
                                        dataGridInstance.searchByText('');
                                    }
                                }
                            },
                            'columnChooserButton',
                            'searchPanel'
                        ]
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
                    noDataText: 'No Punch Time found.',
                    onSelectionChanged: function(selectedItems) {
                        const selectedKeys = selectedItems.selectedRowKeys;
                        
                        // Use setTimeout to ensure button is rendered
                        setTimeout(function() {
                            const bulkEditBtnElement = $('#bulkEditBtn');
                            if (bulkEditBtnElement.length > 0) {
                                const bulkEditBtn = bulkEditBtnElement.dxButton('instance');
                                
                                if (selectedKeys.length > 0) {
                                    bulkEditBtn.option('disabled', false);
                                    bulkEditBtn.option('text', `Bulk Edit (${selectedKeys.length})`);
                                } else {
                                    bulkEditBtn.option('disabled', true);
                                    bulkEditBtn.option('text', 'Bulk Edit');
                                }
                            }
                        }, 0);
                    }
                }).dxDataGrid('instance');
                
                // Bulk Edit form submission
                $('#bulkEditForm').on('submit', function(e) {
                    e.preventDefault();
                    
                    const selectedRows = dataGridInstance.getSelectedRowsData();
                    const selectedIds = selectedRows.map(row => row.id);
                    
                    const formData = {
                        ids: selectedIds,
                        clock_in: $('#bulk_clock_in').val(),
                        clock_out: $('#bulk_clock_out').val(),
                        vacation_type: $('#bulk_vacation_type').val(),
                        reason: $('#bulk_reason').val(),
                        _token: '{{ csrf_token() }}'
                    };
                    
                    const errorsDiv = $('#bulk-edit-errors');
                    const successDiv = $('#bulk-edit-success');
                    const submitBtn = $('#bulkEditSubmitBtn');
                    
                    errorsDiv.addClass('d-none');
                    successDiv.addClass('d-none');
                    submitBtn.prop('disabled', true).text('Saving...');
                    
                    $.ajax({
                        url: '{{ route('employee_times.bulk-update') }}',
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            successDiv.text(response.message || 'Records updated successfully!');
                            successDiv.removeClass('d-none');
                            
                            setTimeout(function() {
                                $('#bulkEditModal').modal('hide');
                                window.location.reload();
                            }, 1500);
                        },
                        error: function(xhr) {
                            let errorMsg = 'Failed to update records.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            errorsDiv.text(errorMsg);
                            errorsDiv.removeClass('d-none');
                            submitBtn.prop('disabled', false).text('Apply Changes');
                        }
                    });
                });
                
                // Reset form when modal is closed
                $('#bulkEditModal').on('hidden.bs.modal', function() {
                    $('#bulkEditForm')[0].reset();
                    $('#bulk-edit-errors').addClass('d-none');
                    $('#bulk-edit-success').addClass('d-none');
                    $('#bulkEditSubmitBtn').prop('disabled', false).text('Apply Changes');
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
