<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Phenix HR Management') }}</title>
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
                <svg viewBox="0 0 62 65" fill="none" xmlns="http://www.w3.org/2000/svg" style="height: 48px; width: auto; background: #f8f9fa; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07);">
                    <path d="M61.8548 14.6253C61.8778 14.7102 61.8895 14.7978 61.8897 14.8858V28.5615C61.8898 28.737 61.8434 28.9095 61.7554 29.0614C61.6675 29.2132 61.5409 29.3392 61.3887 29.4265L49.9104 36.0351V49.1337C49.9104 49.4902 49.7209 49.8192 49.4118 49.9987L25.4519 63.7916C25.3971 63.8227 25.3372 63.8427 25.2774 63.8639C25.255 63.8714 25.2338 63.8851 25.2101 63.8913C25.0426 63.9354 24.8666 63.9354 24.6991 63.8913C24.6716 63.8838 24.6467 63.8689 24.6205 63.8589C24.5657 63.8389 24.5084 63.8215 24.456 63.7916L0.501061 49.9987C0.348882 49.9113 0.222437 49.7853 0.134469 49.6334C0.0465019 49.4816 0.000120578 49.3092 0 49.1337L0 8.10652C0 8.01678 0.0124642 7.92953 0.0348998 7.84477C0.0423783 7.8161 0.0598282 7.78993 0.0697995 7.76126C0.0884958 7.70891 0.105946 7.65531 0.133367 7.6067C0.152063 7.5743 0.179485 7.54812 0.20192 7.51821C0.230588 7.47832 0.256763 7.43719 0.290416 7.40229C0.319084 7.37362 0.356476 7.35243 0.388883 7.32751C0.425029 7.29759 0.457436 7.26518 0.498568 7.2415L12.4779 0.345059C12.6296 0.257786 12.8015 0.211853 12.9765 0.211853C13.1515 0.211853 13.3234 0.257786 13.475 0.345059L25.4531 7.2415H25.4556C25.4955 7.26643 25.5292 7.29759 25.5653 7.32626C25.5977 7.35119 25.6339 7.37362 25.6625 7.40104C25.6974 7.43719 25.7224 7.47832 25.7523 7.51821C25.7735 7.54812 25.8021 7.5743 25.8196 7.6067C25.8483 7.65656 25.8645 7.70891 25.8844 7.76126C25.8944 7.78993 25.9118 7.8161 25.9193 7.84602C25.9423 7.93096 25.954 8.01853 25.9542 8.10652V33.7317L35.9355 27.9844V14.8846C35.9355 14.7973 35.948 14.7088 35.9704 14.6253C35.9792 14.5954 35.9954 14.5692 36.0053 14.5405C36.0253 14.4882 36.0427 14.4346 36.0702 14.386C36.0888 14.3536 36.1163 14.3274 36.1375 14.2975C36.1674 14.2576 36.1923 14.2165 36.2272 14.1816C36.2559 14.1529 36.292 14.1317 36.3244 14.1068C36.3618 14.0769 36.3942 14.0445 36.4341 14.0208L48.4147 7.12434C48.5663 7.03694 48.7383 6.99094 48.9133 6.99094C49.0883 6.99094 49.2602 7.03694 49.4118 7.12434L61.3899 14.0208C61.4323 14.0457 61.4647 14.0769 61.5021 14.1055C61.5333 14.1305 61.5694 14.1529 61.5981 14.1803C61.633 14.2165 61.6579 14.2576 61.6878 14.2975C61.7103 14.3274 61.7377 14.3536 61.7551 14.386C61.7838 14.4346 61.8 14.4882 61.8199 14.5405C61.8312 14.5692 61.8474 14.5954 61.8548 14.6253ZM59.893 27.9844V16.6121L55.7013 19.0252L49.9104 22.3593V33.7317L59.8942 27.9844H59.893ZM47.9149 48.5566V37.1768L42.2187 40.4299L25.953 49.7133V61.2003L47.9149 48.5566ZM1.99677 9.83281V48.5566L23.9562 61.199V49.7145L12.4841 43.2219L12.4804 43.2194L12.4754 43.2169C12.4368 43.1945 12.4044 43.1621 12.3682 43.1347C12.3371 43.1097 12.3009 43.0898 12.2735 43.0624L12.271 43.0586C12.2386 43.0275 12.2162 42.9888 12.1887 42.9539C12.1638 42.9203 12.1339 42.8916 12.114 42.8567L12.1127 42.853C12.0903 42.8156 12.0766 42.7707 12.0604 42.7283C12.0442 42.6909 12.023 42.656 12.013 42.6161C12.0005 42.5688 11.998 42.5177 11.9931 42.4691C11.9881 42.4317 11.9781 42.3943 11.9781 42.3569V15.5801L6.18848 12.2446L1.99677 9.83281ZM12.9777 2.36177L2.99764 8.10652L12.9752 13.8513L22.9541 8.10527L12.9752 2.36177H12.9777ZM18.1678 38.2138L23.9574 34.8809V9.83281L19.7657 12.2459L13.9749 15.5801V40.6281L18.1678 38.2138ZM48.9133 9.14105L38.9344 14.8858L48.9133 20.6305L58.8909 14.8846L48.9133 9.14105ZM47.9149 22.3593L42.124 19.0252L37.9323 16.6121V27.9844L43.7219 31.3174L47.9149 33.7317V22.3593ZM24.9533 47.987L39.59 39.631L46.9065 35.4555L36.9352 29.7145L25.4544 36.3242L14.9907 42.3482L24.9533 47.987Z" fill="#FF2D20"/>
            </svg>
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
                        <a class="nav-link" href="{{ route('employee_times.index') }}"><i class="bi bi-clock-history"></i> <span>Employee Times</span></a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="{{ route('yearly-vacations.index') }}"><i class="bi bi-calendar3"></i> <span>Yearly Vacations</span></a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="{{ route('sick-leaves.index') }}"><i class="bi bi-thermometer-half"></i> <span>Sick Leaves</span></a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="{{ route('salary.index') }}"><i class="bi bi-cash"></i> <span>Salary</span></a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="{{ route('position-improvements.index') }}"><i class="bi bi-bar-chart"></i> <span>Position Improvements</span></a>
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
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
