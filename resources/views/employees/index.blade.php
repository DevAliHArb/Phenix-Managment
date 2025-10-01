@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
    <style>
        /* Pointer cursor and hover effect for DevExtreme DataGrid rows */
        .dx-datagrid-rowsview .dx-row:not(.dx-freespace-row):not(.dx-row-alt) {
            cursor: pointer;
        }
        .dx-datagrid-rowsview .dx-row:not(.dx-freespace-row):hover {
            background-color: #e9ecef !important;
        }
    </style>
@endsection

@section('content')
<div  style="width:100%">
    <div class="headerContainer" >
    <h1>Employees</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div style="display: flex; justify-content: flex-end; margin-bottom: 18px;">
        <a href="{{ route('employees.create') }}" class="btn btn-primary">Add Employee</a>
    </div>

    </div>
    <div class="row">
        <div class="col-md-8 mb-3">
            <div id="employeesGrid"></div>
        </div>
        <div class="col-md-4 mb-3">
            <div id="employee-details-section" style="display:none;">
                <div class="card">
                    <div class="card-header bg-info text-white" style="padding:0;">
                        <style>
                            .employee-detail-tab-btn {
                                border-radius: 0;
                                font-weight: 600;
                                font-size: 1.05rem;
                                transition: background 0.2s, color 0.2s, box-shadow 0.2s;
                                box-shadow: none;
                            }
                            .employee-detail-tab-btn.active, .employee-detail-tab-btn:focus {
                                background: linear-gradient(90deg, #0d6efd 60%, #0dcaf0 100%);
                                color: #fff !important;
                                border: none;
                                box-shadow: 0 2px 8px rgba(13,110,253,0.08);
                            }
                            .employee-detail-tab-btn:not(.active) {
                                background: #f8f9fa;
                                color: #0d6efd;
                                border: 1px solid #0dcaf0;
                            }
                            .employee-detail-tab-btn:not(.active):hover {
                                background: #e9ecef;
                                color: #0a58ca;
                            }
                        </style>
                        <div class="btn-group w-100" role="group" aria-label="Employee Detail Tabs">
                            <button id="tab-employee-details" type="button" class="btn employee-detail-tab-btn btn-info active">Employee Details</button>
                            <button id="tab-employee-address" type="button" class="btn employee-detail-tab-btn btn-outline-info">Employee Address</button>
                        </div>
                    </div>
                    <div class="card-body" style="min-height: 400px;">
                        <div id="employee-details-card">
                            <!-- Populated by JS -->
                        </div>
                        <div id="employee-address-card" style="display:none;">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-header bg-info text-white" style="padding:0;">
                    <style>
                        .employee-tab-btn {
                            border-radius: 0;
                            font-weight: 600;
                            font-size: 1.05rem;
                            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
                            box-shadow: none;
                        }
                        .employee-tab-btn.active, .employee-tab-btn:focus {
                            background: linear-gradient(90deg, #0d6efd 60%, #0dcaf0 100%);
                            color: #fff !important;
                            border: none;
                            box-shadow: 0 2px 8px rgba(13,110,253,0.08);
                        }
                        .employee-tab-btn:not(.active) {
                            background: #f8f9fa;
                            color: #0d6efd;
                            border: 1px solid #0dcaf0;
                        }
                        .employee-tab-btn:not(.active):hover {
                            background: #e9ecef;
                            color: #0a58ca;
                        }
                    </style>
                    <div class="btn-group w-100" role="group" aria-label="Employee Tabs">
                        <button id="tab-employee-salaries" type="button" class="btn employee-tab-btn btn-info active">Progression</button>
                        <button id="tab-employee-times" type="button" class="btn employee-tab-btn btn-outline-info">Punch Time</button>
                        <button id="tab-employee-vacations" type="button" class="btn employee-tab-btn btn-outline-info">Transactions Days</button>
                    </div>
                </div>
                <div class="card-body" style="min-height: 400px;">
                    <div id="employeeTimesGrid" style="display:none;"></div>
                    <div id="employeeSalariesGrid"></div>
                    <div id="employeeVacationsGrid" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
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

// Function to format status with color coding
function formatStatus(status) {
    if (!status || status === '') return '';
    
    const statusLower = status.toLowerCase();
    let bgColor, textColor;
    
    if (statusLower === 'active') {
        bgColor = '#28a745'; // Green background
        textColor = '#fff';   // White text
    } else if (statusLower === 'inactive') {
        bgColor = '#dc3545'; // Red background
        textColor = '#fff';   // White text
    } else {
        bgColor = '#6c757d'; // Gray background for other statuses
        textColor = '#fff';   // White text
    }
    
    return `<span style="background-color: ${bgColor}; color: ${textColor}; padding: 2px 6px; border-radius: 4px; font-size: 14px; font-weight: 500;">${status}</span>`;
}

// Function to format working days from boolean values
function formatWorkingDays(sunday, monday, tuesday, wednesday, thursday, friday, saturday) {
    const days = [];
    if (monday) days.push('Monday');
    if (tuesday) days.push('Tuesday');
    if (wednesday) days.push('Wednesday');
    if (thursday) days.push('Thursday');
    if (friday) days.push('Friday');
    if (saturday) days.push('Saturday');
    if (sunday) days.push('Sunday');
    
    return days.length > 0 ? days.join(', ') : 'No working days set';
}

// Prepare employees data for DevExtreme
const employeesData = [
    @foreach($employees as $employee)
    {
        id: {{ $employee->id }},
        name: `{{ $employee->first_name }} {{ $employee->mid_name }} {{ $employee->last_name }}`,
        image: `<img src='{{ $employee->image }}' alt='Image' style='width:50px;height:50px;object-fit:cover;border-radius:0;border:none;'>`,
        position: `{{ optional($employee->position)->name }}`,
        date_of_birth: `{{ $employee->date_of_birth }}`,
        start_date: `{{ $employee->start_date }}`,
        end_date: `{{ $employee->end_date }}`,
        employment_type: `{{ optional($employee->EmployeeType)->name }}`,
        address: `{{ $employee->address ?? '' }}`,
        email: `{{ $employee->email ?? '' }}`,
        city: `{{ $employee->city ?? '' }}`,
        province: `{{ $employee->province ?? '' }}`,
        building_name: `{{ $employee->building_name ?? '' }}`,
        floor: `{{ $employee->floor ?? '' }}`,
        acc_number: `{{ $employee->acc_number ?? '' }}`,
        phone: `{{ $employee->phone ?? '' }}`,
        status: `{{ $employee->status ?? '' }}`,
        working_hours_from: `{{ $employee->working_hours_from ?? '' }}`,
        working_hours_to: `{{ $employee->working_hours_to ?? '' }}`,
        sunday: {{ $employee->sunday ? 'true' : 'false' }},
        monday: {{ $employee->monday ? 'true' : 'false' }},
        tuesday: {{ $employee->tuesday ? 'true' : 'false' }},
        wednesday: {{ $employee->wednesday ? 'true' : 'false' }},
        thursday: {{ $employee->thursday ? 'true' : 'false' }},
        friday: {{ $employee->friday ? 'true' : 'false' }},
        saturday: {{ $employee->saturday ? 'true' : 'false' }},
        yearly_vacations_total: `{{ $employee->yearly_vacations_total ?? 0 }}`,
        yearly_vacations_used: `{{ $employee->yearly_vacations_used ?? 0 }}`,
        yearly_vacations_left: `{{ $employee->yearly_vacations_left ?? 0 }}`,
        sick_leave_used: `{{ $employee->sick_leave_used ?? 0 }}`,
        last_salary: `{{ $employee->last_salary ?? '' }}`,
        housing_type: `{{ $employee->housing_type ?? '' }}`,
        owner_name: `{{ $employee->owner_name ?? '' }}`,
        owner_mobile_number: `{{ $employee->owner_mobile_number ?? '' }}`,
        employee_times: @json($employee->employeeTimes ?? []),
        yearly_vacations: @json($employee->yearlyVacations ?? []),
        employee_vacations: @json($employee->employeeVacations ?? []),
        sick_leaves: @json($employee->sickLeaves ?? []),
        positionImprovements: [
            @foreach(($employee->positionImprovements ?? []) as $item)
            {
                id: {{ $item->id }},
                position_name: `{{ optional($item->position)->name }}`,
                employee_name: `{{ optional($item->employee)->first_name }} {{ optional($item->employee)->last_name }}`,
                start_date: `{{ $item->start_date }}`,
                end_date: `{{ $item->end_date }}`,
                salaries: @json($item->salaries ?? [])
            },
            @endforeach
        ],
        showUrl: `{{ route('employees.show', $employee->id) }}`,
        editUrl: `{{ route('employees.edit', $employee->id) }}`,
        deleteUrl: `{{ route('employees.destroy', $employee->id) }}`
    },
    @endforeach
];

let selectedEmployeeIndex = 0;

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

function renderEmployeeTimesGrid(times) {
    $("#employeeTimesGrid").show();
    $("#employeeSalariesGrid").hide();
    $("#employeeVacationsGrid").hide();
    // Add title if not present
        if ($('#employeeTimesGridTitle').length === 0) {
                $("#employeeTimesGrid").before(`
                        <div id="employeeTimesGridHeader" style="display: flex; align-items: center; margin-bottom: 8px;">
                                <h6 id="employeeTimesGridTitle" class="mb-2" style="margin-bottom:0; margin-right: 16px;">Employee Times</h6>
                                <button id="exportEmployeeTimesBtn" class="btn btn-sm btn-success" style="margin-left:auto;">Export Timesheet</button>
                        </div>
                `);
                // Add modal for month selection
                $("body").append(`
                        <div class="modal fade" id="exportTimesheetModal" tabindex="-1" aria-labelledby="exportTimesheetModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exportTimesheetModalLabel">Export Timesheet</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="exportTimesheetForm">
                                            <div class="mb-3">
                                                <label for="exportMonth" class="form-label">Month</label>
                                                <select class="form-select" id="exportMonth" name="month" required>
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
                                                <label for="exportYear" class="form-label">Year</label>
                                                <input type="number" class="form-control" id="exportYear" name="year" min="2000" max="2100" value="${new Date().getFullYear()}" required>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" id="confirmExportTimesheetBtn" class="btn btn-success">Export</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                `);
        }
        $("#employeeTimesGridHeader").show();
        $("#exportEmployeeTimesBtn").off('click').on('click', function() {
                const employee = employeesData[selectedEmployeeIndex];
                if (!employee) {
                        alert('No employee selected.');
                        return;
                }
                // Show modal for month selection
            // Set default month and year to current
            const now = new Date();
            $('#exportMonth').val((now.getMonth() + 1).toString());
            $('#exportYear').val(now.getFullYear());
                const modal = new bootstrap.Modal(document.getElementById('exportTimesheetModal'));
                modal.show();
                // Confirm export handler
                $('#confirmExportTimesheetBtn').off('click').on('click', function() {
                        const month = $('#exportMonth').val();
                        const year = $('#exportYear').val();
                        if (!month || !year) {
                                alert('Please select both month and year.');
                                return;
                        }
                        const url = `/employee_times/${employee.id}/export?month=${month}&year=${year}`;
                        $(this).prop('disabled', true).text('Exporting...');
                        fetch(url, {
                                method: 'GET',
                                headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                        })
                        .then(response => {
                                if (!response.ok) throw new Error('Export failed');
                                return response.blob();
                        })
                        .then(blob => {
                                const employeeName = employee.name.replace(/\s+/g, '_');
                                const fileName = `timesheet_${employeeName}_${year}_${month.padStart ? month.padStart(2, '0') : ('0'+month).slice(-2)}.pdf`;
                                const url = window.URL.createObjectURL(blob);
                                const a = document.createElement('a');
                                a.href = url;
                                a.download = fileName;
                                document.body.appendChild(a);
                                a.click();
                                setTimeout(() => {
                                        window.URL.revokeObjectURL(url);
                                        document.body.removeChild(a);
                                }, 100);
                        })
                        .catch(() => alert('Failed to export timesheet.'))
                        .finally(() => {
                                $(this).prop('disabled', false).text('Export');
                                modal.hide();
                        });
                });
        });
    $("#employeeTimesGrid").dxDataGrid({
        dataSource: times,
        columns: [
            { dataField: "date", caption: "Date", sortOrder: "desc" },
            { dataField: "clock_in", caption: "Clock In", cellTemplate: function(container, options) { $(container).text(formatTime(options.data.clock_in)); } },
            { dataField: "clock_out", caption: "Clock Out", cellTemplate: function(container, options) { $(container).text(formatTime(options.data.clock_out)); } },
            { dataField: "total_time", caption: "Total Time" },
            { dataField: "off_day", caption: "Off Day", cellTemplate: function(container, options) { $(container).text(options.data.off_day ? 'Yes' : 'No'); } },
            { dataField: "reason", caption: "Notes" }
        ],
        showBorders: true,
        sorting: {
            mode: "multiple"
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
                    summaryType: 'count',
                    displayFormat: 'Total: {0} rows'
                }
            ]
        },
        noDataText: 'No employee times found.'
    });
}


