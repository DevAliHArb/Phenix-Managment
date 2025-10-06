<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Phenix HR Management') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('FavIcon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Official DevExtreme CSS -->
    <link rel="stylesheet" href="https://cdn3.devexpress.com/jslib/23.2.6/css/dx.light.css">
    @yield('styles')
</head>
<body>
    <style>
        .layout-flex {
            display: flex;
            min-height: 100vh;
        }
        #sidebarMenu {
            width: 220px;
            min-width: 60px;
            max-width: 220px;
            transition: width 0.3s;
            box-shadow: 2px 0 8px rgba(0,0,0,0.05);
            z-index: 1040;
        }
        #sidebarMenu.collapsed {
            width: 60px !important;
        }
        #sidebarMenu .nav-link {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        #sidebarMenu .sidebar-toggle {
            position: absolute;
            top: 10px;
            right: -25px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        #sidebarMenu.collapsed .nav-link span {
            display: none;
        }
        #sidebarMenu .nav-link i {
            margin-right: 8px;
        }
        #mainContent {
            flex: 1 1 0%;
            transition: width 0.3s;
            min-width: 0;
            padding: 0 3% !important; 
            height: 100vh;
            overflow: auto;
        }
    </style>
    <div class="layout-flex">
        <nav id="sidebarMenu" class="bg-light sidebar py-4 position-relative">
            <a href="/" style="display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                <img id="sidebarLogo" src="{{ asset('ColoredLogo1.png') }}" alt="Phenix HR" style="height: 48px; width: auto; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07);">

        </a>
            <div class="sidebar-toggle" id="sidebarToggle" title="Toggle sidebar">
                <i class="bi bi-list"></i>
            </div>
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="{{ route('employees.index') }}"><i class="bi bi-people"></i> <span>Employees</span></a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="{{ route('employee_times.index') }}"><i class="bi bi-clock-history"></i> <span> Punch Time</span></a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="{{ route('employee-vacations.index') }}"><i class="bi bi-person-check"></i> <span>Transaction Days</span></a>
                    </li>
                    {{-- <li class="nav-item mb-2">
                        <a class="nav-link" href="{{ route('yearly-vacations.index') }}"><i class="bi bi-calendar3"></i> <span>Yearly Days Truncations</span></a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="{{ route('sick-leaves.index') }}"><i class="bi bi-thermometer-half"></i> <span>Sick Leaves</span></a>
                    </li> --}}
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="{{ route('position-improvements.index') }}"><i class="bi bi-bar-chart"></i> <span>Job Progression </span></a>
                    </li>
                    {{-- <li class="nav-item mb-2">
                        <a class="nav-link" href="{{ route('salary.index') }}"><i class="bi bi-cash"></i> <span>Position Salary</span></a>
                    </li> --}}
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="{{ route('work-schedule.edit') }}"><i class="bi bi-calendar-week"></i> <span>Work Schedule</span></a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="{{ route('vacation-dates.index') }}"><i class="bi bi-calendar-event"></i> <span>Vacation Dates</span></a>
                    </li>
                </ul>
            </div>
        </nav>
        <main id="mainContent" class="px-md-4">
            <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 mt-2 rounded shadow-sm">
                <div class="container-fluid">
                    <a class="navbar-brand" href="/">Phenix HR Management</a>
                    <div class="collapse navbar-collapse">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item"><a class="nav-link" href="{{ route('employees.index') }}">Employees</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
            @yield('content')
        </main>
    </div>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Official DevExtreme JS -->
    <script src="https://cdn3.devexpress.com/jslib/23.2.6/js/dx.all.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(function() {
            $('#sidebarToggle').on('click', function() {
                $('#sidebarMenu').toggleClass('collapsed');
                
                // Switch logo based on sidebar state
                const logo = $('#sidebarLogo');
                if ($('#sidebarMenu').hasClass('collapsed')) {
                    // Sidebar is collapsed, use small logo
                    logo.attr('src', '{{ asset("FavIcon.svg") }}');
                } else {
                    // Sidebar is open, use colored logo
                    logo.attr('src', '{{ asset("ColoredLogo1.png") }}');
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
