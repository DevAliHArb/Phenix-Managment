@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
    <style>
        .weekend { background: #fbe4d5 !important; }
        .vacation { background: #daeef3 !important; }
        .sickleave { background: #ffe6e6 !important; }
        .holiday { background: #e6ffe6 !important; }
        .unpaid { background: #bcd6bc !important; }
        .halfday { background: #fff4cc !important; }
        .flagged { background: #ff9d9d !important; }
        .calc-card-resolved { background-color: rgba(25, 135, 84, 0.15) !important; }
        #pdfPreviewContainer {
            width: 100%;
            height: 100%;
            overflow: auto;
            background: #525659;
        }

        #pdfPreviewContainer canvas {
            display: block;
            margin: 10px auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .spin {
            animation: spin 1s linear infinite;
        }
    </style>
@endsection

@section('content')
<div style="width:100%">
    <div class="headerContainer mb-2">
        <h1 class="mb-0"> Punch Time Logs</h1>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
                <div style="display: flex; justify-content: flex-end; margin-bottom: 18px; gap: 10px;">
                        <!-- Filter Toggle Button -->
                        <button type="button" class="btn btn-primary" id="toggleFilterBtn">Filter</button>
                        <!-- Sync Button -->
                        <button type="button" class="btn btn-primary" id="syncBtn">Sync</button>
                        <!-- Sync Button -->
                        <button type="button" class="btn btn-primary" id="calculateBtn">Calculate</button>
                        <!-- Import Button triggers modal -->
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">Import</button>
                        <!-- Export All Button triggers modal -->
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportAllModal">Export All</button>

                        <a href="{{ route('employee_times.create') }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">Add Punch Time</a>
                </div>
            </div>
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
                                    @foreach($employees as $emp)
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

        <!-- PDF Preview Modal -->
        <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-labelledby="pdfPreviewModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pdfPreviewModalLabel">PDF Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="padding: 0; height: calc(100vh - 120px); overflow: hidden;">
                        <div id="pdfPreviewLoading" style="display: flex; justify-content: center; align-items: center; height: 100%; background: #525659;">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-white">Loading preview...</p>
                            </div>
                        </div>
                        <div id="pdfPreviewContainer" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="downloadPdfBtn" class="btn btn-success">
                            <i class="fas fa-download"></i> Download
                        </button>
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
                                    <option value="Half Day Vacation">Half Day Vacation</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason</label>
                                <input type="text" class="form-control" id="bulk_reason" name="reason">
                                <small class="form-text text-muted">Leave empty to keep current values, or tick below to clear.</small>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="bulk_clear_reason" name="clear_reason" value="1">
                                    <label class="form-check-label" for="bulk_clear_reason">Set reason to empty</label>
                                </div>
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

        <!-- Bulk Add Modal -->
        <div class="modal fade" id="bulkAddModal" tabindex="-1" aria-labelledby="bulkAddModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkAddModalLabel">Bulk Add Punch Time</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="bulkAddForm">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Employees (Required)</label>
                                <div style="max-height: 250px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 10px;">
                                    <div class="mb-2">
                                        <input type="checkbox" id="selectAllBulkAddEmployees">
                                        <label for="selectAllBulkAddEmployees" style="font-weight: bold;">Select All</label>
                                    </div>
                                    <hr style="margin: 8px 0;">
                                    @foreach($employees as $emp)
                                        <div class="form-check">
                                            <input class="form-check-input bulk-add-employee-checkbox" type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" id="bulk_add_emp_{{ $emp->id }}">
                                            <label class="form-check-label" for="bulk_add_emp_{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->mid_name }} {{ $emp->last_name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="form-text text-muted">Select one or more employees</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date (Required)</label>
                                <input type="date" class="form-control" id="bulk_add_date" name="date" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Time In</label>
                                <input type="time" class="form-control" id="bulk_add_clock_in" name="clock_in">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Time Out</label>
                                <input type="time" class="form-control" id="bulk_add_clock_out" name="clock_out">
                                <small class="form-text text-muted">Total Time will be auto-calculated.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status (Vacation Type)</label>
                                <select class="form-control" id="bulk_add_vacation_type" name="vacation_type">
                                    <option value="">-- Select Status --</option>
                                    <option value="Attended">Attended</option>
                                    <option value="Off">Off</option>
                                    <option value="Vacation">Vacation</option>
                                    <option value="Sick Leave">Sick Leave</option>
                                    <option value="Holiday">Holiday</option>
                                    <option value="Unpaid">Unpaid</option>
                                    <option value="Half Day Vacation">Half Day Vacation</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason</label>
                                <input type="text" class="form-control" id="bulk_add_reason" name="reason">
                            </div>
                            <div id="bulk-add-errors" class="alert alert-danger d-none"></div>
                            <div id="bulk-add-success" class="alert alert-success d-none"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="bulkAddSubmitBtn" class="btn btn-primary">Add Records</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sync Modal -->
        <div class="modal fade" id="syncModal" tabindex="-1" aria-labelledby="syncModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" id="syncModalDialog">
                <div class="modal-content">
                    <div class="modal-header justify-content-between">
                        <h5 class="modal-title" id="bulkAddModalLabel">Sync Machine Records</h5>
                        <div class="d-flex justify-content-between gap-2 align-items-center">
                            <div id="syncModalHeaderMessage"></div>
                            <button type="button" id="syncModalCloseBtn" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div id="syncModalMessage"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="SyncModalPrimaryButton" class="btn btn-primary">Yes</button>
                        <button type="button" id="SyncModalSecondaryButton" class="btn btn-secondary">No</button>
                        <button type="button" id="SyncModalTertiaryButton" class="btn btn-secondary">No</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculate Modal -->
        <div class="modal fade" id="calculateModal" tabindex="-1" aria-labelledby="calculateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" id="calculateModalDialog">
                <div class="modal-content">
                    <div class="modal-header justify-content-between">
                        <h5 class="modal-title" id="bulkAddModalLabel">Calculate Attendance</h5>
                        <button type="button" id="calculateModalCloseBtn" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                        <div class="modal-body">
                            <div class="calculateModalPage" id="calculateModalSuccess">
                                <div class="calculateModalMessage"></div>
                            </div>
                            <div class="calculateModalPage" id="calculateModalFlagged">
                                <div class="calculateModalMessage"></div>
                            </div>
                            <div class="calculateModalPage" id="calculateModalConfirm">
                                <div class="calculateModalMessage"></div>
                            </div>
                            <div class="calculateModalPage" id="calculateModalError">

                            </div>
                            <div class="calculateModalPage d-none" id="calculateModalProgress">
                                <div class="d-flex gap-2 flex-column mb-2">
                                    <label for="calc-import-log">Logs:</label>
                                    <textarea class="w-100 rounded border border-secondary-subtle" id="calc-import-log" name="calc-import-log" style="height: 300px; font-size: 0.8rem;">
                                    </textarea>
                                </div>
                                <label for="calc-import-progress">Import progress:</label><br>
                                <div class="d-flex w-100  align-items-center">
                                    <progress class="progress-bar bg-info flex-grow-1" id="calc-import-progress" value="0" max="100"></progress>
                                    <span class="m-2" id="calc-import-progress-label">0%</span>
                                </div>
                            </div>

                        </div>
                            <div class="modal-footer">
                                <button type="button" id="calculateModalPrimaryButton" class="btn btn-primary">Yes</button>
                                <button type="button" id="calculateModalSecondaryButton" class="btn btn-secondary">No</button>
                                <button type="button" id="calculateModalTertiaryButton" class="btn btn-secondary">No</button>
                            </div>
                </div>
            </div>
        </div>
        <!-- Filter Bar (toggled by the Filter button) -->
        <div id="filterBar" style="display:none; position:relative; z-index:1; background:#f8f9fa; border:1px solid #dee2e6; border-radius:6px; padding:12px 16px; margin-bottom:14px; flex-wrap:wrap; gap:10px; align-items:flex-end;">
            <div style="position:relative;">
                <label class="form-label mb-1" style="font-size:0.8rem;">Employee</label>
                <input type="text" id="filterEmployeeName" class="form-control form-control-sm" placeholder="Search employee..." autocomplete="off" style="width:220px;">
                <input type="hidden" id="filterEmployeeId" value="">
                <div id="filterEmployeeList" style="display:none; position:absolute; top:100%; left:0; z-index:1060; width:250px; max-height:180px; overflow-y:auto; background:#fff; border:1px solid #dee2e6; border-radius:6px; margin-top:2px; box-shadow:0 4px 10px rgba(0,0,0,.15);"></div>
            </div>
            <div>
                <label class="form-label mb-1" style="font-size:0.8rem;">Month</label>
                <select id="filterMonth" class="form-select form-select-sm" style="width:130px;">
                    <option value="">All Months</option>
                    <option value="01">January</option>
                    <option value="02">February</option>
                    <option value="03">March</option>
                    <option value="04">April</option>
                    <option value="05">May</option>
                    <option value="06">June</option>
                    <option value="07">July</option>
                    <option value="08">August</option>
                    <option value="09">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1" style="font-size:0.8rem;">Year</label>
                <select id="filterYear" class="form-select form-select-sm" style="width:100px;">
                    <option value="">All Years</option>
                    @php
                        $currentYear = now()->year;
                    @endphp
                    @for($y = 2023; $y <= $currentYear + 1; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div style="display: flex; gap: 6px; align-items: center;">
                <button type="button" id="applyFilterBtn" class="btn btn-primary btn-sm">Apply</button>
                <button type="button" id="clearFilterBtn" class="btn btn-secondary btn-sm">Clear</button>
            </div>
        </div>

        <div id="employeeTimesGrid"></div>

        <!-- Load PDF.js library -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
        <script>
            // Configure PDF.js worker
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        </script>

        @push('scripts')
        <script>
            // Import form logic
            document.addEventListener('DOMContentLoaded', function() {
                // Toggle filter bar visibility
                $('#toggleFilterBtn').on('click', function() {
                    const $bar = $('#filterBar');
                    if ($bar.is(':visible')) {
                        $bar.slideUp(200);
                    } else {
                        $bar.css('display', 'flex').hide().slideDown(200);
                    }
                });
                const syncModalElement = document.getElementById('syncModal');
                const syncModal = new bootstrap.Modal(syncModalElement);
                const calculateModalElement = document.getElementById('calculateModal');
                const calculateModal = new bootstrap.Modal(calculateModalElement);
                const importForm = document.getElementById('importForm');

                const $testConn = $('#test-connection');
                const $testConnIcon = $testConn.find('i.bi');
                const $connectionStatus = $('#connection-status');
                const $connectionMessage = $('#connection-message');
                const $syncBtn = $('#syncBtn');
                let machineConnected = true;

                const setSyncDisabled = (disabled) => {
                    machineConnected = !disabled;
                    if (disabled) {
                        $syncBtn.prop('disabled', true).attr('title', 'Cannot sync while the machine is unreachable.');
                    } else {
                        $syncBtn.prop('disabled', false).removeAttr('title');
                    }
                };

                $testConn.on('click', function() {
                    const machineSettings = @json($machineSettings);
                    const ip = machineSettings.ip;
                    const port = machineSettings.port;
                    
                    $testConnIcon.removeClass('d-none')
                    $connectionMessage.addClass('d-none').html('');
                    $testConnIcon.addClass('spin');
                    $connectionStatus.html('Testing...');

                    $.ajax({
                        url: '{{ route('settings.testMachineConnection') }}',
                        method: 'POST',
                        data: {
                            ip: ip,
                            port: port,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            $testConn.removeClass('bg-primary');
                            $testConnIcon.removeClass('spin');
                            if (response.status === 'connected') {
                                $connectionStatus.html('Online');
                                $testConn.removeClass('bg-danger').addClass('bg-success');
                                setSyncDisabled(false);
                            } else {
                                $connectionStatus.html('Unreachable');
                                $testConn.removeClass('bg-success').addClass('bg-danger');
                                setSyncDisabled(true);
                            }
                        },
                        error: function(xhr) {
                            $testConn.removeClass('bg-primary');
                            $testConnIcon.removeClass('spin');
                            let errorMsg = 'Connection test failed. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            $connectionStatus.html('Unreachable');
                            $testConn.removeClass('bg-success').addClass('bg-danger');
                            $connectionMessage.html(`${errorMsg}`).removeClass('d-none');
                            setSyncDisabled(true);
                        }
                    });
                });

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

                        // Show preview modal
                        showPdfPreview(blob, fileName);

                        console.log(`Successfully loaded preview for ${employeeCount} employees for ${monthCount} months and ${yearCount} years`);

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

                // Function to show PDF preview in modal
                async function showPdfPreview(blob, fileName) {
                    const url = window.URL.createObjectURL(blob);
                    const container = document.getElementById('pdfPreviewContainer');
                    const loadingDiv = document.getElementById('pdfPreviewLoading');
                    const downloadBtn = document.getElementById('downloadPdfBtn');

                    // Clear previous content
                    container.innerHTML = '';

                    // Show preview modal
                    const previewModal = new bootstrap.Modal(document.getElementById('pdfPreviewModal'));
                    previewModal.show();

                    // Show loading
                    loadingDiv.style.display = 'flex';
                    container.style.display = 'none';

                    try {
                        // Load PDF document
                        const pdf = await pdfjsLib.getDocument(url).promise;

                        // Render all pages
                        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                            const page = await pdf.getPage(pageNum);

                            // Calculate scale to fit width
                            const viewport = page.getViewport({ scale: 1 });
                            const containerWidth = container.clientWidth || 800;
                            const scale = (containerWidth * 0.95) / viewport.width;
                            const scaledViewport = page.getViewport({ scale: scale });

                            // Create canvas for this page
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');
                            canvas.height = scaledViewport.height;
                            canvas.width = scaledViewport.width;

                            // Render page
                            await page.render({
                                canvasContext: context,
                                viewport: scaledViewport
                            }).promise;

                            // Add canvas to container
                            container.appendChild(canvas);
                        }

                        // Hide loading, show container
                        loadingDiv.style.display = 'none';
                        container.style.display = 'block';

                    } catch (error) {
                        console.error('Error loading PDF:', error);
                        loadingDiv.innerHTML = '<div class="text-center text-white"><p>Error loading PDF preview</p><p class="small">' + error.message + '</p></div>';
                    }

                    // Setup download button
                    downloadBtn.onclick = function() {
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = fileName;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    };

                    // Clean up when modal closes
                    document.getElementById('pdfPreviewModal').addEventListener('hidden.bs.modal', function() {
                        window.URL.revokeObjectURL(url);
                        container.innerHTML = '';
                        loadingDiv.style.display = 'flex';
                        loadingDiv.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-white">Loading preview...</p></div>';
                        container.style.display = 'none';
                    }, { once: true });
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

                $('#syncModal').on('hide.bs.modal', function () {
                        if (this.contains(document.activeElement)) {
                            document.activeElement.blur();
                        }
                    });
                var syncModalData = {
                    page : null,
                    flaggedRecords : [],
                    invalidRecords : null,
                    recordsAdded : null,
                    message : null,
                    recordsModified : null,
                    totalRows : null,
                    headerMessage : null,
                    progress : null,
                    progressMessage : null,
                    nextPage : null,
                    progressComplete : false
                    };
                var showSyncModalPage = (page) => {
                    syncModalData.page = page;
                    const message = $('#syncModalMessage');
                    const primaryBtn = $('#SyncModalPrimaryButton');
                    const secondaryBtn = $('#SyncModalSecondaryButton');
                    const tertiaryBtn = $('#SyncModalTertiaryButton');
                    const dialog = $('#syncModalDialog');
                    const closeBtn = $('#syncModalCloseBtn');
                    const headerMessage =$('#syncModalHeaderMessage')
                    closeBtn.removeClass('d-none');



                    if (syncModalData.page === 'flagged' ) {
                        dialog.removeClass('modal-sm modal-lg').addClass('modal-xl');
                    }
                    else if( syncModalData.page === 'normal' ){
                        dialog.removeClass('modal-sm modal-xl').addClass('modal-lg');
                    }
                    else {
                        dialog.removeClass('modal-xl modal-lg modal-sm');
                    }

                    headerMessage.html('').addClass('d-none')
                    primaryBtn.text('Continue').addClass('bg-primary').removeClass('bg-danger').removeClass('d-none');
                    secondaryBtn.addClass('d-none');
                    tertiaryBtn.addClass('d-none');
                    primaryBtn.prop("disabled", false);
                    $("#syncModal .modal-footer").removeClass("d-none");

                    if(syncModalData.page==='calculate-success'){
                        let htmlStatement="Attendance calculated successfully !"
                        message.html(htmlStatement)

                        $("#syncModal .modal-footer").addClass("d-none");
                    }

                    if(syncModalData.page==='normal'){

                        headerMessage.html(syncModalData.headerMessage).removeClass('d-none')
                        // let htmlStatement = syncModalData.recordsAdded===0&&syncModalData.flaggedRecords.length===0&&syncModalData.invalidRecords===0 ? "All up to date , no new imports":'';
                        htmlStatement = syncModalData.message;
                        message.html(htmlStatement);
                        primaryBtn.text('Ok');
                    }

                    if(syncModalData.page==='flagged'){


                        const idsToFetch = syncModalData.flaggedRecords.map((element) => element.uid)
                        const originalRecordsRequest = $.ajax({
                            url: "{{ route('employee_times.getMachineRecordsById') }}",
                            type: "POST",
                            data: {
                                idsToFetch : idsToFetch,
                                _token: "{{ csrf_token() }}"
                            }
                        });
                        const eventCodesRequest = $.ajax({
                             url: "{{ route('employee_times.getEventCodes') }}",
                            type: "GET",
                            data: {
                                _token: "{{ csrf_token() }}"
                            }
                        });

                        $.when(originalRecordsRequest,eventCodesRequest).done(function (originalResponse, eventCodesResponse) {


                            const originalRecords = originalResponse[0];
                            const eventCodes = eventCodesResponse[0];



                            const formattedConflictRecords  = syncModalData.flaggedRecords.map((element,index)=>{
                                const originals = originalRecords.filter(
                                        record => record.machine_id == element.uid
                                    );
                                let flaggedTime = element.timestamp.split(" ")

                                let originalCellsHtml = originals.length > 0
                                        ? originals.map((original, i) => {
                                            let originalTime = original.timestamp.split(" ")
                                            return `
                                                <div class="row g-0 text-center small align-items-center text-muted bg-danger-subtle ${i > 0 ? 'border-top' : ''}">
                                                    <div class="col-3 py-1">${original.emp_id}</div>
                                                    <div class="col-3 py-1">${original.event_type.name}</div>
                                                    <div class="col-3 py-1">${originalTime[0]}</div>
                                                    <div class="col-3 py-1">${originalTime[1]}</div>
                                                </div>`;
                                        }).join('')
                                        : `<div class="small text-muted text-center">No conflicting record</div>`;

                                return {
                                        uid: element.uid,
                                        flaggedEmpId: element.id,
                                        flaggedEvent: eventCodes[element.type],
                                        flaggedDate: flaggedTime[0],
                                        flaggedClock: flaggedTime[1],
                                        originalCellsHtml: originalCellsHtml
                                    };
                            })

                            let htmlStatement = `<div class="align-items-center mb-3">${syncModalData.message??''}</div>`
                            htmlStatement+= `
                                    <div class="container-fluid">

                                        <div class="row mb-4">
                                            <div class="col-12 alert alert-warning">
                                                <p class="mb-0">
                                                    Some machine records have the same id as saved records,but with newer dates.
                                                    <b>What do you want to do?</b>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-4 ">

                                            <div class="col-1 text-center fw-bold">

                                            </div>

                                            <div class="col-4 text-center fw-bold">
                                                Flagged records
                                            </div>

                                            <div class="col-4 text-center fw-bold">
                                                Conflicted records
                                            </div>

                                            <div class="col-2 text-center fw-bold">

                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-2 pb-2 border-bottom">

                                            <div class="col-1 text-center fw-bold">
                                                ID
                                            </div>

                                            <div class="col-4">
                                                <div class="row g-0 text-center small">
                                                    <div class="col-3 fw-bold">Employee #</div>
                                                    <div class="col-3 fw-bold">Event Type</div>
                                                    <div class="col-3 fw-bold">Date</div>
                                                    <div class="col-3 fw-bold">Time</div>
                                                </div>
                                            </div>

                                            <div class="col-4">
                                                <div class="row g-0 text-center small">
                                                    <div class="col-3 fw-bold">Employee #</div>
                                                    <div class="col-3 fw-bold">Event Type</div>
                                                    <div class="col-3 fw-bold">Date</div>
                                                    <div class="col-3 fw-bold">Time</div>
                                                </div>
                                            </div>

                                            <div class="col-2 text-center fw-bold">
                                                Actions
                                            </div>
                                        </div>`

                                     formattedConflictRecords.forEach((element,index) => {
                                        isDuplicate=false
                                        if(originalRecords.some(record=> record.machine_id == element.uid))
                                            isDuplicate=true;

                                          htmlStatement += `
                                                <div class="row align-items-center py-2 mb-1 border-bottom">

                                                    <div class="col-1 text-center text-success fw-bold">
                                                        ${element.uid}
                                                    </div>


                                                    <div id="flagged-record-${element.uid}" class="col-4 px-1">
                                                        <div class="row g-0 text-center small align-items-center bg-success-subtle">
                                                            <div class="col-3 py-1">${element.flaggedEmpId}</div>
                                                            <div class="col-3 py-1">${element.flaggedEvent}</div>
                                                            <div class="col-3 py-1">${element.flaggedDate}</div>
                                                            <div class="col-3 py-1">${element.flaggedClock}</div>
                                                        </div>
                                                    </div>

                                                    <div id="original-record-${element.uid}" class="col-4 px-1">
                                                        ${element.originalCellsHtml}
                                                    </div>
                                                    <div class="col-2 d-flex  justify-content-center align-items-center">
                                                        <button class="rounded border-0 bg-success text-light m-1 p-1 flex-grow-1"  id=submit-btn-${element.uid}>
                                                        ${isDuplicate?'Overwrite':'Add'}</button>
                                                        <button class="rounded border-0  bg-danger text-light m-1 p-1 flex-grow-1"  id=ignore-btn-${element.uid}>Ignore</button>
                                                    </div>
                                                </div>
                                            `;});

                                        htmlStatement+=`</div>`


                                        message.html(htmlStatement)
                                        headerMessage.html(syncModalData.headerMessage).removeClass('d-none')
                                        primaryBtn.text('Continue')
                                        // tertiaryBtn.text('Reset records').removeClass('d-none')
                                        // tertiaryBtn.prop('disabled', syncModalData.flaggedRecords.length === 0)

                                        closeBtn.addClass('d-none');




                        })
                        .fail(function(xhr) {
                                const errorMsg =
                                    xhr.responseJSON?.error ||
                                    "An error occurred while Fetching Attendance Records";

                                syncModalData.message = errorMsg;
                                showSyncModalPage('error');
                            });

                    }

                    if(syncModalData.page==='flagged-ignore'){
                        let htmlStatement=syncModalData.message??"";
                        htmlStatement+=`You still have some unresolved conflicts.All the flagged records will be <span class="text-danger fw-bold">ignored</span> if you continue.Do You want to proceed ?`
                        message.html(htmlStatement)
                        primaryBtn.text('Yes')
                        primaryBtn.addClass('bg-danger').removeClass('bg-primary')
                        secondaryBtn.text('Go back').removeClass('d-none')
                        closeBtn.addClass('d-none');

                    }

                    if(syncModalData.page==='import-success'){
                        let htmlStatement=syncModalData.message??"";
                        htmlStatement+="Do you want to calculate attendance records now ?"
                        message.html(htmlStatement)
                        primaryBtn.text('Yes')
                        secondaryBtn.text('Later').removeClass('d-none')
                    }

                    if(syncModalData.page==='import-progress'){
                        closeBtn.addClass('d-none');
                        let htmlStatement=`
                        <div class="d-flex gap-2 flex-column mb-2">
                        <label for="sync-import-log">Logs:</label>
                        <textarea class="w-100 rounded border border-secondary-subtle" id="sync-import-log" name="import-log" style="height: 300px; font-size: 0.8rem;">
                        </textarea>
                        </div>
                        <label for="sync-import-progress">Import progress:</label><br>
                        <div class="d-flex w-100  align-items-center">
                            <progress class="progress-bar bg-info flex-grow-1" id="sync-import-progress" value="0" max="100"></progress>
                            <span class="m-2" id="sync-import-progress-label">0%</span>
                        </div>`
                        message.html(htmlStatement)
                        $("#syncModal .modal-footer").addClass("d-none");

                    }
                    if(syncModalData.page==='error')
                    {
                        let htmlStatement = `<h5>Error</h5><div class="alert alert-danger mb-0 ">${syncModalData.message}. </div>`;
                        message.html(htmlStatement);
                        primaryBtn.text('Ok');
                        secondaryBtn.hide();
                        tertiaryBtn.hide();
                    }

                }
                // shared SSE stream reader used by the sync + calculate flows
                const createSseReader = ({ onProgress, onError, onDone }) => {
                    let buffer = '';
                    let readOffset = 0;

                    const processBlocks = () => {
                        while (true) {
                            const boundary = buffer.indexOf('\n\n');
                            if (boundary === -1) break;

                            const rawEvent = buffer.slice(0, boundary);
                            buffer = buffer.slice(boundary + 2);
                            if (rawEvent.trim() === '') continue;

                            let eventName = 'message';
                            let eventData = '';
                            rawEvent.split('\n').forEach(line => {
                                if (line.startsWith('event:')) {
                                    eventName = line.slice(6).trim();
                                } else if (line.startsWith('data:')) {
                                    eventData = line.slice(5).trim();
                                }
                            });

                            const payload = JSON.parse(eventData || '{}');
                            if (eventName === 'progress' && onProgress) onProgress(payload);
                            else if (eventName === 'error' && onError) onError(payload);
                            else if (eventName === 'done' && onDone) onDone(payload);
                        }
                    };

                    // appends only the newly-received slice and parses complete events
                    const consume = (responseText) => {
                        if (responseText.length > readOffset) {
                            buffer += responseText.slice(readOffset);
                            readOffset = responseText.length;
                            processBlocks();
                        }
                    };

                    return {
                        consume,
                        reset() { buffer = ''; readOffset = 0; }
                    };
                };

                // ---- Sync Punch Times flow ----
                let syncReader = null;
                let syncFinished = false;

                const finishSync = (errorMsg, response) => {
                    if (syncFinished) return;
                    syncFinished = true;

                    const $btn = $("#syncBtn");
                    $btn.prop("disabled", !machineConnected).text($btn.data('originalText') || 'Sync Punch Times');
                    $('#calculateBtn').prop('disabled', false);

                    if (errorMsg) {
                        syncModalData.message = errorMsg;
                        showSyncModalPage('error');
                        syncModal.show();
                        return;
                    }

                    var flaggedRecords = response.flaggedRecords || [];
                    syncModalData.flaggedRecords = flaggedRecords;
                    syncModalData.invalidRecords = response.invalidRecords;
                    syncModalData.recordsAdded = response.recordsAdded;
                    syncModalData.totalRows = response.totalRows;
                    syncModalData.oldRows = response.oldRows;
                    syncModalData.headerMessage = response.latestRecord
                        ? `<div class="d-flex justify-content-end">
                              <div class="small  bg-danger  rounded p-2 text-light">
                                <strong class="text-white me-2">Last Sync Data: ID </strong>
                                <span class="bg-white text-danger fw-bold rounded px-2 py-1 me-2">
                                  ${response.latestRecord.last_machine_id}
                                </span>
                                <strong>Date </strong>
                                <span class="bg-white text-danger fw-bold rounded px-2 py-1 me-2">
                                  ${response.latestRecord.last_attendance_date}
                                </span>
                              </div>
                            </div>`
                        : '';
                    syncModalData.message = `
                        <div class="alert alert-success mb-1">
                            <strong>Sync complete:</strong><br>
                            <span class="badge bg-success ">${syncModalData.oldRows}</span> rows total before insertion <br>
                            <span class="badge bg-success ">${syncModalData.recordsAdded}</span> new record(s) imported <br>
                            <span class="badge bg-success ">${syncModalData.invalidRecords}</span> ignored.<br>
                           <span class="badge bg-success ">${syncModalData.totalRows}</span> rows total in database.
                        </div>`;

                    if (flaggedRecords && flaggedRecords.length > 0) {
                        showSyncModalPage('flagged');
                    } else {
                        showSyncModalPage('normal');
                    }
                };

                $("#syncBtn").on("click", function() {
                    const $btn = $(this);
                    const originalText = $btn.text();
                    $btn.data('originalText', originalText);
                    $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span>Syncing...');
                    $('#calculateBtn').prop('disabled', true);
                    showSyncModalPage('import-progress');
                    syncModal.show();

                    syncFinished = false;
                    syncReader = createSseReader({
                        onProgress: function(payload) {
                            const progress = Number(payload.progress) || 0;
                            $('#sync-import-progress').val(progress);
                            $('#sync-import-progress-label').text(`${progress}%`);
                            const logEl = $('#sync-import-log');
                            if (payload.message) {
                                logEl.val((logEl.val() + '\n' + payload.message).trim());
                                logEl.scrollTop(logEl[0].scrollHeight);
                            }
                        },
                        onError: function(payload) {
                            finishSync(payload.message || "An error occurred while syncing Punch Times.", null);
                        },
                        onDone: function(response) {
                            finishSync(null, response);
                        }
                    });
                    syncReader.reset();

                    $.ajax({
                        url: "{{ route('employee_times.importMachineRecords') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        xhr: function() {
                            const xhr = new XMLHttpRequest();
                            // read the stream incrementally
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState >= 3) {
                                    syncReader.consume(xhr.responseText);
                                }
                            };
                            return xhr;
                        },
                        success: function(response) {
                            syncReader.consume(response);
                        },
                        error: function(xhr) {
                            if (!syncFinished) {
                                const errorMsg = xhr.responseJSON?.error || "An error occurred while syncing Punch Times.";
                                finishSync(errorMsg, null);
                            }
                        }
                    });
                });
                $("#SyncModalPrimaryButton").on('click',function(){

                    if(syncModalData.page==='import-success'){
                        // close the sync modal and hand over to the full calculate flow
                        syncModal.hide();
                        $('.modal-backdrop').remove();
                        $('#calculateBtn').click();
                    }
                    else if(syncModalData.page==='normal'){
                        syncModalData.message='';
                        showSyncModalPage('import-success');
                    }
                    else if(syncModalData.page==='flagged'){
                        syncModalData.message='';
                        if(syncModalData.flaggedRecords.length===0)
                        {
                            showSyncModalPage('import-success');
                        }
                        else{
                            showSyncModalPage('flagged-ignore');
                        }

                    }
                    else if (syncModalData.page==='flagged-ignore'){
                        let count = syncModalData.flaggedRecords.length
                        syncModalData.flaggedRecords = [];
                        syncModalData.message=`<div class="alert alert-success mb-0">${count} Flagged records ignored</div>`

                        showSyncModalPage('import-success')

                    }

                    else if (syncModalData.page==='error'){
                        if(syncModal){
                            syncModal.hide();
                        }
                    }
                });

                $("#SyncModalSecondaryButton").on('click',function(){

                    if(syncModalData.page==='flagged-ignore'){
                        showSyncModalPage('flagged')}

                    else{
                        if (syncModal) {
                            syncModal.hide()
                        }
                    }
                });

                $('#syncModal').on('hide.bs.modal', function (e) {
                    if (syncModalData.page === 'flagged' || syncModalData.page === 'flagged-ignore' || syncModalData.page === 'flagged-delete-options' || syncModalData.page === 'import-progress') {
                        e.preventDefault();
                    }
                });

                $("#SyncModalTertiaryButton").on('click',function(){



                })


              $(document).on('click', 'button[id*="submit-btn-"]', function () {
                    const id = Number(this.id.slice(11));
                    const submitBtn = $(this);
                    const ignoreButton = $(`button[id="ignore-btn-${id}"]`);
                    const actionLabel = $(this).text().trim();

                    let addedDiv  = $(`<div class="d-flex justify-content-center align-items-center text-center">
                        <span class="spinner-border spinner-border-sm text-primary"></span>
                        </div>`);
                    $(ignoreButton).after(addedDiv);
                    $(this).hide()
                    $(ignoreButton).hide()


                    $.ajax({
                        url: '{{ route('employee_times.addFlaggedRecord') }}',
                        method: 'POST',
                        data: {
                            event : syncModalData.flaggedRecords.find(element => element.uid === id),
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            actionLabel === 'Add' ?
                            addedDiv.html('<span class="text-success fw-bold">Added</span>'):
                            addedDiv.html('<span class="text-success fw-bold">Overwritten</span>');
                            syncModalData.flaggedRecords= syncModalData.flaggedRecords.filter(element => element['uid'] !==id);
                            $("#SyncModalTertiaryButton").prop('disabled', syncModalData.flaggedRecords.length === 0);

                        },
                        error: function(xhr) {
                            let errorMsg = 'Failed to update records.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            addedDiv.html('<span class="text-danger fw-bold">Failed, try again</span>');
                            submitBtn.show();
                            ignoreButton.show();
                        }
                    });
                });
                $(document).on('click', 'button[id*="ignore-btn-"]', function () {
                    const id = Number(this.id.slice(11));
                    const submitBtn = $(`button[id="submit-btn-${id}"]`);
                    let addedDiv  = $(`<div class="d-flex justify-content-center align-items-center text-center">
                        <span class="spinner-border spinner-border-sm text-primary"></span>
                        </div>`);

                    $(this).after(addedDiv);
                    $(this).hide();
                    $(submitBtn).hide();

                    syncModalData.flaggedRecords = syncModalData.flaggedRecords.filter(element => element.uid !== id);
                    $("#SyncModalTertiaryButton").prop('disabled', syncModalData.flaggedRecords.length === 0);
                    addedDiv.html('<span class="text-danger fw-bold">Ignored</span>');


                });

                var calculateModalData = {
                    flaggedRecords: [],
                    conflictedRecords: [],
                    pendingCount: 0,
                    touched: false
                };

                // tracks which page the calculate modal footer buttons should act on
                // 'flagged' | 'confirm' | 'success' | 'error' | null
                let calculateModalPage = null;

                const calcAbnormalityLabels = {
                    'missing_first_in': 'Missing first clock in',
                    'double_in_with_out': 'Double clock in',
                    'orphan_out': 'Extra clock out',
                    'irregular_sequence': 'Irregular event sequence'
                };
                const calcAbnormalityColors = {
                    'missing_first_in': 'bg-warning text-dark',
                    'double_in_with_out': 'bg-danger',
                    'orphan_out': 'bg-primary',
                    'irregular_sequence': 'bg-info text-dark'
                };

                const formatCalcEventDate = (timestamp) => timestamp ? String(timestamp).split(' ')[0] : '';
                const formatCalcEventTime = (timestamp) => timestamp ? String(timestamp).split(' ')[1] : '';

                const buildSequenceHtml = (events) => events.map(ev => {
                    const typeName = ev.event_type ? ev.event_type.name : 'Unknown';
                    const cls = typeName === 'Clock In' ? 'bg-success'
                            : typeName === 'Clock Out' ? 'bg-danger'
                            : typeName === 'Break In' ? 'bg-warning text-dark'
                            : 'bg-info text-dark';
                    const fromSaved = (ev.machine_id == 0) ? ' <span class="fw-normal fst-italic">(saved)</span>' : '';
                    return `<span class="badge ${cls} me-1 mb-1" id="event-badge-${ev.emp_id}-${formatCalcEventDate(ev.timestamp)}-${formatCalcEventTime(ev.timestamp)}">${typeName}${formatCalcEventTime(ev.timestamp)}${fromSaved}</span>`;
                }).join('<span class="me-1 fw-bold">&rarr;</span>');
               

                const buildConflictsSection = () => {
                    let html = '';
                    const grouped = {};
                    calculateModalData.conflictedRecords.forEach((record) => {
                        if (!grouped[record.employee_id]) {
                            grouped[record.employee_id] = [];
                        }
                        grouped[record.employee_id].push({...record});
                    });

                    Object.values(grouped).forEach(empRecords => {
                        const name = empRecords[0].employee_name;
                        const empId = empRecords[0].employee_id;
                        html +=`<div class="d-flex align-items-center">
                                    <hr class="flex-grow-1">
                                    <div class="bg-white text-center  p-2 fw-bold  border-danger-subtle rounded border mb-2 mt-2">
                                    ${name} (#${empId})
                                    </div>
                                    <hr class="flex-grow-1">
                                    <div
                                        class="arrow-box bg-white text-center p-2 d-flex justify-content-center align-items-center gap-2 fw-bold border-danger-subtle rounded border"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#conflicts-${empId}"
                                        aria-expanded="false"
                                        aria-controls="conflicts-${empId}">
                                        <span class="calc-count-badge" data-emp="${empId}" data-remaining="${empRecords.length}" id="conflict-count-${empId}">${empRecords.length} remaining</span>
                                        <i class="bi bi-chevron-down arrow"></i>
                                    </div>
                                </div>
                                <div class="collapse" id="conflicts-${empId}">`
                        empRecords.forEach(record => {
                            const c = record.conflicts || {};
                            html += `
                            <div class="border border-5 border-secondary-subtle shadow  bg-white rounded p-2 mb-2" data-emp="${record.employee_id}" data-name=${record.employee_name}  id="calc-conflict-card-${record.employee_id}-${record.date}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-dark">${record.date}</span>
                                    <div class="calc-conflict-status small"></div>
                                </div>
                                <div class="calc-conflict-body">
                                <div class="mb-1 small fw-bold">Events in sequence:</div>
                                        <div class="p-2 border mb-2  rounded border-secondary-subtle">
                                            <div class=" small">${buildSequenceHtml(record.events || [])}</div>
                                        </div>
                                <div class=" g-2 d-flex  justify-content-between">
                                    <div class="d-flex gap-2">`;
                            if (c.clock_in) {
                                html += `
                                    <div class="">
                                        <label class="small fw-bold d-block mb-1">Clock In</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="calc-choice-in-${record.employee_id}-${record.date}" id="calc-in-old-${record.employee_id}-${record.date}" value="old" checked>
                                            <label class="form-check-label small" for="calc-in-old-${record.employee_id}-${record.date}">Old: <span class="text-muted">${c.clock_in.old}</span></label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="calc-choice-in-${record.employee_id}-${record.date}" id="calc-in-new-${record.employee_id}-${record.date}" value="new">
                                            <label class="form-check-label small" for="calc-in-new-${record.employee_id}-${record.date}">New: <span class="text-muted">${c.clock_in.new}</span></label>
                                        </div>
                                    </div>`;
                            }
                            if (c.clock_out) {
                                html += `
                                    <div class="">
                                        <label class="small fw-bold d-block mb-1">Clock Out</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="calc-choice-out-${record.employee_id}-${record.date}" id="calc-out-old-${record.employee_id}-${record.date}" value="old" checked>
                                            <label class="form-check-label small" for="calc-out-old-${record.employee_id}-${record.date}">Old: <span class="text-muted">${c.clock_out.old}</span></label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="calc-choice-out-${record.employee_id}-${record.date}" id="calc-out-new-${record.employee_id}-${record.date}" value="new">
                                            <label class="form-check-label small" for="calc-out-new-${record.employee_id}-${record.date}">New: <span class="text-muted">${c.clock_out.new}</span></label>
                                        </div>
                                    </div>`;
                            }
                            html += `</div>
                                    <div class="d-flex justify-content-center align-items-end">
                                        ${(c.clock_in || c.clock_out)
                                            ? `<button type="button" class="btn btn-primary btn-sm" id="calc-conflict-submit-${record.employee_id}-${record.date}">Submit</button>`
                                            : 'No conflicts'}
                                    </div>
                                </div>
                                </div>
                            </div>`;
                        });
                        html +=`</div>`;
                    });
                    return html;
                };

                // renders a single flagged-record card (warning + the flagged action buttons).
                // used both when building the whole flagged section and when a resolved conflict
                // turns out to be flagged (so it re-renders in the same card).
                const buildFlaggedCard = (record) => {
                    const label = calcAbnormalityLabels[record.abnormality] || record.abnormality || 'Abnormal sequence';
                    const labelColor = calcAbnormalityColors[record.abnormality] || 'bg-warning text-dark';
                    const state = record.state || 'abnormal';
                    const isDerivable = state === 'derivable';
                    const stateBadge = isDerivable
                        ? '<span class="badge bg-info text-dark">flagged - derivable</span>'
                        : '<span class="badge bg-danger">abnormal</span>';
                    let actions = '';
                    if (state === 'derivable') {
                        actions = `
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success btn-sm flex-grow-1 calc-flag-action" data-action="addpurposeful" id="flag-${record.employee_id}-${record.date}-addpurposeful">Add purposeful</button>
                                <button type="button" class="btn btn-secondary btn-sm flex-grow-1 calc-flag-action" data-action="ignore" id="flag-${record.employee_id}-${record.date}-ignore">Ignore</button>
                            </div>`;
                    } else {
                        actions = `
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success btn-sm flex-grow-1 calc-flag-action" data-action="add" id="flag-${record.employee_id}-${record.date}-add">Add</button>
                                <button type="button" class="btn btn-primary btn-sm flex-grow-1 calc-flag-action" data-action="calculated" id="flag-${record.employee_id}-${record.date}-calculated">Mark as calculated</button>
                                <button type="button" class="btn btn-secondary btn-sm flex-grow-1 calc-flag-action" data-action="ignore" id="flag-${record.employee_id}-${record.date}-ignore">Ignore</button>
                            </div>`;
                    }
                    return `
                    <div class="border border-5 border-secondary-subtle shadow  bg-white rounded p-2 mb-2" data-emp="${record.employee_id}" id="calc-flagged-card-${record.employee_id}-${record.date}">
                            <div class="d-flex gap-2 flex-wrap justify-content-between align-items-center mb-1">
                                <span class="badge bg-dark">${record.date}</span>
                                <div class="calc-flagged-status small"></div>
                                <div>
                                <span class="badge ${labelColor}">${label}</span>
                                ${stateBadge}
                                </div>
                            </div>
                            <div class="calc-flagged-body">
                            <div class="mb-1 small fw-bold">Events in sequence:</div>
                        <div class="p-2 border mb-2  rounded border-secondary-subtle">
                            <div class=" small">${buildSequenceHtml(record.events || [])}</div>
                        </div>
                        <div class="row g-2 justify-content-end">
                            <div class="col-md-8 d-flex gap-2 flex-wrap justify-content-end">
                                ${actions}
                            </div>
                        </div>
                        </div>
                    </div>`;
                };

                const buildFlaggedSection = () => {
                    let html = '';
                    const grouped = {};
                    calculateModalData.flaggedRecords.forEach((record) => {
                        if (!grouped[record.employee_id]) {
                            grouped[record.employee_id] = [];
                        }
                        grouped[record.employee_id].push({...record});
                    });


                    Object.values(grouped).forEach(empRecords => {
                        const name = empRecords[0].employee_name;
                        const empId = empRecords[0].employee_id;
                        html +=`<div class="d-flex align-items-center">
                                    <hr class="flex-grow-1">
                                    <div class="bg-white text-center  p-2 fw-bold  border-danger-subtle rounded border mb-2 mt-2">
                                    ${name} (#${empId})
                                    </div>
                                    <hr class="flex-grow-1">
                                    <div
                                        class="arrow-box bg-white text-center p-2  d-flex justify-content-center align-items-center gap-2 fw-bold border-danger-subtle rounded border"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#flagged-${empId}"
                                        aria-expanded="false"
                                        aria-controls="flagged-${empId}">
                                        <span class="calc-count-badge" data-emp="${empId}" data-remaining="${empRecords.length}" id="flagged-count-${empId}">${empRecords.length} remaining</span>
                                        <i class="bi bi-chevron-down arrow"></i>
                                    </div>
                                </div>
                                <div class="collapse" id="flagged-${empId}">`
                        empRecords.forEach(record => {
                            html += buildFlaggedCard(record);
                        });
                        html +=`</div>`
                    });

                    return html;
                };

                const decrementCalcCount = (sectionType, employeeId) => {
                    const prefix = sectionType === 'conflict' ? 'conflict-count-' : 'flagged-count-';
                    const $badge = $('.calc-count-badge').filter(function () {
                        return this.id === `${prefix}${employeeId}`;
                    });
                    if (!$badge.length) return;

                    const remaining = Math.max(0, Number($badge.attr('data-remaining')) - 1);
                    $badge.attr('data-remaining', remaining).text(`${remaining} remaining`);
                };

                const resetCalculateModal = () => {
                    $('#calculateModalFlagged').addClass('d-none');
                    $('#calculateModalSuccess').addClass('d-none');
                    $('#calculateModalConfirm').addClass('d-none');
                    $('#calculateModalError').addClass('d-none');
                    $('#calculateModalProgress').addClass('d-none');
                    $('.calculateModalMessage').html('');
                    $('#calculateModalPrimaryButton').hide();
                    $('#calculateModalSecondaryButton').hide();
                    $('#calculateModalTertiaryButton').hide();
                    $('#calculateModalDialog').removeClass('modal-xl modal-lg');
                    calculateModalPage = null;
                    calculateModalData.flaggedRecords = [];
                    calculateModalData.conflictedRecords = [];
                    calculateModalData.pendingCount = 0;
                    calculateModalData.touched = false;
                }

                // Calculate Attendance button handler (SSE stream via AJAX)
                let calcReader = null;
                let calcFinished = false;

                const finishCalculate = (errorMsg, response) => {
                    if (calcFinished) return;
                    calcFinished = true;

                    $('#calculateModal .modal-footer').removeClass('d-none');

                    const $btn = $("#calculateBtn");
                    $btn.prop("disabled", false).text($btn.data('originalText') || 'Calculate');
                    $('#syncBtn').prop('disabled', false);

                    const flagged = $('#calculateModalFlagged');
                    const success = $('#calculateModalSuccess');
                    const error  = $('#calculateModalError');
                    const message = $('.calculateModalMessage');
                    const primaryBtn = $('#calculateModalPrimaryButton');
                    const secondaryBtn = $('#calculateModalSecondaryButton');
                    const tertiaryBtn = $('#calculateModalTertiaryButton');

                    if (errorMsg) {
                        const htmlStatement = `<h5>Error</h5><div class="alert alert-danger mb-0 ">${errorMsg}. </div>`;
                        error.html(htmlStatement);
                        $('#calculateModalProgress').addClass('d-none');
                        error.removeClass('d-none');
                        primaryBtn.text('Ok').show();
                        secondaryBtn.hide();
                        tertiaryBtn.hide();
                        calculateModalPage = 'error';
                        $('#calculateModalCloseBtn').show();
                        calculateModal.show();
                        return;
                    }

                    var flaggedRecords = response.flaggedRecords || [];
                    var conflictedRecords = response.conflictedRecords || [];
                    var recordsModified = response.recordsModified ;
                    var recordsCreated = response.recordsCreated ;
                    var recordsFlagged = response.recordsFlagged ;
                    var totalRecords = response.totalRecords;

                    let htmlmessage=`
                        <div class="alert alert-success mb-1">
                            <strong>Calculation complete:</strong><br>
                            <span class="badge bg-success ">${recordsCreated}</span> new record(s) calculated <br>
                            <span class="badge bg-success ">${recordsModified}</span> records modified.<br>
                            <span class="badge bg-success ">${recordsFlagged}</span> records flagged , <span class="badge bg-warning ">${flaggedRecords.length || 0}</span> of which needs intervention.<br>
                            <span class="badge bg-warning ">${conflictedRecords.length}</span> records contain contain conflicting times.<br>
                            <span class="badge bg-secondary ">${totalRecords}</span> records total in database.
                            </div>`;

                    message.html(htmlmessage);

                    if((flaggedRecords && flaggedRecords.length>0) || (conflictedRecords && conflictedRecords.length>0)){
                        calculateModalData.flaggedRecords = flaggedRecords;
                        calculateModalData.conflictedRecords = conflictedRecords;
                        calculateModalData.pendingCount = flaggedRecords.length + conflictedRecords.length;

                        let interventionHtml = ``;

                        if(conflictedRecords.length > 0){
                            interventionHtml += `

                                <h5 class="fw-bold text-danger-emphasis mt-4 mb-4">
                                    Conflicted records (${conflictedRecords.length})
                                    </H5>
                                    choose old or new times :
                                    <div class="rounded py-1 px-2">
                                `
                                + buildConflictsSection();
                                +`</div>`
                        }

                        if(flaggedRecords.length > 0){
                            interventionHtml += `
                            <h5 class="fw-bold text-danger-emphasis mt-4 mb-4">
                            Flagged records (${flaggedRecords.length})
                            </h5>
                            <div class="rounded py-1 px-2">`
                            + buildFlaggedSection();
                            +`</div>`
                            }

                        message.append(interventionHtml);
                        message.find('input[type="radio"][value="old"]').prop('checked', true);
                        message.find('[data-bs-toggle="collapse"]').each(function () {
                            new bootstrap.Collapse(this, { toggle: false });
                        });
                        $('#calculateModalProgress').addClass('d-none');
                        flagged.removeClass('d-none');
                        $('#calculateModalDialog').removeClass('modal-lg').addClass('modal-xl');
                        primaryBtn.text('ok').show();
                        calculateModalPage = 'flagged';
                        $('#calculateModalCloseBtn').hide();

                        // switch to the confirmation page
                        const showCalculateConfirm = function(){
                            flagged.addClass('d-none');
                            const confirmMsg = $('.calculateModalMessage', $('#calculateModalConfirm'));
                            confirmMsg.html(`
                                You still have some unresolved conflicts.All the flagged records will be <span class="text-danger fw-bold">ignored</span> if you continue.Do You want to proceed ?`);
                            $('#calculateModalConfirm').removeClass('d-none');
                            calculateModalPage = 'confirm';
                            primaryBtn.text('Yes, ignore all').show();
                            secondaryBtn.text('Back').removeClass('d-none').show();
                        };

                        // perform the ignore-all action
                        // the OK button on the warning (confirm) page spins while the ignore
                        // requests run, then the modal switches to a success page.
                        const showCalculateSuccess = function(){
                            $('#calculateModalConfirm').addClass('d-none');
                            $('.calculateModalMessage', $('#calculateModalSuccess'))
                                .html('<div class="alert alert-success mb-0"><strong>Done!</strong> All unresolved records have been handled.</div>');
                            $('#calculateModalSuccess').removeClass('d-none');
                            calculateModalPage = 'success';
                            primaryBtn.text('OK').show();
                            secondaryBtn.addClass('d-none');
                            $('#calculateModalCloseBtn').show();
                        };
                        const runIgnoreAll = function(){
                            const emptyRoute = "{{ route('employee_times.addEmptyAttendanceRecord') }}";
                            const derivable = (calculateModalData.flaggedRecords || [])
                                .filter(r => (r.state || 'abnormal') === 'derivable');
                            const reqs = derivable.map(r => $.ajax({
                                url: emptyRoute,
                                type: 'POST',
                                data: {
                                    employee_id: r.employee_id,
                                    date: r.date,
                                    _token: "{{ csrf_token() }}"
                                }
                            }));
                            calculateModalData.pendingCount = 0;
                            calculateModalData.touched = true;
                            $('.calculateModalStatus').remove();

                            // spin the OK button while the requests run (stay on the warning page)
                            primaryBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Ignoring...');

                            if(reqs.length === 0){
                                // nothing to ignore -> jump straight to the success page
                                primaryBtn.prop('disabled', false);
                                showCalculateSuccess();
                                return;
                            }
                            $.when.apply($, reqs).always(function(){
                                primaryBtn.prop('disabled', false);
                                showCalculateSuccess();
                            });
                        };
                        calculateModalData.showCalculateConfirm = showCalculateConfirm;
                        calculateModalData.runIgnoreAll = runIgnoreAll;
                    }
                    else{
                        $('#calculateModalProgress').addClass('d-none');
                        success.removeClass('d-none');
                        primaryBtn.text('ok').show();
                        calculateModalPage = 'success';
                        $('#calculateModalCloseBtn').show();
                    }


                    calculateModal.show();

                    // Optionally reload the page or grid
                    // location.reload();
                };

                $("#calculateBtn").on("click", function() {
                    const $btn = $(this);
                    $btn.data('originalText', $btn.text());
                    $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span>Calculating...');
                    $('#syncBtn').prop('disabled', true);

                    resetCalculateModal();

                    // show the progress page inside the modal while the stream runs
                    $('#calculateModalProgress').removeClass('d-none');
                    $('#calculateModal .modal-footer').addClass('d-none');
                    $('#calc-import-log').val('');
                    $('#calc-import-progress').val(0);
                    $('#calc-import-progress-label').text('0%');
                    calculateModalPage = 'progress';
                    calculateModal.show();

                    calcFinished = false;
                    calcReader = createSseReader({
                        onProgress: function(payload) {
                            const progress = Number(payload.progress) || 0;
                            $('#calc-import-progress').val(progress);
                            $('#calc-import-progress-label').text(`${progress}%`);
                            const logEl = $('#calc-import-log');
                            if (payload.message) {
                                logEl.val((logEl.val() + '\n' + payload.message).trim());
                                logEl.scrollTop(logEl[0].scrollHeight);
                            }
                        },
                        onError: function(payload) {
                            finishCalculate(payload.message || "An error occurred while Calculating Attendance.", null);
                        },
                        onDone: function(response) {
                            finishCalculate(null, response);
                        }
                    });
                    calcReader.reset();

                    $.ajax({
                        url: "{{ route('employee_times.calculateAttendance') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        xhr: function() {
                            const xhr = new XMLHttpRequest();
                            // read the stream incrementally
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState >= 3) {
                                    calcReader.consume(xhr.responseText);
                                }
                            };
                            return xhr;
                        },
                        success: function(response) {
                            calcReader.consume(response);
                        },
                        error: function(xhr) {
                            if (!calcFinished) {
                                const errorMsg = xhr.responseJSON?.error || "An error occurred while Calculating Attendance.";
                                finishCalculate(errorMsg, null);
                            }
                        }
                    });
                });


                $('#calculateModalPrimaryButton').on('click', function(){
                    if (calculateModalPage === 'flagged') {
                        // 'ok' -> head to the ignore-all confirmation
                        if (typeof calculateModalData.showCalculateConfirm === 'function') {
                            calculateModalData.showCalculateConfirm();
                        }
                        return;
                    }
                    if (calculateModalPage === 'confirm') {
                        if (typeof calculateModalData.runIgnoreAll === 'function') {
                            calculateModalData.runIgnoreAll();
                        }
                        return;
                    }
                    calculateModal.hide();
                    calculateModalPage = null;
                })

                // Back on the confirm page -> return to the flagged list
                $('#calculateModalSecondaryButton').on('click', function(){
                    if (calculateModalPage !== 'confirm') return;
                    $('#calculateModalConfirm').addClass('d-none');
                    $('#calculateModalFlagged').removeClass('d-none');
                    calculateModalPage = 'flagged';
                    $('#calculateModalPrimaryButton').text('ok').show();
                    $(this).addClass('d-none');
                })

                // conflict rows : one submit per employee/day -> sends clock in & clock out choices together
                $(document).on('click', '[id^="calc-conflict-submit-"]', function(){
                    const id = this.id;
                    const date = id.slice(-10);
                    const employeeId = id.slice('calc-conflict-submit-'.length, -11);
                    const record = calculateModalData.conflictedRecords.find(element => element.date === date && String(element.employee_id) === employeeId);
                    if (!record) return;
                    const statusEl = $(`#calc-conflict-card-${record.employee_id}-${record.date} .calc-conflict-status`);
                    const $btn = $(this);
                    const c = record.conflicts || {};

                    // resolve the chosen radio ('old'|'new') to the actual H:i:s timestamp
                    const clockInPick  = c.clock_in  ? $(`input[name="calc-choice-in-${employeeId}-${date}"]:checked`).val() : null;
                    const clockOutPick = c.clock_out ? $(`input[name="calc-choice-out-${employeeId}-${date}"]:checked`).val() : null;
                    const clockInChoice  = c.clock_in  ? (clockInPick === 'old' ? c.clock_in.old  : c.clock_in.new)  : null;
                    const clockOutChoice = c.clock_out ? (clockOutPick === 'old' ? c.clock_out.old : c.clock_out.new) : null;

                    if ((c.clock_in && !clockInChoice) || (c.clock_out && !clockOutChoice)) {
                        statusEl.html('<span class="text-danger fw-bold">Please choose old or new for each time before submitting.</span>');
                        return;
                    }

                    $btn.prop('disabled', true);
                    const conflictOriginalText = $btn.html();
                    $btn.html('<span class="spinner-border spinner-border-sm me-1"></span>');
                    $.ajax({
                        url: "{{ route('employee_times.resolveCalculationConflicts') }}",
                        type: "POST",
                        data: {
                            choices: {
                                employee_id: record.employee_id,
                                date: record.date,
                                clock_in: clockInChoice,
                                clock_out: clockOutChoice
                            },
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            calculateModalData.touched = true;

                            // clear the submit spinner first so it can never linger, even if the
                            // flagged re-render below fails for any reason.
                            $btn.prop('disabled', false).html(conflictOriginalText);

                            // after resolving the conflict the record became flagged -> re-render it
                            // in the same card with a warning and the flagged-record action buttons,
                            // so the user keeps going through the flagged pathways. it stays pending
                            // until one of those actions is taken, so pendingCount is not decremented.
                            if (response.flagged) {
                                const flaggedRecord = response.flagged;
                                flaggedRecord.state = flaggedRecord.state || 'abnormal';
                                calculateModalData.flaggedRecords.push(flaggedRecord);
                                $(`#calc-conflict-card-${record.employee_id}-${record.date}`)
                                    .html(buildFlaggedCard(flaggedRecord));
                                return;
                            }

                            calculateModalData.pendingCount--;
                            const $card = $(`#calc-conflict-card-${record.employee_id}-${record.date}`);
                            $card.css({'opacity': 0.5, 'pointer-events': 'none'}).addClass('calc-card-resolved');
                            $card.find('.calc-conflict-body').hide();
                            $card.find('.calc-conflict-status').html('<span class="text-success fw-bold">Conflict resolved</span>');
                            decrementCalcCount('conflict', record.employee_id);
                            if (calculateModalData.pendingCount <= 0) {
                                calculateModalPage = null;
                                $('#calculateModalCloseBtn').show();
                            }
                        },
                        error: function(xhr) {
                            const errorMsg = xhr.responseJSON?.error || "Failed to resolve the conflict.";
                            statusEl.html(`<span class="text-danger fw-bold">${errorMsg}</span>`);
                            $btn.prop('disabled', false).html(conflictOriginalText);
                        }
                    });
                });

                // flagged rows : dispatch the action (addpurposeful / add / calculated / ignore) per state
                $(document).on('click', '.calc-flag-action', function(){
                    const $btn = $(this);
                    const action = $btn.data('action');
                    const card = $btn.closest('[id^="calc-flagged-card-"]');
                    const cardId = card.attr('id').replace('calc-flagged-card-', '');
                    const date = cardId.slice(-10);
                    const employeeId = String(card.data('emp'));
                    const record = calculateModalData.flaggedRecords.find(
                        element => String(element.employee_id) === employeeId && element.date === date
                    );
                    if (!record) return;
                    const statusEl = card.find('.calc-flagged-status');
                    const state = record.state || 'abnormal';
                    const sectionType = card.closest('.collapse').attr('id').startsWith('conflicts-') ? 'conflict' : 'flagged';

                    // ignore on an abnormal record : do nothing -> will pop up next run
                    if (action === 'ignore' && state === 'abnormal') {
                        calculateModalData.pendingCount--;
                        card.css({'opacity': 0.5, 'pointer-events': 'none'}).addClass('calc-card-resolved');
                        card.find('.calc-flagged-body').hide();
                        card.find('.calc-flagged-status').html('<span class="text-secondary fw-bold">Record ignored - will show again next time</span>');
                        decrementCalcCount(sectionType, employeeId);
                        if (calculateModalData.pendingCount <= 0) {
                            calculateModalPage = null;
                            $('#calculateModalCloseBtn').show();
                        }
                        return;
                    }

                    const routes = {
                        'addpurposeful': "{{ route('employee_times.addRelevantAttendanceInfo') }}",
                        'add': "{{ route('employee_times.addEmptyAttendanceRecord') }}",
                        'calculated': "{{ route('employee_times.ignoreEvents') }}",
                        'ignore': "{{ route('employee_times.addEmptyAttendanceRecord') }}"
                    };
                    const successMessages = {
                        'addpurposeful': 'Purposeful data added',
                        'add': 'Empty record added',
                        'calculated': 'Events marked as calculated',
                        'ignore': state === 'derivable' ? 'Record ignored - empty record added' : 'Record ignored - will show again next time'
                    };
                    const url = routes[action];
                    if (!url) return;

                    const originalText = $btn.html();
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {
                            employee_id: record.employee_id,
                            date: record.date,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            calculateModalData.touched = true;
                            calculateModalData.pendingCount--;
                            card.css({'opacity': 0.5, 'pointer-events': 'none'}).addClass('calc-card-resolved');
                            card.find('.calc-flagged-body').hide();
                            card.find('.calc-flagged-status').html(`<span class="text-success fw-bold">${successMessages[action] || originalText}</span>`);
                            decrementCalcCount(sectionType, employeeId);

                            // once every flagged/conflicted record is resolved the user may close
                            if (calculateModalData.pendingCount <= 0) {
                                calculateModalPage = null;
                                $('#calculateModalCloseBtn').show();
                            }
                        },
                        error: function(xhr) {
                            const errorMsg = xhr.responseJSON?.error || "Failed to process the record.";
                            statusEl.html(`<span class="text-danger fw-bold">${errorMsg}</span>`);
                            $btn.prop('disabled', false).html(originalText);
                        }
                    });
                });

                // refresh the grid so resolved times show up
                // keep the calculate modal open until all flagged/conflicted records are resolved
                $('#calculateModal').on('hide.bs.modal', function (e) {
                    if (calculateModalPage === 'flagged' || calculateModalPage === 'confirm' || calculateModalPage === 'progress') {
                        e.preventDefault();
                    }
                });

                $('#calculateModal').on('hidden.bs.modal', function () {
                    if (calculateModalData.touched) {
                        location.reload();
                    }
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

            const employeeTimesData = [
                @foreach($employeeTimes as $item)
                {
                    id: {{ $item->id }},
                    employee: `{{ optional($item->employee)->first_name }} {{ optional($item->employee)->mid_name }} {{ optional($item->employee)->last_name }}`,
                    employee_id: {{ $item->employee_id }},
                    first_name: `{{ optional($item->employee)->first_name }}`,
                    mid_name: `{{ optional($item->employee)->mid_name }}`,
                    last_name: `{{ optional($item->employee)->last_name }}`,
                    date: `{{ $item->date }}`,
                    date_str: `{{ $item->date }}`,
                    time_in: `{{ $item->clock_in }}`,
                    time_out: `{{ $item->clock_out }}`,
                    total_time: `{{ $item->total_time ?? '' }}`,
                    status: `{{ $item->off_day ? 'Yes' : 'No' }}`,
                    vacation_type: `{{ $item->vacation_type ?? '' }}`,
                    flagged: `{{ $item->flagged ? 'Yes' : 'No' }}`,
                    break_flag : `{{ $item->break_flag??  'pass' }}`,
                    total_leave_diff : `{{ $item->total_leave_diff ?? '' }}`,
                    total_break_diff : `{{ $item->total_break_diff ?? '' }}`,
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
            window.deleteItem = deleteItem;

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
                        { dataField: "total_leave_diff", caption: "Leave Difference", allowFiltering: true, headerFilter: { allowSearch: true }, cellTemplate: function(container, options) { $(container).text(displayDiff(options.data.total_leave_diff)); } },
                        { dataField: "total_break_diff", caption: "Break Difference", allowFiltering: true, headerFilter: { allowSearch: true }, cellTemplate: function(container, options) { $(container).text(displayDiff(options.data.total_break_diff)); } },
                        { dataField: "flagged", caption: "Flagged", allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
                        { dataField: "break_flag", caption: "Break Flag", allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
                        { dataField: "employee_id", visible: false, allowFiltering: true },
                        { dataField: "first_name", visible: false, allowFiltering: true },
                        { dataField: "mid_name", visible: false, allowFiltering: true },
                        { dataField: "last_name", visible: false, allowFiltering: true },
                        { dataField: "date_str", visible: false, allowFiltering: true },

                        {
                            dataField: "extra_minus",
                            caption: "Extra-Minus",
                            allowFiltering: true,
                            headerFilter: { allowSearch: true },
                            cellTemplate: function(container, options) {
                                const extraMinusTime = calculateExtraMinus(options.data.total_time, options.data.vacation_type);
                                $(container).text(extraMinusTime);
                            }
                        },
                        { dataField: "status", caption: "Off Day", allowFiltering: true, headerFilter: { allowSearch: true } },
                        { dataField: "vacation_type", caption: "Status", allowFiltering: true, headerFilter: { allowSearch: true } },
                        { dataField: "reason", caption: "Reason", allowFiltering: true, headerFilter: { allowSearch: true } },
                        {
                            caption: "Actions",
                            cellTemplate: function(container, options) {
                                const editLink = `<a href="${options.data.editUrl}" target="_blank" rel="noopener noreferrer" style="color: #0d6efd; text-decoration: underline; margin-right: 10px;">Edit</a>`;
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
                            const flaggedRow = e.data.flagged === 'Yes' || e.data.break_flag !== 'pass';

                            if (flaggedRow) {
                                e.rowElement.addClass('flagged')
                            } else if (vacationType === 'off') {
                                e.rowElement.addClass('weekend');
                            } else if (vacationType === 'vacation') {
                                e.rowElement.addClass('vacation');
                            } else if (vacationType === 'holiday') {
                                e.rowElement.addClass('holiday');
                            } else if (vacationType === 'sick leave') {
                                e.rowElement.addClass('sickleave');
                            } else if (vacationType === 'unpaid') {
                                e.rowElement.addClass('unpaid');
                            } else if (vacationType === 'half day vacation') {
                                e.rowElement.addClass('halfday');
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
                                    icon: 'add',
                                    text: 'Bulk Add',
                                    hint: 'Add punch time for multiple employees',
                                    onClick: function() {
                                        $('#bulkAddModal').modal('show');
                                    }
                                }
                            },
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
            // filter logic: show all months and years until filters are applied
                $('#filterMonth').val('');
                $('#filterYear').val('');

                // Employee searchable dropdown (max 4 shown + Show more)
                var employeeFilterOptions = @json($employees->map(function ($e) {
                    return ['id' => $e->id, 'name' => trim($e->first_name . ' ' . $e->mid_name . ' ' . $e->last_name)];
                }));
                var empVisibleLimit = 4;
                var empExpanded = false;
                var empFilteredList = employeeFilterOptions;

                function renderEmployeeFilterList() {
                    var limit = empExpanded ? empFilteredList.length : Math.min(empVisibleLimit, empFilteredList.length);
                    var html = '';
                    for (var i = 0; i < limit; i++) {
                        var emp = empFilteredList[i];
                        var name = String(emp.name).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
                        html += '<div class="emp-filter-item" data-id="' + emp.id + '" data-name="' + name + '" style="padding:6px 10px; cursor:pointer; font-size:0.85rem;">' + name + '</div>';
                    }
                    if (empFilteredList.length > empVisibleLimit) {
                        html += empExpanded
                            ? '<div class="emp-filter-show-less" style="padding:6px 10px; cursor:pointer; font-size:0.8rem; color:#0d6efd; border-top:1px solid #eee;">Show less</div>'
                            : '<div class="emp-filter-show-more" style="padding:6px 10px; cursor:pointer; font-size:0.8rem; color:#0d6efd; border-top:1px solid #eee;">Show more (' + (empFilteredList.length - empVisibleLimit) + ' more)</div>';
                    }
                    if (!empFilteredList.length) {
                        html = '<div style="padding:6px 10px; font-size:0.85rem; color:#6c757d;">No employees found</div>';
                    }
                    $('#filterEmployeeList').html(html);
                }

                function filterEmployeeOptions() {
                    var q = $('#filterEmployeeName').val().trim().toLowerCase();
                    empFilteredList = q
                        ? employeeFilterOptions.filter(function (e) { return String(e.name).toLowerCase().indexOf(q) !== -1; })
                        : employeeFilterOptions;
                    empExpanded = false;
                    renderEmployeeFilterList();
                }

                $('#filterEmployeeName').on('focus input', function () {
                    filterEmployeeOptions();
                    $('#filterEmployeeList').show();
                });
                $('#filterEmployeeList').on('click', '.emp-filter-item', function () {
                    $('#filterEmployeeId').val($(this).data('id'));
                    $('#filterEmployeeName').val($(this).data('name'));
                    $('#filterEmployeeList').hide();
                });
                $('#filterEmployeeList').on('click', '.emp-filter-show-more', function (e) {
                    e.stopPropagation();
                    empExpanded = true;
                    renderEmployeeFilterList();
                });
                $('#filterEmployeeList').on('click', '.emp-filter-show-less', function (e) {
                    e.stopPropagation();
                    empExpanded = false;
                    renderEmployeeFilterList();
                });
                $(document).on('click', function (e) {
                    if (!$(e.target).closest('#filterEmployeeName, #filterEmployeeList').length) {
                        $('#filterEmployeeList').hide();
                    }
                });

                function applyEmployeeTimeFilter() {
                    var employeeId = $('#filterEmployeeId').val();
                    var month = $('#filterMonth').val();
                    var year = $('#filterYear').val();

                    var filters = [];

                    if (employeeId) {
                        filters.push(['employee_id', '=', parseInt(employeeId, 10)]);
                    }
                    if (month && year) {
                        var padMonth = month.padStart(2, '0');
                        filters.push(['date_str', 'contains', year + '-' + padMonth]);
                    } else if (month) {
                        var padMonth = month.padStart(2, '0');
                        filters.push(['date_str', 'contains', '-' + padMonth + '-']);
                    } else if (year) {
                        filters.push(['date_str', 'contains', year + '-']);
                    }

                    dataGridInstance.filter(filters.length > 0 ? filters : null);
                }

                // Apply Filter
                $('#applyFilterBtn').on('click', applyEmployeeTimeFilter);

                // Clear Filter
                $('#clearFilterBtn').on('click', function () {
                    $('#filterEmployeeId').val('');
                    $('#filterEmployeeName').val('');
                    $('#filterMonth').val('');
                    $('#filterYear').val('');
                    applyEmployeeTimeFilter();
                });

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
                        clear_reason: $('#bulk_clear_reason').is(':checked') ? 1 : 0,
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

                // Bulk Add form submission
                $('#bulkAddForm').on('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const submitBtn = $('#bulkAddSubmitBtn');

                    // Prevent double submission
                    if (submitBtn.prop('disabled')) {
                        return false;
                    }

                    // Collect selected employee IDs from checkboxes
                    const selectedEmployeeIds = [];
                    $('.bulk-add-employee-checkbox:checked').each(function() {
                        selectedEmployeeIds.push($(this).val());
                    });

                    // Validate at least one employee is selected
                    if (selectedEmployeeIds.length === 0) {
                        const errorsDiv = $('#bulk-add-errors');
                        errorsDiv.text('Please select at least one employee.');
                        errorsDiv.removeClass('d-none');
                        return false;
                    }

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('date', $('#bulk_add_date').val());
                    formData.append('clock_in', $('#bulk_add_clock_in').val());
                    formData.append('clock_out', $('#bulk_add_clock_out').val());
                    formData.append('vacation_type', $('#bulk_add_vacation_type').val());
                    formData.append('reason', $('#bulk_add_reason').val());

                    // Add each selected employee ID
                    selectedEmployeeIds.forEach(function(id) {
                        formData.append('employee_ids[]', id);
                    });

                    const errorsDiv = $('#bulk-add-errors');
                    const successDiv = $('#bulk-add-success');

                    errorsDiv.addClass('d-none');
                    successDiv.addClass('d-none');
                    submitBtn.prop('disabled', true).text('Adding...');

                    $.ajax({
                        url: '{{ route('employee_times.bulk-add') }}',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            successDiv.text(response.message || 'Records added successfully!');
                            successDiv.removeClass('d-none');

                            setTimeout(function() {
                                $('#bulkAddModal').modal('hide');
                                window.location.reload();
                            }, 1500);
                        },
                        error: function(xhr) {
                            let errorMsg = 'Failed to add records.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                            }
                            errorsDiv.text(errorMsg);
                            errorsDiv.removeClass('d-none');
                            submitBtn.prop('disabled', false).text('Add Records');
                        }
                    });

                    return false;
                });

                // Select all employees checkbox handler for bulk add
                $('#selectAllBulkAddEmployees').on('change', function() {
                    $('.bulk-add-employee-checkbox').prop('checked', $(this).prop('checked'));
                });

                // Update select all checkbox based on individual checkboxes for bulk add
                $('.bulk-add-employee-checkbox').on('change', function() {
                    const totalCheckboxes = $('.bulk-add-employee-checkbox').length;
                    const checkedCheckboxes = $('.bulk-add-employee-checkbox:checked').length;
                    $('#selectAllBulkAddEmployees').prop('checked', totalCheckboxes === checkedCheckboxes);
                });

                // Reset form when bulk add modal is closed
                $('#bulkAddModal').on('hidden.bs.modal', function() {
                    $('#bulkAddForm')[0].reset();
                    $('.bulk-add-employee-checkbox').prop('checked', false);
                    $('#selectAllBulkAddEmployees').prop('checked', false);
                    $('#bulk-add-errors').addClass('d-none');
                    $('#bulk-add-success').addClass('d-none');
                    $('#bulkAddSubmitBtn').prop('disabled', false).text('Add Records');
                });
            });});
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
// Display-only helper: shows the diff with a leading '-' (e.g. -01:02) without altering the stored value
function displayDiff(value) {
    if (!value || value === '') return '';
    const parts = String(value).split(':');
    const hhmm = parts.length >= 2 ? parts[0].padStart(2, '0') + ':' + parts[1].padStart(2, '0') : String(value);
    return '-' + hhmm;
}

// Function to calculate extra/minus time compared to 9 hours (or 4.5 for half-day)
function calculateExtraMinus(totalTimeString, vacationType) {
    if (!totalTimeString || totalTimeString === '') return '';

    // Parse total time string (format: "hh:mm:ss" or "hh:mm")
    const timeParts = totalTimeString.split(':');
    if (timeParts.length < 2) return '';

    const hours = parseInt(timeParts[0], 10) || 0;
    const minutes = parseInt(timeParts[1], 10) || 0;
    const seconds = parseInt(timeParts[2], 10) || 0;

    // Convert total time to minutes
    const totalMinutes = (hours * 60) + minutes + (seconds / 60);

    // Determine standard minutes based on vacation type
    let standardMinutes = 9 * 60; // Default 9 hours
    if (vacationType && vacationType.toLowerCase() === 'half day vacation') {
        standardMinutes = 4.5 * 60; // 4.5 hours for half-day
    }

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
