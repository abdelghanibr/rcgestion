<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'OPOW Mila') }}</title>

    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <style>
        body {
            background: #f6f8fa;
            font-family: "Cairo", sans-serif !important;
        }
        .navbar-modern {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 16px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
        }
        .avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #0284c7;
        }
    </style>

    @stack('styles')
    @stack('css')
</head>

<body>

{{-- ================= NAVBAR ================= --}}
@if(Auth::check())
<nav class="navbar navbar-expand-lg navbar-modern">
    <div class="container-fluid">

        <a class="navbar-brand fw-bold" href="{{ url('/') }}">
            <img src="{{ asset('images/djs-logo.png') }}" style="width:42px; margin-left:8px;">
            OPOW Mila
        </a>

        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-center">

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                        <img src="{{ asset('images/default-user.png') }}" class="avatar-sm me-2">
                        <span>{{ Auth::user()->name ?? '' }}</span>
                    </a>

                    <ul class="dropdown-menu">

                        <li><a class="dropdown-item" href="#"><i class="fa-solid fa-user ms-1"></i> الملف الشخصي</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fa-solid fa-gauge ms-1"></i> لوحة التحكم</a></li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                          @php
    $logoutRoute = match(Auth::user()->type) {
        'admin' => 'admin.logout',
        'club' => 'club.logout',
        'company' => 'entreprise.logout',
        'person' => 'person.logout',
        default => 'logout', // fallback
    };
@endphp

<form method="POST" action="{{ route($logoutRoute) }}">
    @csrf
    <button class="dropdown-item text-danger">
        <i class="fa-solid fa-right-from-bracket ms-1"></i>
        تسجيل الخروج
    </button>
</form>

                        </li>

                    </ul>
                </li>

            </ul>
        </div>

    </div>
</nav>

@else
{{-- Navbar للزائر --}}
<nav class="navbar navbar-expand-lg navbar-modern">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}">
            <img src="{{ asset('images/djs-logo.png') }}" style="width:42px; margin-left:8px;">
            OPOW Mila
        </a>

        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link fw-bold" href="{{ url('/') }}">الصفحة الرئيسية</a></li>
            <li class="nav-item"><a class="nav-link text-primary fw-bold" href="{{ route('person.login') }}">تسجيل الدخول</a></li>
        </ul>
    </div>
</nav>
@endif
{{-- ========================================= --}}

<main class="py-4">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
@stack('js')
</body>
</html>
