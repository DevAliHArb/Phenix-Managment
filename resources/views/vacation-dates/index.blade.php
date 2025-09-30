@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div style="width:100%">
    <div class="headerContainer" >
        <h1>Vacation Dates</h1>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div style="display: flex; justify-content: flex-end; margin-bottom: 18px;">
            <a href="{{ route('vacation-dates.create') }}" class="btn btn-primary">Add Vacation Date</a>
        </div>
    </div>
    <div id="vacationDatesGrid"></div>
    @push('scripts')
    <script>
        const vacationDatesData = [
            @foreach($vacations as $vacation)
            {
                date: `{{ $vacation->date }}`,
                name: `{{ $vacation->name }}`,
                editUrl: `{{ route('vacation-dates.edit', $vacation) }}`,
                deleteUrl: `{{ route('vacation-dates.destroy', $vacation) }}`
            },
            @endforeach
        ];

        $(function() {
            $("#vacationDatesGrid").dxDataGrid({
                dataSource: vacationDatesData,
                columns: [
                    { dataField: "date", caption: "Date", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "name", caption: "Name", allowFiltering: true, headerFilter: { allowSearch: true } },
                    {
                        caption: "Actions",
                        cellTemplate: function(container, options) {
                            const editLink = `<a href="${options.data.editUrl}" style="color: #0d6efd; text-decoration: underline; margin-right: 10px;">Edit</a>`;
                            const deleteLink = `<a href="#" style="color: #dc3545; text-decoration: underline;" onclick="event.preventDefault(); if(confirm('Delete this vacation date?')) { var f = document.createElement('form'); f.style.display='none'; f.method='POST'; f.action='${options.data.deleteUrl}'; f.innerHTML='<input type=\'hidden\' name=\'_token\' value=\'{{ csrf_token() }}\'><input type=\'hidden\' name=\'_method\' value=\'DELETE\'>'; document.body.appendChild(f); f.submit(); }">Delete</a>`;
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
