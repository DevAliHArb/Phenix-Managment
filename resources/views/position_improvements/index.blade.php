@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="headerContainer" >
    <h1>Position Improvements</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div style="display: flex; justify-content: flex-end; margin-bottom: 18px;">
        <a href="{{ route('position-improvements.create') }}" class="btn btn-primary">Add Position Improvement</a>
    </div>
    </div>
    <div id="positionImprovementsGrid"></div>
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
                editUrl: `{{ route('position-improvements.edit', $item->id) }}`,
                deleteUrl: `{{ route('position-improvements.destroy', $item->id) }}`
            },
            @endforeach
        ];

        $(function() {
            $("#positionImprovementsGrid").dxDataGrid({
                dataSource: positionImprovementsData,
                columns: [
                    { dataField: "id", caption: "ID", width: 60, allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
                    { dataField: "position", caption: "Position", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "employee", caption: "Employee", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "start_date", caption: "Start Date", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "end_date", caption: "End Date", allowFiltering: true, headerFilter: { allowSearch: true } },
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
                noDataText: 'No position improvements found.'
            });
        });
    </script>
    @endpush
</div>
@endsection
