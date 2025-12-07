<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول الإدارة - منصة الرياضة للجميع</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: "Cairo", sans-serif;
            background: linear-gradient(135deg, #6a0000, #b60000);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
        }
        .login-box {
            width: 95%;
            max-width: 450px;
            background: #ffffff;
            border-radius: 20px;
            padding: 35px 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }
        .header-card {
            background: #ffe2e2;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            margin-bottom: 25px;
        }
        .header-card img {
            height: 60px;
        }
        h3 {
            font-weight: 800;
            color: #7a0000;
            text-align: center;
            margin-bottom: 15px;
        }
        input.form-control {
            height: 48px;
            border-radius: 12px;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            border: none;
            background: #7a0000;
            color: #fff;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: #530000;
        }
        .alert {
            font-size: .95rem;
        }
    </style>
</head>

<body>

<div class="login-box">

    <!-- Header -->
    <div class="header-card">
        <img src="{{ asset('images/djs-logo.png') }}" alt="Logo">
        <p class="fw-bold mt-2">لوحة تحكم الإدارة</p>
    </div>

    <h3>تسجيل الدخول</h3>

    <!-- Login Form -->
    <form method="POST" action="{{ route('admin.login.post') }}">
        @csrf

        <label>البريد الإلكتروني</label>
        <input type="email" name="email" class="form-control" required>

        <label class="mt-3">كلمة المرور</label>
        <input type="password" name="password" class="form-control" required>

        <!-- Checkbox Anti-Bot -->
        <div class="mt-3 mb-2">
            <input type="checkbox" required>
            <label>أؤكد أنني لست روبوت 🤖</label>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger py-2">
            {{ $errors->first() }}
        </div>
        @endif

        <button type="submit" class="btn-login">دخول</button>

    </form>

</div>

</body>
</html>
