@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
    <style>
        .dx-row-selected {
            background-color: #e3f2fd !important;
        }
        .dx-row-selected:hover {
            background-color: #bbdefb !important;
        }
        #addSalariesBtn {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        #addSalariesBtn:hover {
            transform: translateY(-1px);
        }
    </style>
@endsection

@section('content')
<div  style="width:100%">
    <div class="headerContainer" >
    <h1>Position</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div style="display: flex; justify-content: flex-end; margin-bottom: 18px;">
        <a href="{{ route('position-improvements.create') }}?return_url={{ urlencode(request()->fullUrl()) }}" class="btn btn-primary">Add Position Improvement</a>
    </div>
    </div>
    <div id="positionImprovementsGrid"></div>
    
    <div style="margin-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <h2 style="margin: 0;">Position Salaries</h2>
            <button id="addSalariesBtn" class="btn btn-success" onclick="addSalariesForSelected()" title="Add new salary record">Add Salaries</button>
        </div>
        <div id="positionSalariesGrid"></div>
    </div>
    
    @push('scripts')
    <script>
        const positionImprovementsData = [
            @foreach($items as $item)
            {
                id: {{ $item->id }},
                position: `{{ optional($item->position)->name }}`,
                employee: `{{ optional($item->employee)->first_name }} {{ optional($item->employee)->last_name }}`,
                start_date: `{{ $item->start_date }}`,
                end_date: `{{ $item->end_date }}`,
                is_active: `{{ $item->is_active ? 'Active' : 'Inactive' }}`,
                editUrl: `{{ route('position-improvements.edit', $item->id) }}`,
                deleteUrl: `{{ route('position-improvements.destroy', $item->id) }}`
            },
            @endforeach
        ];

        let selectedRowId = null;

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

        function addSalariesForSelected() {
            const returnUrl = encodeURIComponent(window.location.href);
            if (selectedRowId) {
                // If a row is selected, pre-fill the position improvement
                window.location.href = `{{ route('salary.create') }}?position_improvement_id=${selectedRowId}&return_url=${returnUrl}`;
            } else {
                // If no row is selected, open normal salary creation form
                window.location.href = `{{ route('salary.create') }}?return_url=${returnUrl}`;
            }
        }

        $(function() {
            $("#positionImprovementsGrid").dxDataGrid({
                dataSource: positionImprovementsData,
                selection: {
                    mode: 'single'
                },
                onSelectionChanged: function(e) {
                    const selectedRows = e.selectedRowsData;
                    
                    if (selectedRows.length > 0) {
                        selectedRowId = selectedRows[0].id;
                    } else {
                        selectedRowId = null;
                    }
                },
                columns: [
                    { dataField: "id", caption: "ID", width: 60, allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
                    { dataField: "position", caption: "Position", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "employee", caption: "Employee", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "start_date", caption: "Start Date", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "end_date", caption: "End Date", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "is_active", caption: "Status", width: 100, allowFiltering: true, headerFilter: { allowSearch: true } },
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
                            showInColumn: 'position',
                            alignByColumn: true
                        },
                        {
                            summaryType: 'count',
                            displayFormat: 'Total: {0} rows'
                        }
                    ]
                },
                noDataText: 'No position improvements found.',
                onRowClick: function(e) {
                    const grid = e.component;
                    if (grid.isRowSelected(e.rowIndex)) {
                        grid.deselectRows([e.key]);
                    } else {
                        grid.selectRows([e.key], false);
                    }
                }
            });

            // Position Salaries Grid - using same data structure as salary index
            const positionSalariesData = [
                @if(isset($salaryItems))
                    @foreach($salaryItems as $item)
                    {
                        id: {{ $item->id }},
                        employee: `{{ optional(optional($item->positionImprovement)->employee)->first_name }} {{ optional(optional($item->positionImprovement)->employee)->last_name }}`,
                        position: `{{ optional(optional($item->positionImprovement)->position)->name }}`,
                        salary: `{{ $item->salary }}`,
                        start_date: `{{ $item->start_date }}`,
                        end_date: `{{ $item->end_date }}`,
                        status: `{{ $item->status ? 'Active' : 'Inactive' }}`,
                        editUrl: `{{ route('salary.edit', $item->id) }}`,
                        deleteUrl: `{{ route('salary.destroy', $item->id) }}`
                    },
                    @endforeach
                @endif
            ];

            $("#positionSalariesGrid").dxDataGrid({
                dataSource: positionSalariesData,
                columns: [
                    { dataField: "id", caption: "ID", width: 60, allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
                    { dataField: "employee", caption: "Employee", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "position", caption: "Position", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "start_date", caption: "Start Date", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "end_date", caption: "End Date", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "salary", caption: "Salary", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "status", caption: "Status", allowFiltering: true, headerFilter: { allowSearch: true } },
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
                    placeholder: 'Search salary records...'
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
                            displayFormat: 'Total: {0} records',
                            showInColumn: 'position',
                            alignByColumn: true
                        },
                        {
                            column: 'salary',
                            summaryType: 'sum',
                            displayFormat: 'Total Salaries: {0}',
                            valueFormat: 'currency'
                        }
                    ]
                },
                noDataText: 'No position salary records found.'
            });
        });
    </script>
    @endpush
</div>
@endsection