function renderEmployeeVacationsGrid(yearlyVacations, sickLeaves, employeeVacations) {
    $("#employeeTimesGrid").hide();
    $("#employeeSalariesGrid").hide();
    $("#employeeVacationsGrid").show();
    // Hide Export Timesheet button and title if present
    $("#employeeTimesGridHeader").hide();
    
    // Add titles for both tables if not present
    // $("#employeeVacationsGrid").html('<div class="row">'
    //     + '<div class="col-md-6">'
    //     + '<h6 id="yearlyVacationsGridTitle" class="mb-2">Yearly Vacations</h6>'
    //     + '<div id="yearlyVacationsGrid"></div>'
    //     + '</div>'
    //     + '<div class="col-md-6">'
    //     + '<h6 id="sickLeavesGridTitle" class="mb-2">Sick Leaves</h6>'
    //     + '<div id="sickLeavesGrid"></div>'
    //     + '</div>'
    //     + '</div>');

    // Render Yearly Vacations grid
    // $("#yearlyVacationsGrid").dxDataGrid({
    //     dataSource: yearlyVacations,
    //     columns: [
    //         { dataField: "id", caption: "ID", width: 60, visible: false },
    //         { dataField: "date", caption: "Date" },
    //         { dataField: "reason", caption: "Reason" }
    //     ],
    //     showBorders: true,
    //     editing: {
    //         allowAdding: true
    //     },
    //     onToolbarPreparing: function(e) {
    //         // Find and modify the Add button
    //         const addButton = e.toolbarOptions.items.find(item => item.name === 'addRowButton');
    //         if (addButton) {
    //             addButton.options.onClick = function() {
    //                 const employee = employeesData[selectedEmployeeIndex];
    //                 if (!employee) {
    //                     alert('No employee selected.');
    //                     return;
    //                 }
    //                 // Redirect to yearly vacations create page with employee ID as parameter
    //                 window.location.href = `/yearly-vacations/create?employee_id=${employee.id}`;
    //             };
    //         }
    //     },
    //     paging: { pageSize: 10 },
    //     pager: {
    //         showPageSizeSelector: true,
    //         allowedPageSizes: [5, 10, 20],
    //         showInfo: false,
    //         showNavigationButtons: true,
    //         visible: true
    //     },
    //     searchPanel: {
    //         visible: true,
    //         width: 240,
    //         placeholder: 'Search...'
    //     },
    //     filterRow: {
    //         visible: true,
    //         applyFilter: 'auto'
    //     },
    //     headerFilter: {
    //         visible: true
    //     },
    //     columnChooser: {
    //         enabled: true,
    //         mode: 'dragAndDrop',
    //         title: 'Column Chooser',
    //         emptyPanelText: 'Drag a column here to hide it'
    //     },
    //     allowColumnReordering: true,
    //     summary: {
    //         totalItems: [
    //             {
    //                 summaryType: 'count',
    //                 displayFormat: 'Total: {0} rows'
    //             }
    //         ]
    //     },
    //     noDataText: 'No yearly vacations found.'
    // });

    // // Render Sick Leaves grid
    // $("#sickLeavesGrid").dxDataGrid({
    //     dataSource: sickLeaves,
    //     columns: [
    //         { dataField: "id", caption: "ID", width: 60, visible: false },
    //         { dataField: "date", caption: "Date" },
    //         { dataField: "reason", caption: "Reason" },
    //         { 
    //             dataField: "attachment", 
    //             caption: "Attachment",
    //             cellTemplate: function(container, options) {
    //                 if (options.data.attachment) {
    //                     $(container).html(`<a href="${options.data.attachment}" target="_blank" style="color: #0d6efd;">View</a>`);
    //                 } else {
    //                     $(container).text('No attachment');
    //                 }
    //             }
    //         }
    //     ],
    //     showBorders: true,
    //     editing: {
    //         allowAdding: true
    //     },
    //     onToolbarPreparing: function(e) {
    //         // Find and modify the Add button
    //         const addButton = e.toolbarOptions.items.find(item => item.name === 'addRowButton');
    //         if (addButton) {
    //             addButton.options.onClick = function() {
    //                 const employee = employeesData[selectedEmployeeIndex];
    //                 if (!employee) {
    //                     alert('No employee selected.');
    //                     return;
    //                 }
    //                 // Redirect to sick leaves create page with employee ID as parameter
    //                 window.location.href = `/sick-leaves/create?employee_id=${employee.id}`;
    //             };
    //         }
    //     },
    //     paging: { pageSize: 10 },
    //     pager: {
    //         showPageSizeSelector: true,
    //         allowedPageSizes: [5, 10, 20],
    //         showInfo: false,
    //         showNavigationButtons: true,
    //         visible: true
    //     },
    //     searchPanel: {
    //         visible: true,
    //         width: 240,
    //         placeholder: 'Search...'
    //     },
    //     filterRow: {
    //         visible: true,
    //         applyFilter: 'auto'
    //     },
    //     headerFilter: {
    //         visible: true
    //     },
    //     columnChooser: {
    //         enabled: true,
    //         mode: 'dragAndDrop',
    //         title: 'Column Chooser',
    //         emptyPanelText: 'Drag a column here to hide it'
    //     },
    //     allowColumnReordering: true,
    //     summary: {
    //         totalItems: [
    //             {
    //                 summaryType: 'count',
    //                 displayFormat: 'Total: {0} rows'
    //             }
    //         ]
    //     },
    //     noDataText: 'No sick leaves found.'
    // });


    $("#employeeVacationsGrid").dxDataGrid({
                dataSource: employeeVacations,
                columns: [
                    { dataField: "id", caption: "ID", width: 60, allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
                    { dataField: "date", caption: "Date", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "reason", caption: "Reason", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { 
                        dataField: "type", 
                        caption: "Type", 
                        allowFiltering: true, 
                        headerFilter: { allowSearch: true },
                        cellTemplate: function(container, options) {
                            $(container).text(options.data.type && options.data.type.name ? options.data.type.name : '');
                        }
                    },
                    { 
                        dataField: "attachment", 
                        caption: "Attachment", 
                        allowFiltering: false, 
                        encodeHtml: false, 
                        cellTemplate: function(container, options) {
                            if (options.data.attachment) {
                                const link = $('<a>', {
                                    text: 'View',
                                    href: options.data.attachment,
                                    target: '_blank',
                                    style: 'color: #0d6efd; text-decoration: underline; cursor: pointer;',
                                    click: function(e) {
                                        e.preventDefault();
                                        window.open('attachments/' + options.data.attachment, '_blank');
                                    }
                                });
                                $(container).append(link);
                            } else {
                                $(container).text('');
                            }
                        }
                    },
                    {
                        caption: "Actions",
                        cellTemplate: function(container, options) {
                            // Generate URLs dynamically using the vacation ID
                            const editUrl = `{{ route('employee-vacations.edit', '') }}/${options.data.id}`;
                            const deleteUrl = `{{ route('employee-vacations.destroy', '') }}/${options.data.id}`;
                            
                            const editLink = `<a href="${editUrl}" style="color: #0d6efd; text-decoration: underline; margin-right: 10px;">Edit</a>`;
                            const deleteLink = `<a href="javascript:void(0)" style="color: #dc3545; text-decoration: underline;" onclick="deleteItem('${deleteUrl}', '{{ csrf_token() }}')">Delete</a>`;
                            $(container).append(editLink + deleteLink);
                        },
                        width: 180,
                        allowFiltering: false
                    }
                ],
                showBorders: true,
                editing: {
                    allowAdding: true
                },
                onToolbarPreparing: function(e) {
                    // Find and modify the Add button
                    const addButton = e.toolbarOptions.items.find(item => item.name === 'addRowButton');
                    if (addButton) {
                        addButton.options.onClick = function() {
                            const employee = employeesData[selectedEmployeeIndex];
                            if (!employee) {
                                alert('No employee selected.');
                                return;
                            }
                            // Redirect to employee vacations create page with employee ID as parameter and lock it
                            const returnUrl = encodeURIComponent(window.location.href);
                            window.location.href = `/employee-vacations/create?employee_id=${employee.id}&lock_employee=1&return_url=${returnUrl}`;
                        };
                    }
                },
                paging: { pageSize: 10 },
                pager: {
                    showPageSizeSelector: true,
                    allowedPageSizes: [5, 10, 20],
                    showInfo: false,
                },
                filterRow: { visible: true },
                headerFilter: { visible: true },
                searchPanel: { visible: true, width: 240, placeholder: 'Search...' },
                hoverStateEnabled: true,
                rowAlternationEnabled: true,
                columnAutoWidth: true,
                wordWrapEnabled: true,
                onRowClick: function(e) {
                    console.log('Employee vacation row clicked:', e.data);
                }
            });
        }

            

function renderEmployeeSalariesGrid(positionImprovements) {
    $("#employeeTimesGrid").hide();
    $("#employeeSalariesGrid").show();
    $("#employeeVacationsGrid").hide();
    // Hide Export Timesheet button and title if present
    $("#employeeTimesGridHeader").hide();
    // Add titles for both tables if not present
    $("#employeeSalariesGrid").html('<div class="row">'
        + '<div class="col-md-7">'
        + '<h6 id="positionImprovementsGridTitle" class="mb-2">Position Improvements</h6>'
        + '<div id="positionImprovementsGrid"></div>'
        + '</div>'
        + '<div class="col-md-5">'
        + '<h6 id="salariesGridTitle" class="mb-2">Salaries</h6>'
        + '<div id="salariesGrid"></div>'
        + '</div>'
        + '</div>');

    // Render Position Improvements grid
    $("#positionImprovementsGrid").dxDataGrid({
        dataSource: positionImprovements,
        columns: [
            { dataField: "id", caption: "ID", visible: false },
            { dataField: "position_name", caption: "Current Position" },
            { dataField: "employee_name", caption: "Employee", visible: false },
            { dataField: "start_date", caption: "Start Date" },
            { dataField: "end_date", caption: "End Date" }
        ],
        showBorders: true,
        editing: {
            allowAdding: true
        },
        onToolbarPreparing: function(e) {
            // Find and modify the Add button
            const addButton = e.toolbarOptions.items.find(item => item.name === 'addRowButton');
            if (addButton) {
                addButton.options.onClick = function() {
                    const employee = employeesData[selectedEmployeeIndex];
                    if (!employee) {
                        alert('No employee selected.');
                        return;
                    }
                    // Redirect to position improvements create page with employee ID as parameter and lock it
                    const returnUrl = encodeURIComponent(window.location.href);
                    window.location.href = `/position-improvements/create?employee_id=${employee.id}&lock_employee=1&return_url=${returnUrl}`;
                };
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
                    summaryType: 'count',
                    displayFormat: 'Total: {0} rows'
                }
            ]
        },
        noDataText: 'No position improvements found.',
        onRowClick: function(e) {
            console.log('PositionImprovement row clicked:', e.data);
            const grid = $("#positionImprovementsGrid").dxDataGrid("instance");
            grid.selectRowsByIndexes([e.rowIndex]);
            renderSalariesGrid(e.data.salaries || [], e.data.id);
        },
        onContentReady: function(e) {
            // Show salaries for the first row by default
            if (e.component.getVisibleRows().length > 0 && !e.component.getSelectedRowKeys().length) {
                e.component.selectRowsByIndexes([0]);
                const first = e.component.getVisibleRows()[0].data;
                renderSalariesGrid(first.salaries || [], first.id);
            } else if (e.component.getSelectedRowKeys().length) {
                const selectedIndex = e.component.getRowIndexByKey(e.component.getSelectedRowKeys()[0]);
                const selected = e.component.getVisibleRows()[selectedIndex]?.data;
                if (selected) renderSalariesGrid(selected.salaries || [], selected.id);
            } else {
                renderSalariesGrid([], null);
            }
        },
        onOptionChanged: function(e) {
            if (e.name === 'dataSource') {
                // Data changed, select first row and update salaries
                const grid = $("#positionImprovementsGrid").dxDataGrid("instance");
                const rows = grid.getVisibleRows();
                if (rows.length > 0) {
                    grid.selectRowsByIndexes([0]);
                    renderSalariesGrid(rows[0].data.salaries || [], rows[0].data.id);
                } else {
                    renderSalariesGrid([], null);
                }
            }
        }
    });
    // Render empty salaries grid initially (will be filled by onContentReady)
    function renderSalariesGrid(salaries, positionImprovementId) {
        $("#salariesGrid").dxDataGrid({
            dataSource: salaries,
            columns: [
                { dataField: "id", caption: "ID" },
                { dataField: "salary", caption: "Salary" },
                { dataField: "status", caption: "Status" }
            ],
            showBorders: true,
            editing: {
                allowAdding: true
            },
            onToolbarPreparing: function(e) {
                // Find and modify the Add button
                const addButton = e.toolbarOptions.items.find(item => item.name === 'addRowButton');
                if (addButton) {
                    addButton.options.onClick = function() {
                        if (!positionImprovementId) {
                            alert('No position improvement selected.');
                            return;
                        }
                        // Redirect to salary create page with position improvement ID as parameter and lock it
                        const returnUrl = encodeURIComponent(window.location.href);
                        window.location.href = `/salary/create?position_improvement_id=${positionImprovementId}&lock_position_improvement=1&return_url=${returnUrl}`;
                    };
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
                        summaryType: 'count',
                        displayFormat: 'Total: {0} rows'
                    }
                ]
            },
            noDataText: 'No salaries found.'
        });
    }
}

