@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="headerContainer" >
    <h1>Sick Leaves</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div style="display: flex; justify-content: flex-end; margin-bottom: 18px;">
        <a href="{{ route('sick-leaves.create') }}" class="btn btn-primary">Add Sick Leave</a>
    </div>
    </div>
    <div id="sickLeavesGrid"></div>
    @push('scripts')
    <script>
        const sickLeavesData = [
            @foreach($items as $item)
            {
                id: {{ $item->id }},
                employee: `{{ optional($item->employee)->first_name }} {{ optional($item->employee)->last_name }}`,
                date: `{{ $item->date }}`,
                reason: `{{ $item->reason }}`,
                attachment: `{!! $item->attachment ? '<a href="' . asset('public/attachments/' . $item->attachment) . '" target="_blank">View</a>' : '' !!}`,
                editUrl: `{{ route('sick-leaves.edit', $item->id) }}`,
                deleteUrl: `{{ route('sick-leaves.destroy', $item->id) }}`
            },
            @endforeach
        ];

        $(function() {
            $("#sickLeavesGrid").dxDataGrid({
                dataSource: sickLeavesData,
                columns: [
                    { dataField: "id", caption: "ID", width: 60, allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
                    { dataField: "employee", caption: "Employee", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "date", caption: "Date", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "reason", caption: "Reason", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "attachment", caption: "Attachment", allowFiltering: false, encodeHtml: false, cellTemplate: function(container, options) { $(container).html(options.data.attachment); } },
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
                            showInColumn: 'employee',
                            alignByColumn: true
                        },
                        {
                            summaryType: 'count',
                            displayFormat: 'Total: {0} rows'
                        }
                    ]
                },
                noDataText: 'No sick leaves found.'
            });
        });
    </script>
    @endpush
</div>
@endsection
