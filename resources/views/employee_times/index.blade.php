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
    
        <div id="employeeTimesGrid"></div>
        @push('scripts')
        <script>
            const employeeTimesData = [
                @foreach($employeeTimes as $item)
                {
                    id: {{ $item->id }},
                    employee: `{{ optional($item->employee)->first_name }} {{ optional($item->employee)->last_name }}`,
                    date: `{{ $item->date }}`,
                    time_in: `{{ $item->clock_in }}`,
                    time_out: `{{ $item->clock_out }}`,
                    status: `{{ $item->off_day ? 'Yes' : 'No' }}`,
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
                        { dataField: "date", caption: "Date", allowFiltering: true, headerFilter: { allowSearch: true } },
                        { dataField: "time_in", caption: "Time In", allowFiltering: true, headerFilter: { allowSearch: true } },
                        { dataField: "time_out", caption: "Time Out", allowFiltering: true, headerFilter: { allowSearch: true } },
                        { dataField: "status", caption: "Status", allowFiltering: true, headerFilter: { allowSearch: true } },
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
                    selection: {
                        mode: 'multiple',
                        showCheckBoxesMode: 'always'
                    },
                    export: {
                        enabled: true,
                        allowExportSelectedData: true,
                        texts: {
                            exportAll: 'Export All',
                            exportSelectedRows: 'Export Selected'
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
@endsection