$(function() {
    $("#employeesGrid").dxDataGrid({
    dataSource: employeesData,
    columns: [ 
            { dataField: "id", caption: "ID", width: 60, allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
            { dataField: "name", caption: "Name", allowFiltering: true, headerFilter: { allowSearch: true } },
            { dataField: "position", caption: "Current Position", allowFiltering: true, headerFilter: { allowSearch: true } },
            // { dataField: "date_of_birth", caption: "Birthdate", allowFiltering: true, headerFilter: { allowSearch: true } },
            // { dataField: "start_date", caption: "Start Date", allowFiltering: true, headerFilter: { allowSearch: true } },
            // { dataField: "end_date", caption: "End Date", allowFiltering: true, headerFilter: { allowSearch: true } },
            { dataField: "employment_type", caption: "Employment Type", allowFiltering: true, headerFilter: { allowSearch: true } },
            {
                caption: "Actions",
                cellTemplate: function(container, options) {
                    const editLink = `<a href="${options.data.editUrl}" style="color: #0d6efd; text-decoration: underline; margin-right: 10px;">Edit</a>`;
                    const deleteLink = `<a href="javascript:void(0)" style="color: #dc3545; text-decoration: underline;" onclick="deleteItem('${options.data.deleteUrl}', '{{ csrf_token() }}')">Delete</a>`;
                    $(container).append(editLink + deleteLink);
                },
                width: 200,
                allowFiltering: false
            }
        ],
        showBorders: true,
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
                    showInColumn: 'name',
                    alignByColumn: true
                },
                {
                    summaryType: 'count',
                    displayFormat: 'Total: {0} rows'
                }
            ]
        },
        noDataText: 'No employees found.',
        onRowClick: function(e) {
            console.log('Employee row clicked:', e.data);
            e.component.selectRowsByIndexes([e.rowIndex]);
            selectedEmployeeIndex = e.rowIndex;
            const employee = e.data;
            const detailsSection = document.getElementById('employee-details-section');
            const detailsCard = document.getElementById('employee-details-card');
            const addressCard = document.getElementById('employee-address-card');
            
            // Employee Details Tab Content
            let detailsHtml = `<div class="row">
                <div>
                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                        ${employee.image}
                        <h5 style="margin: 0; margin-left: 15px;">${employee.name}</h5>
                    </div>
                    <p><strong>Email:</strong> ${employee.email ?? ''}</p>
                    <p><strong>Phone:</strong> ${employee.phone ?? ''}</p>
                    <p><strong>Birthdate:</strong> ${employee.date_of_birth ?? ''}</p>
                    <p><strong>Position:</strong> ${employee.position ?? ''}</p>
                    <p><strong>Employment Type:</strong> ${employee.employment_type ?? ''}</p>
                    <p><strong>Account Number:</strong> ${employee.acc_number ?? ''}</p>
                    <p><strong>Start Date:</strong> ${employee.start_date ?? ''}</p>
                    <p><strong>End Date:</strong> ${employee.end_date ?? ''}</p>
                    <p><strong>Working Hours:</strong> ${formatTime(employee.working_hours_from)} - ${formatTime(employee.working_hours_to)}</p>
                    <p><strong>Working Days:</strong> ${formatWorkingDays(employee.sunday, employee.monday, employee.tuesday, employee.wednesday, employee.thursday, employee.friday, employee.saturday)}</p>
                    <p><strong>Yearly Vacations:</strong> Total: ${employee.yearly_vacations_total ?? 0}, Used: ${employee.yearly_vacations_used ?? 0}, Left: ${employee.yearly_vacations_left ?? 0}</p>
                    <p><strong>Sick Leave Used:</strong> ${employee.sick_leave_used ?? 0}</p>
                    <p><strong>Last Salary:</strong> ${employee.last_salary ?? ''}</p>
                    <p><strong>Status:</strong> ${formatStatus(employee.status ?? '')}</p>
                </div>
            </div>`;
            
            // Employee Address Tab Content
            // Create combined address
            const addressParts = [
                employee.address,
                employee.building_name,
                employee.floor ? `Floor ${employee.floor}` : '',
                employee.city,
                employee.province
            ].filter(part => part && part.trim() !== '');
            const combinedAddress = addressParts.length > 0 ? addressParts.join(', ') : 'No address provided';
            
            let addressHtml = `<div class="row">
                <div>
                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                        ${employee.image}
                        <h5 style="margin: 0; margin-left: 15px;">${employee.name}</h5>
                    </div>
                    <p><strong>Address:</strong> ${combinedAddress}</p>
                    <hr style="margin: 15px 0;">
                    <p><strong>Street:</strong> ${employee.address ?? ''}</p>
                    <p><strong>City:</strong> ${employee.city ?? ''}</p>
                    <p><strong>Province:</strong> ${employee.province ?? ''}</p>
                    <p><strong>Building Name:</strong> ${employee.building_name ?? ''}</p>
                    <p><strong>Floor:</strong> ${employee.floor ?? ''}</p>
                    <p><strong>Housing Type:</strong> ${employee.housing_type ? employee.housing_type.charAt(0).toUpperCase() + employee.housing_type.slice(1) : ''}</p>
                    ${employee.housing_type === 'rent' && (employee.owner_name || employee.owner_mobile_number) ? `
                        <p><strong>Owner Name:</strong> ${employee.owner_name ?? ''}</p>
                        <p><strong>Owner Mobile:</strong> ${employee.owner_mobile_number ?? ''}</p>
                    ` : ''}
                </div>
            </div>`;
            
            detailsCard.innerHTML = detailsHtml;
            addressCard.innerHTML = addressHtml;
            
            // Set up tab click handlers for employee detail tabs
            $("#tab-employee-details").off("click").on("click", function() {
                $(this).addClass("active btn-info").removeClass("btn-outline-info");
                $("#tab-employee-address").removeClass("active btn-info").addClass("btn-outline-info");
                $("#employee-details-card").show();
                $("#employee-address-card").hide();
            });
            
            $("#tab-employee-address").off("click").on("click", function() {
                $(this).addClass("active btn-info").removeClass("btn-outline-info");
                $("#tab-employee-details").removeClass("active btn-info").addClass("btn-outline-info");
                $("#employee-details-card").hide();
                $("#employee-address-card").show();
            });
            
            // Ensure the details tab is active by default
            $("#tab-employee-details").addClass("active btn-info").removeClass("btn-outline-info");
            $("#tab-employee-address").removeClass("active btn-info").addClass("btn-outline-info");
            $("#employee-details-card").show();
            $("#employee-address-card").hide();
            // Show correct tab and grid
            if ($("#tab-employee-salaries").hasClass("active")) {
                renderEmployeeSalariesGrid(employee.positionImprovements || []);
            } else if ($("#tab-employee-vacations").hasClass("active")) {
                renderEmployeeVacationsGrid(employee.yearly_vacations || [], employee.sick_leaves || [], employee.employee_vacations || []);
            } else {
                renderEmployeeTimesGrid(employee.employee_times || []);
            }
            detailsSection.style.display = '';
        },
        onContentReady: function(e) {
            // Select and show the first employee by default
            if (e.component.getVisibleRows().length > 0 && selectedEmployeeIndex === 0) {
                e.component.selectRowsByIndexes([0]);
                const firstEmployee = e.component.getVisibleRows()[0].data;
                const detailsSection = document.getElementById('employee-details-section');
                const detailsCard = document.getElementById('employee-details-card');
                const addressCard = document.getElementById('employee-address-card');
                
                // Employee Details Tab Content
                let detailsHtml = `<div class="row">
                    <div>
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            ${firstEmployee.image}
                            <h5 style="margin: 0; margin-left: 15px;">${firstEmployee.name}</h5>
                        </div>
                        <p><strong>Email:</strong> ${firstEmployee.email ?? ''}</p>
                        <p><strong>Phone:</strong> ${firstEmployee.phone ?? ''}</p>
                        <p><strong>Birthdate:</strong> ${firstEmployee.date_of_birth ?? ''}</p>
                        <p><strong>Position:</strong> ${firstEmployee.position ?? ''}</p>
                        <p><strong>Employment Type:</strong> ${firstEmployee.employment_type ?? ''}</p>
                        <p><strong>Account Number:</strong> ${firstEmployee.acc_number ?? ''}</p>
                        <p><strong>Start Date:</strong> ${firstEmployee.start_date ?? ''}</p>
                        <p><strong>End Date:</strong> ${firstEmployee.end_date ?? ''}</p>
                        <p><strong>Working Hours:</strong> ${formatTime(firstEmployee.working_hours_from)} - ${formatTime(firstEmployee.working_hours_to)}</p>
                        <p><strong>Working Days:</strong> ${formatWorkingDays(firstEmployee.sunday, firstEmployee.monday, firstEmployee.tuesday, firstEmployee.wednesday, firstEmployee.thursday, firstEmployee.friday, firstEmployee.saturday)}</p>
                        <p><strong>Yearly Vacations:</strong> Total: ${firstEmployee.yearly_vacations_total ?? 0}, Used: ${firstEmployee.yearly_vacations_used ?? 0}, Left: ${firstEmployee.yearly_vacations_left ?? 0}</p>
                        <p><strong>Sick Leave Used:</strong> ${firstEmployee.sick_leave_used ?? 0}</p>
                        <p><strong>Last Salary:</strong> ${firstEmployee.last_salary ?? ''}</p>
                        <p><strong>Status:</strong> ${formatStatus(firstEmployee.status ?? '')}</p>
                    </div>
                </div>`;
                
                // Employee Address Tab Content
                // Create combined address
                const firstEmployeeAddressParts = [
                    firstEmployee.address,
                    firstEmployee.building_name,
                    firstEmployee.floor ? `Floor ${firstEmployee.floor}` : '',
                    firstEmployee.city,
                    firstEmployee.province
                ].filter(part => part && part.trim() !== '');
                const firstEmployeeCombinedAddress = firstEmployeeAddressParts.length > 0 ? firstEmployeeAddressParts.join(', ') : 'No address provided';
                
                let addressHtml = `<div class="row">
                    <div>
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            ${firstEmployee.image}
                            <h5 style="margin: 0; margin-left: 15px;">${firstEmployee.name}</h5>
                        </div>
                        <p><strong>Address:</strong> ${firstEmployeeCombinedAddress}</p>
                        <hr style="margin: 15px 0;">
                        <p><strong>Street:</strong> ${firstEmployee.address ?? ''}</p>
                        <p><strong>City:</strong> ${firstEmployee.city ?? ''}</p>
                        <p><strong>Province:</strong> ${firstEmployee.province ?? ''}</p>
                        <p><strong>Building Name:</strong> ${firstEmployee.building_name ?? ''}</p>
                        <p><strong>Floor:</strong> ${firstEmployee.floor ?? ''}</p>
                        <p><strong>Housing Type:</strong> ${firstEmployee.housing_type ? firstEmployee.housing_type.charAt(0).toUpperCase() + firstEmployee.housing_type.slice(1) : ''}</p>
                        ${firstEmployee.housing_type === 'rent' && (firstEmployee.owner_name || firstEmployee.owner_mobile_number) ? `
                            <p><strong>Owner Name:</strong> ${firstEmployee.owner_name ?? ''}</p>
                            <p><strong>Owner Mobile:</strong> ${firstEmployee.owner_mobile_number ?? ''}</p>
                        ` : ''}
                    </div>
                </div>`;
                
                detailsCard.innerHTML = detailsHtml;
                addressCard.innerHTML = addressHtml;
                
                // Set up tab click handlers for employee detail tabs
                $("#tab-employee-details").off("click").on("click", function() {
                    $(this).addClass("active btn-info").removeClass("btn-outline-info");
                    $("#tab-employee-address").removeClass("active btn-info").addClass("btn-outline-info");
                    $("#employee-details-card").show();
                    $("#employee-address-card").hide();
                });
                
                $("#tab-employee-address").off("click").on("click", function() {
                    $(this).addClass("active btn-info").removeClass("btn-outline-info");
                    $("#tab-employee-details").removeClass("active btn-info").addClass("btn-outline-info");
                    $("#employee-details-card").hide();
                    $("#employee-address-card").show();
                });
                
                // Ensure the details tab is active by default
                $("#tab-employee-details").addClass("active btn-info").removeClass("btn-outline-info");
                $("#tab-employee-address").removeClass("active btn-info").addClass("btn-outline-info");
                $("#employee-details-card").show();
                $("#employee-address-card").hide();
                if ($("#tab-employee-salaries").hasClass("active")) {
                    renderEmployeeSalariesGrid(firstEmployee.positionImprovements || []);
                } else if ($("#tab-employee-vacations").hasClass("active")) {
                    renderEmployeeVacationsGrid(firstEmployee.yearly_vacations || [], firstEmployee.sick_leaves || [], firstEmployee.employee_vacations || []);
                } else {
                    renderEmployeeTimesGrid(firstEmployee.employee_times || []);
                }

                // Tab click handlers
                $("#tab-employee-times").off("click").on("click", function() {
                    $(this).addClass("active btn-info").removeClass("btn-outline-info");
                    $("#tab-employee-salaries").removeClass("active btn-info").addClass("btn-outline-info");
                    $("#tab-employee-vacations").removeClass("active btn-info").addClass("btn-outline-info");
                    const employee = e.component.getVisibleRows()[selectedEmployeeIndex]?.data;
                    renderEmployeeTimesGrid(employee?.employee_times || []);
                    $("#employeeTimesGridHeader").show();
                });
                $("#tab-employee-salaries").off("click").on("click", function() {
                    $(this).addClass("active btn-info").removeClass("btn-outline-info");
                    $("#tab-employee-times").removeClass("active btn-info").addClass("btn-outline-info");
                    $("#tab-employee-vacations").removeClass("active btn-info").addClass("btn-outline-info");
                    const employee = e.component.getVisibleRows()[selectedEmployeeIndex]?.data;
                    renderEmployeeSalariesGrid(employee?.positionImprovements || []);
                    $("#employeeTimesGridHeader").hide();
                });
                $("#tab-employee-vacations").off("click").on("click", function() {
                    $(this).addClass("active btn-info").removeClass("btn-outline-info");
                    $("#tab-employee-times").removeClass("active btn-info").addClass("btn-outline-info");
                    $("#tab-employee-salaries").removeClass("active btn-info").addClass("btn-outline-info");
                    const employee = e.component.getVisibleRows()[selectedEmployeeIndex]?.data;
                    renderEmployeeVacationsGrid(employee?.yearly_vacations || [], employee?.sick_leaves || [], employee?.employee_vacations || []);
                    $("#employeeTimesGridHeader").hide();
                });
                detailsSection.style.display = '';
            }
        }
    });
});
</script>
@endpush

@endsection
