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
    <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 18px; gap: 12px;">
            <form id="yearFilterForm" style="display: flex; align-items: center; gap: 8px;">
                <label for="yearFilter" style="margin-bottom:0;">Year:</label>
                <select id="yearFilter" name="year" class="form-select" style="width: auto;">
                    @php
                        $currentYear = date('Y');
                        $years = collect($vacations)->pluck('date')->map(fn($d) => date('Y', strtotime($d)))->unique()->sort()->values();
                        if (!$years->contains($currentYear)) $years->push($currentYear);
                        $years = $years->sortDesc();
                    @endphp
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('vacation-dates.create') }}" class="btn btn-primary">Add Vacation Date</a>
            <button type="button" class="btn btn-success" id="addYearlyVacationBtn">Add Yearly Vacation</button>
        <!-- Yearly Vacation Modal -->
        <div class="modal fade" id="yearlyVacationModal" tabindex="-1" aria-labelledby="yearlyVacationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="yearlyVacationModalLabel">Add Yearly Vacation Dates</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="yearlyVacationForm">
                        <div class="modal-body">
                            <label for="yearInput">Year:</label>
                            <input type="number" id="yearInput" name="year" class="form-control" min="2000" max="2100" value="{{ date('Y') }}" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
    </div>
    <div id="vacationDatesGrid"></div>
    @push('scripts')
        <script>
            document.getElementById('addYearlyVacationBtn').addEventListener('click', function() {
                var modal = new bootstrap.Modal(document.getElementById('yearlyVacationModal'));
                modal.show();
            });

            document.getElementById('yearlyVacationForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var year = document.getElementById('yearInput').value;
                fetch(`{{ route('vacation-dates.addYearly') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ year })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to add yearly vacation dates.');
                    }
                })
                .catch(() => alert('Failed to add yearly vacation dates.'));
            });
        </script>
    <script>
        const vacationDatesData = [
            @foreach($vacations as $vacation)
            {
                date: `{{ $vacation->date }}`,
                year: `{{ date('Y', strtotime($vacation->date)) }}`,
                name: `{{ $vacation->name }}`,
                editUrl: `{{ route('vacation-dates.edit', $vacation) }}`,
                deleteUrl: `{{ route('vacation-dates.destroy', $vacation) }}`
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

        function getFilteredData(year) {
            return vacationDatesData.filter(item => item.year == year);
        }

        $(function() {
            let selectedYear = $('#yearFilter').val();
            $("#vacationDatesGrid").dxDataGrid({
                dataSource: getFilteredData(selectedYear),
                columns: [
                    { dataField: "date", caption: "Date", allowFiltering: true, headerFilter: { allowSearch: true } },
                    { dataField: "name", caption: "Name", allowFiltering: true, headerFilter: { allowSearch: true } },
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

            $('#yearFilter').on('change', function() {
                selectedYear = $(this).val();
                $("#vacationDatesGrid").dxDataGrid('instance').option('dataSource', getFilteredData(selectedYear));
            });
        });
    </script>
    @endpush
</div>
@endsection
