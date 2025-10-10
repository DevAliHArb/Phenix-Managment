@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div style="width:100%">
    <div class="headerContainer" >
    <h1>Yearly Days Truncations</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div style="display: flex; justify-content: flex-end; margin-bottom: 18px;">
        <a href="{{ route('yearly-vacations.create') }}" class="btn btn-primary">Add Yearly Days Truncations</a>
    </div>
    </div>
    <div id="yearlyVacationsGrid"></div>
    @push('scripts')
    <script>
        const yearlyVacationsData = [
            @foreach($yearlyVacations as $vacation)
            {
                id: {{ $vacation->id }},
                employee: `{{ optional($vacation->employee)->first_name }} {{ optional($vacation->employee)->mid_name }} {{ optional($vacation->employee)->last_name }}`,
                date: `{{ $vacation->date }}`,
                reason: `{{ $vacation->reason }}`,
                showUrl: `{{ route('yearly-vacations.show', $vacation->id) }}`,
                editUrl: `{{ route('yearly-vacations.edit', $vacation->id) }}`,
                deleteUrl: `{{ route('yearly-vacations.destroy', $vacation->id) }}`
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
            $("#yearlyVacationsGrid").dxDataGrid({
                dataSource: yearlyVacationsData,
                columns: [
                    { dataField: "id", caption: "ID", width: 60, allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
                    { dataField: "employee", caption: "Employee", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "date", caption: "Date", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "reason", caption: "Reason", allowFiltering: true, headerFilter: { allowSearch: true } },
                    {
                        caption: "Actions",
                        cellTemplate: function(container, options) {
                            const editLink = `<a href="${options.data.editUrl}" style="color: #0d6efd; text-decoration: underline; margin-right: 10px;">Edit</a>`;
                            const deleteLink = `<a href="#" style="color: #dc3545; text-decoration: underline;" onclick="deleteItem('${options.data.deleteUrl}', '{{ csrf_token() }}')">Delete</a>`;
                            $(container).append( editLink + deleteLink);
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
                noDataText: 'No yearly days truncations found.'
            });
        });
    </script>
    @endpush
</div>
@endsection
