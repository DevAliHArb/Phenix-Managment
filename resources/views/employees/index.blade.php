@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('resources/css/employees.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="headerContainer" >
    <h1>Employees</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div style="display: flex; justify-content: flex-end; margin-bottom: 18px;">
        <a href="{{ route('employees.create') }}" class="btn btn-primary">Add Employee</a>
    </div>

    </div>
    <div style="overflow-x:auto;">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Image</th>
                    <th>Position</th>
                    <th>Birthdate</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Employment Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                <tr class="employee-row" style="cursor:pointer;" data-employee='@json($employee)'>
                    <td>{{ $employee->id }}</td>
                    <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                    <td><img src="{{ $employee->image }}" alt="Image" style="max-width:50px;max-height:50px;"></td>
                    <td>{{ optional($employee->position)->name }}</td>
                    <td>{{ $employee->date_of_birth }}</td>
                    <td>{{ $employee->start_date }}</td>
                    <td>{{ $employee->end_date }}</td>
                    <td>{{ optional($employee->EmployeeType)->name }}</td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-info btn-sm" title="View"><span>&#128065;</span></a>
                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning btn-sm" title="Edit"><span>&#9998;</span></a>
                        <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')" title="Delete"><span>&#128465;</span></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; color:#888;">No employees found.</td>
                </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    <!-- Employee Details and Position Improvements Cards -->
    <div id="employee-details-section" style="display:none; margin-top:32px;">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">Employee Details</div>
                    <div class="card-body" id="employee-details-card">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
            <div class="col-md-8 mb-3">
                <div class="card">
                    <div class="card-header bg-info text-white">Employee Times</div>
                    <div class="card-body" id="employee-times-card">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.employee-row');
    const detailsSection = document.getElementById('employee-details-section');
    const detailsCard = document.getElementById('employee-details-card');
    const timesCard = document.getElementById('employee-times-card');

    rows.forEach(row => {
        row.addEventListener('click', function() {
            // Remove 'selected' class from all rows
            rows.forEach(r => r.classList.remove('selected'));
            // Add 'selected' class to the clicked row
            this.classList.add('selected');

            const employee = JSON.parse(this.getAttribute('data-employee'));
            // Employee Details  <img src="${employee.image}" alt="Image" class="img-thumbnail mb-2" style="max-width:120px;">
            let html = `<div class="row">
                <div >
                  
                    <h5>${employee.first_name} ${employee.last_name}</h5>
                    <p><strong>Address:</strong> ${employee.address ?? ''}</p>
                    <p><strong>Birthdate:</strong> ${employee.date_of_birth ?? ''}</p>
                    <p><strong>Phone:</strong> ${employee.phone ?? ''}</p>
                    <p><strong>Status:</strong> ${employee.status ?? ''}</p>
                    <p><strong>Position:</strong> ${(employee.position && employee.position.name) ? employee.position.name : ''}</p>
                    <p><strong>Start Date:</strong> ${employee.start_date ?? ''}</p>
                    <p><strong>End Date:</strong> ${employee.end_date ?? ''}</p>
                    <p><strong>Working Hours:</strong> ${employee.working_hours_from ?? ''} - ${employee.working_hours_to ?? ''}</p>
                    <p><strong>Yearly Vacations:</strong> Total: ${employee.yearly_vacations_total ?? 0}, Used: ${employee.yearly_vacations_used ?? 0}, Left: ${employee.yearly_vacations_left ?? 0}</p>
                    <p><strong>Sick Leave Used:</strong> ${employee.sick_leave_used ?? 0}</p>
                    <p><strong>Last Salary:</strong> ${employee.last_salary ?? ''}</p>
                </div>
            </div>`;
            detailsCard.innerHTML = html;

            // Employee Times Table
            let times = employee.employee_times || [];
            if(times.length === 0) {
                timesCard.innerHTML = '<p class="text-muted">No employee times found.</p>';
            } else {
                let table = `<table class=\"table table-sm table-bordered\"><thead><tr><th>Date</th><th>Clock In</th><th>Clock Out</th><th>Total Time</th><th>Off Day</th><th>Reason</th></tr></thead><tbody>`;
                times.forEach(time => {
                    table += `<tr><td>${time.date ?? ''}</td><td>${time.clock_in ?? ''}</td><td>${time.clock_out ?? ''}</td><td>${time.total_time ?? ''}</td><td>${time.off_day ? 'Yes' : 'No'}</td><td>${time.reason ?? ''}</td></tr>`;
                });
                table += '</tbody></table>';
                timesCard.innerHTML = table;
            }

            detailsSection.style.display = '';
            detailsSection.scrollIntoView({behavior: 'smooth'});
        });
    });

        // Select and trigger click on the first row by default
        if (rows.length > 0) {
            rows[0].classList.add('selected');
            rows[0].click();
        }
        rows.forEach(row => {
            row.addEventListener('click', function() {
                // Remove 'selected' class from all rows
                rows.forEach(r => r.classList.remove('selected'));
                // Add 'selected' class to the clicked row
                this.classList.add('selected');

                const employee = JSON.parse(this.getAttribute('data-employee'));
                // Employee Details
                let html = `<div class="row">
                    <div class="col-4 text-center">
                        <img src="${employee.image}" alt="Image" class="img-thumbnail mb-2" style="max-width:120px;">
                    </div>
                    <div class="col-8">
                        <h5>${employee.first_name} ${employee.last_name}</h5>
                        <p><strong>Address:</strong> ${employee.address ?? ''}</p>
                        <p><strong>Birthdate:</strong> ${employee.date_of_birth ?? ''}</p>
                        <p><strong>Phone:</strong> ${employee.phone ?? ''}</p>
                        <p><strong>Status:</strong> ${employee.status ?? ''}</p>
                        <p><strong>Position:</strong> ${(employee.position && employee.position.name) ? employee.position.name : ''}</p>
                        <p><strong>Start Date:</strong> ${employee.start_date ?? ''}</p>
                        <p><strong>End Date:</strong> ${employee.end_date ?? ''}</p>
                        <p><strong>Working Hours:</strong> ${employee.working_hours_from ?? ''} - ${employee.working_hours_to ?? ''}</p>
                        <p><strong>Yearly Vacations:</strong> Total: ${employee.yearly_vacations_total ?? 0}, Used: ${employee.yearly_vacations_used ?? 0}, Left: ${employee.yearly_vacations_left ?? 0}</p>
                        <p><strong>Sick Leave Used:</strong> ${employee.sick_leave_used ?? 0}</p>
                        <p><strong>Last Salary:</strong> ${employee.last_salary ?? ''}</p>
                    </div>
                </div>`;
                detailsCard.innerHTML = html;

                // Position Improvements Table
                let improvements = employee.position_improvements || [];
                if(improvements.length === 0) {
                    improvementsCard.innerHTML = '<p class="text-muted">No position improvements found.</p>';
                } else {
                    let table = `<table class="table table-sm table-bordered"><thead><tr><th>ID</th><th>Position ID</th><th>Start Date</th><th>End Date</th><th>Active</th></tr></thead><tbody>`;
                    improvements.forEach(impr => {
                        table += `<tr><td>${impr.id}</td><td>${impr.position_id}</td><td>${impr.start_date}</td><td>${impr.end_date}</td><td>${impr.is_active ? 'Yes' : 'No'}</td></tr>`;
                    });
                    table += '</tbody></table>';
                    improvementsCard.innerHTML = table;
                }

                detailsSection.style.display = '';
                detailsSection.scrollIntoView({behavior: 'smooth'});
            });
        });
});
</script>
@endpush

@endsection
