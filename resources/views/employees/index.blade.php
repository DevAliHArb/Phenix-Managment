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
                <div class="card-header bg-info text-white">Employee Times</div>
                <div class="card-body">
                    <div id="employeeTimesGrid"></div>
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
        showUrl: `{{ route('employees.show', $employee->id) }}`,
        editUrl: `{{ route('employees.edit', $employee->id) }}`,
        deleteUrl: `{{ route('employees.destroy', $employee->id) }}`
    },
    @endforeach
];

let selectedEmployeeIndex = 0;

function renderEmployeeTimesGrid(times) {
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
            renderEmployeeTimesGrid(employee.employee_times || []);
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
                renderEmployeeTimesGrid(firstEmployee.employee_times || []);
                detailsSection.style.display = '';
            }
        }
    });
});
</script>
@endpush

@endsection
