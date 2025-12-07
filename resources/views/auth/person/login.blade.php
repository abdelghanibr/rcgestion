<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول فرد - منصة الرياضة للجميع</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: "Cairo", sans-serif;
            background: linear-gradient(135deg, #00416A, #0D775C);
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
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .header-card {
            background: linear-gradient(135deg, #e0f2e9, #d8f3df);
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
            color: #1b5e20;
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
            background: #1b5e20;
            color: #fff;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: #0d3d14;
        }
        .footer-text {
            text-align: center;
            margin-top: 18px;
            font-size: 0.95rem;
        }
        .footer-text a {
            color: #0d47a1;
            font-weight: 700;
        }
    </style>
</head>

<body>

<div class="login-box">

    <!-- Header -->
    <div class="header-card">
        <img src="{{ asset('images/djs-logo.png') }}" alt="Logo">
        <p class="fw-bold mt-2">منصة الرياضة للجميع - ولاية ميلة</p>
    </div>

    <h3>تسجيل دخول فرد</h3>

    <!-- Login Form -->
    <form method="POST" action="{{ route('person.login.post') }}">
        @csrf

        <label>البريد الإلكتروني</label>
        <input type="email" name="email" class="form-control"
               placeholder="example@mail.com" required>

        <label class="mt-3">كلمة المرور</label>
        <input type="password" name="password" class="form-control"
               placeholder="••••••" required>

        <!-- Schema Verification -->
        <div class="mt-3 mb-2">
            <input type="checkbox" name="schema" required>
            <label>أؤكد أنني لست روبوت 🤖</label>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger py-2">
            {{ $errors->first() }}
        </div>
        @endif

        <button type="submit" class="btn-login">دخول</button>

    </form>

<div class="footer-text text-center mt-3">
    لا تملك حسابًا؟
    <a href="{{ route('person.register') }}" class="fw-bold">سجّل الآن</a>
</div>

<div class="text-center mt-2">
    <a href="{{ route('password.request') }}" class="fw-bold" style="color:#0d47a1;">
        نسيت كلمة المرور؟
    </a>
</div>


</body>
</html>
