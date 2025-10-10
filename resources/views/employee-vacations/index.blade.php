@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div style="width:100%">
    <div class="headerContainer" >
        <h1>Transaction Days</h1>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div style="display: flex; justify-content: flex-end; margin-bottom: 18px;">
            <a href="{{ route('employee-vacations.create') }}?return_url={{ urlencode(request()->fullUrl()) }}" class="btn btn-primary">Add Vacation</a>
        </div>
    </div>
    <div id="employeeVacationsGrid"></div>
    @push('scripts')
    <script>
        const employeeVacationsData = [
            @foreach($items as $item)
            {
                id: {{ $item->id }},
                employee: `{{ optional($item->employee)->first_name }} {{ optional($item->employee)->mid_name }} {{ optional($item->employee)->last_name }}`,
                date: `{{ $item->date }}`,
                reason: `{{ $item->reason }}`,
                type: `{{ $item->type->name ?? '' }}`,
                attachment: `{!! $item->attachment ? '<a href="' . asset('attachments/' . $item->attachment) . '" target="_blank">View</a>' : '' !!}`,
                editUrl: `{{ route('employee-vacations.edit', $item->id) }}`,
                deleteUrl: `{{ route('employee-vacations.destroy', $item->id) }}`
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
            $("#employeeVacationsGrid").dxDataGrid({
                dataSource: employeeVacationsData,
                columns: [
                    { dataField: "id", caption: "ID", width: 60, allowFiltering: true, headerFilter: { allowSearch: true }, visible: false },
                    { dataField: "employee", caption: "Employee", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "date", caption: "Date", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "reason", caption: "Reason", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "type", caption: "Type", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "attachment", caption: "Attachment", allowFiltering: false, encodeHtml: false, cellTemplate: function(container, options) { $(container).html(options.data.attachment); } },
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
                },
                filterRow: { visible: true },
                headerFilter: { visible: true },
                searchPanel: { visible: true, width: 240, placeholder: 'Search...' },
                hoverStateEnabled: true,
                rowAlternationEnabled: true,
                columnAutoWidth: true,
                wordWrapEnabled: true,
            });
        });
    </script>
    @endpush
</div>
@endsection
