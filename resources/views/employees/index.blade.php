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
                    <div class="card-header bg-primary text-white">Employee Details</div>
                    <div class="card-body" id="employee-details-card">
                        <!-- Populated by JS -->
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
                        <button id="tab-employee-times" type="button" class="btn employee-tab-btn btn-info active">Employee Times</button>
                        <button id="tab-employee-salaries" type="button" class="btn employee-tab-btn btn-outline-info">Employee Salaries</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="employeeTimesGrid"></div>
                    <div id="employeeSalariesGrid" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Prepare employees data for DevExtreme
const employeesData = [
    @foreach($employees as $employee)
    {
        id: {{ $employee->id }},
        name: `{{ $employee->first_name }} {{ $employee->last_name }}`,
        image: `<img src='{{ $employee->image }}' alt='Image' style='max-width:50px;max-height:50px;'>`,
        position: `{{ optional($employee->position)->name }}`,
        date_of_birth: `{{ $employee->date_of_birth }}`,
        start_date: `{{ $employee->start_date }}`,
        end_date: `{{ $employee->end_date }}`,
        employment_type: `{{ optional($employee->EmployeeType)->name }}`,
        address: `{{ $employee->address ?? '' }}`,
        phone: `{{ $employee->phone ?? '' }}`,
        status: `{{ $employee->status ?? '' }}`,
        working_hours_from: `{{ $employee->working_hours_from ?? '' }}`,
        working_hours_to: `{{ $employee->working_hours_to ?? '' }}`,
        yearly_vacations_total: `{{ $employee->yearly_vacations_total ?? 0 }}`,
        yearly_vacations_used: `{{ $employee->yearly_vacations_used ?? 0 }}`,
        yearly_vacations_left: `{{ $employee->yearly_vacations_left ?? 0 }}`,
        sick_leave_used: `{{ $employee->sick_leave_used ?? 0 }}`,
        last_salary: `{{ $employee->last_salary ?? '' }}`,
        employee_times: @json($employee->employeeTimes ?? []),
        positionImprovements: [
            @foreach(($employee->positionImprovements ?? []) as $item)
            {
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


function renderEmployeeTimesGrid(times) {
    $("#employeeTimesGrid").show();
    $("#employeeSalariesGrid").hide();
    // Add title if not present
    if ($('#employeeTimesGridTitle').length === 0) {
        $("#employeeTimesGrid").before('<h6 id="employeeTimesGridTitle" class="mb-2">Employee Times</h6>');
    }
    $("#employeeTimesGridTitle").show();
    $("#employeeTimesGrid").dxDataGrid({
        dataSource: times,
        columns: [
            { dataField: "date", caption: "Date" },
            { dataField: "clock_in", caption: "Clock In" },
            { dataField: "clock_out", caption: "Clock Out" },
            { dataField: "total_time", caption: "Total Time" },
            { dataField: "off_day", caption: "Off Day", cellTemplate: function(container, options) { $(container).text(options.data.off_day ? 'Yes' : 'No'); } },
            { dataField: "reason", caption: "Reason" }
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
                    summaryType: 'count',
                    displayFormat: 'Total: {0} rows'
                }
            ]
        },
        noDataText: 'No employee times found.'
    });
}


function renderEmployeeSalariesGrid(positionImprovements) {
    $("#employeeTimesGrid").hide();
    $("#employeeSalariesGrid").show();
    // Remove Employee Times title if present
    $("#employeeTimesGridTitle").hide();
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
            // { dataField: "id", caption: "ID", visible: false },
            { dataField: "position_name", caption: "Position" },
            { dataField: "employee_name", caption: "Employee", visible: false },
            { dataField: "start_date", caption: "Start Date" },
            { dataField: "end_date", caption: "End Date" }
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
            renderSalariesGrid(e.data.salaries || []);
        },
        onContentReady: function(e) {
            // Show salaries for the first row by default
            if (e.component.getVisibleRows().length > 0 && !e.component.getSelectedRowKeys().length) {
                e.component.selectRowsByIndexes([0]);
                const first = e.component.getVisibleRows()[0].data;
                renderSalariesGrid(first.salaries || []);
            } else if (e.component.getSelectedRowKeys().length) {
                const selectedIndex = e.component.getRowIndexByKey(e.component.getSelectedRowKeys()[0]);
                const selected = e.component.getVisibleRows()[selectedIndex]?.data;
                if (selected) renderSalariesGrid(selected.salaries || []);
            } else {
                renderSalariesGrid([]);
            }
        },
        onOptionChanged: function(e) {
            if (e.name === 'dataSource') {
                // Data changed, select first row and update salaries
                const grid = $("#positionImprovementsGrid").dxDataGrid("instance");
                const rows = grid.getVisibleRows();
                if (rows.length > 0) {
                    grid.selectRowsByIndexes([0]);
                    renderSalariesGrid(rows[0].data.salaries || []);
                } else {
                    renderSalariesGrid([]);
                }
            }
        }
    });
    // Render empty salaries grid initially (will be filled by onContentReady)
    function renderSalariesGrid(salaries) {
        $("#salariesGrid").dxDataGrid({
            dataSource: salaries,
            columns: [
                { dataField: "id", caption: "ID" },
                { dataField: "salary", caption: "Salary" },
                { dataField: "status", caption: "Status" }
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
            { dataField: "image", caption: "Image", allowFiltering: false, encodeHtml: false, cellTemplate: function(container, options) { $(container).html(options.data.image); } },
            { dataField: "position", caption: "Position", allowFiltering: true, headerFilter: { allowSearch: true } },
            // { dataField: "date_of_birth", caption: "Birthdate", allowFiltering: true, headerFilter: { allowSearch: true } },
            // { dataField: "start_date", caption: "Start Date", allowFiltering: true, headerFilter: { allowSearch: true } },
            // { dataField: "end_date", caption: "End Date", allowFiltering: true, headerFilter: { allowSearch: true } },
            { dataField: "employment_type", caption: "Employment Type", allowFiltering: true, headerFilter: { allowSearch: true } },
            {
                caption: "Actions",
                cellTemplate: function(container, options) {
                    const editLink = `<a href="${options.data.editUrl}" style="color: #0d6efd; text-decoration: underline; margin-right: 10px;">Edit</a>`;
                    const deleteLink = `<a href="#" style="color: #dc3545; text-decoration: underline;" onclick="event.preventDefault(); if(confirm('Are you sure?')) { var f = document.createElement('form'); f.style.display='none'; f.method='POST'; f.action='${options.data.deleteUrl}'; f.innerHTML='<input type=\'hidden\' name=\'_token\' value=\'{{ csrf_token() }}\'><input type=\'hidden\' name=\'_method\' value=\'DELETE\'>'; document.body.appendChild(f); f.submit(); }">Delete</a>`;
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
            let html = `<div class="row">
                <div >
                    <h5>${employee.name}</h5>
                    <p><strong>Address:</strong> ${employee.address ?? ''}</p>
                    <p><strong>Birthdate:</strong> ${employee.date_of_birth ?? ''}</p>
                    <p><strong>Phone:</strong> ${employee.phone ?? ''}</p>
                    <p><strong>Status:</strong> ${employee.status ?? ''}</p>
                    <p><strong>Position:</strong> ${employee.position ?? ''}</p>
                    <p><strong>Start Date:</strong> ${employee.start_date ?? ''}</p>
                    <p><strong>End Date:</strong> ${employee.end_date ?? ''}</p>
                    <p><strong>Working Hours:</strong> ${employee.working_hours_from ?? ''} - ${employee.working_hours_to ?? ''}</p>
                    <p><strong>Yearly Vacations:</strong> Total: ${employee.yearly_vacations_total ?? 0}, Used: ${employee.yearly_vacations_used ?? 0}, Left: ${employee.yearly_vacations_left ?? 0}</p>
                    <p><strong>Sick Leave Used:</strong> ${employee.sick_leave_used ?? 0}</p>
                    <p><strong>Last Salary:</strong> ${employee.last_salary ?? ''}</p>
                </div>
            </div>`;
            detailsCard.innerHTML = html;
            // Show correct tab and grid
            if ($("#tab-employee-times").hasClass("active")) {
                renderEmployeeTimesGrid(employee.employee_times || []);
            } else {
                renderEmployeeSalariesGrid(employee.positionImprovements || []);
            }
            detailsSection.style.display = '';
            detailsSection.scrollIntoView({behavior: 'smooth'});
        },
        onContentReady: function(e) {
            // Select and show the first employee by default
            if (e.component.getVisibleRows().length > 0 && selectedEmployeeIndex === 0) {
                e.component.selectRowsByIndexes([0]);
                const firstEmployee = e.component.getVisibleRows()[0].data;
                const detailsSection = document.getElementById('employee-details-section');
                const detailsCard = document.getElementById('employee-details-card');
                let html = `<div class="row">
                    <div >
                        <h5>${firstEmployee.name}</h5>
                        <p><strong>Address:</strong> ${firstEmployee.address ?? ''}</p>
                        <p><strong>Birthdate:</strong> ${firstEmployee.date_of_birth ?? ''}</p>
                        <p><strong>Phone:</strong> ${firstEmployee.phone ?? ''}</p>
                        <p><strong>Status:</strong> ${firstEmployee.status ?? ''}</p>
                        <p><strong>Position:</strong> ${firstEmployee.position ?? ''}</p>
                        <p><strong>Start Date:</strong> ${firstEmployee.start_date ?? ''}</p>
                        <p><strong>End Date:</strong> ${firstEmployee.end_date ?? ''}</p>
                        <p><strong>Working Hours:</strong> ${firstEmployee.working_hours_from ?? ''} - ${firstEmployee.working_hours_to ?? ''}</p>
                        <p><strong>Yearly Vacations:</strong> Total: ${firstEmployee.yearly_vacations_total ?? 0}, Used: ${firstEmployee.yearly_vacations_used ?? 0}, Left: ${firstEmployee.yearly_vacations_left ?? 0}</p>
                        <p><strong>Sick Leave Used:</strong> ${firstEmployee.sick_leave_used ?? 0}</p>
                        <p><strong>Last Salary:</strong> ${firstEmployee.last_salary ?? ''}</p>
                    </div>
                </div>`;
                detailsCard.innerHTML = html;
                if ($("#tab-employee-times").hasClass("active")) {
                    renderEmployeeTimesGrid(firstEmployee.employee_times || []);
                } else {
                    renderEmployeeSalariesGrid(firstEmployee.positionImprovements || []);
                }

                // Tab click handlers
                $("#tab-employee-times").off("click").on("click", function() {
                    $(this).addClass("active btn-info").removeClass("btn-outline-info");
                    $("#tab-employee-salaries").removeClass("active btn-info").addClass("btn-outline-info");
                    const employee = e.component.getVisibleRows()[selectedEmployeeIndex]?.data;
                    renderEmployeeTimesGrid(employee?.employee_times || []);
                });
                $("#tab-employee-salaries").off("click").on("click", function() {
                    $(this).addClass("active btn-info").removeClass("btn-outline-info");
                    $("#tab-employee-times").removeClass("active btn-info").addClass("btn-outline-info");
                    const employee = e.component.getVisibleRows()[selectedEmployeeIndex]?.data;
                    renderEmployeeSalariesGrid(employee?.positionImprovements || []);
                });
                detailsSection.style.display = '';
            }
        }
    });
});
</script>
@endpush

@endsection
