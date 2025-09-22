@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
    <!-- DevExtreme CSS moved to layout -->
@endsection

@section('content')
<div class="container">
    <div class="headerContainer" >
    <h1>Salaries</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div style="display: flex; justify-content: flex-end; margin-bottom: 18px;">
        <a href="{{ route('salary.create') }}" class="btn btn-primary">Add Salary</a>
    </div>
    </div>
    <div id="salaryGrid"></div>
    @push('scripts')
    <script>
        const salaryData = [
            @foreach($items as $item)
            {
                id: {{ $item->id }},
                employee: `{{ optional(optional($item->positionImprovement)->employee)->first_name }} {{ optional(optional($item->positionImprovement)->employee)->last_name }}`,
                position: `{{ optional(optional($item->positionImprovement)->position)->name }}`,
                salary: `{{ $item->salary }}`,
                status: `{{ $item->status ? 'Active' : 'Inactive' }}`,
                editUrl: `{{ route('salary.edit', $item->id) }}`,
                deleteUrl: `{{ route('salary.destroy', $item->id) }}`
            },
            @endforeach
        ];

        $(function() {
            $("#salaryGrid").dxDataGrid({
                dataSource: salaryData,
                columns: [
                    { dataField: "id", caption: "ID", width: 60, allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
                    { dataField: "employee", caption: "Employee", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "position", caption: "Position", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "salary", caption: "Salary", allowFiltering: true, headerFilter: { allowSearch: true } },
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
                paging: { pageSize: 10 },
                pager: {
                    showPageSizeSelector: true,
                    allowedPageSizes: [5, 10, 20],
                    showInfo: false,
                    showNavigationButtons: true,
                    visible: true
                },
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
                noDataText: 'No salaries found.'
            });
        });
    </script>
    @endpush
    
</div>
@endsection
