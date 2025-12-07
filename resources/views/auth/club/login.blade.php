<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول نادي - منصة الرياضة للجميع</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: "Cairo", sans-serif;
            background: linear-gradient(135deg, #0D4775, #007b55);
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
            background: linear-gradient(135deg, #d4f7e4, #b6eed1);
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            margin-bottom: 25px;
        }
        .header-card img { height: 60px; }
        h3 { font-weight: 800; color: #0A6C44; text-align: center; }
        input.form-control { height: 48px; border-radius: 12px; }
        .btn-login {
            width: 100%; padding: 12px;
            border: none; background: #0A6C44;
            color: #fff; border-radius: 12px;
            font-size: 1.1rem; font-weight: 700;
        }
        .btn-login:hover { background: #06492c; }
        .footer-text { text-align:center; margin-top:18px; }
        .footer-text a { font-weight:700; color:#0057b3; }
    </style>
</head>

<body>

<div class="login-box">

    <div class="header-card">
        <img src="{{ asset('images/djs-logo.png') }}" alt="Logo">
        <p class="fw-bold mt-2">دخول النوادي الرياضية - ولاية ميلة</p>
    </div>

    <h3>تسجيل دخول نادي</h3>

    <form method="POST" action="{{ route('club.login.post') }}">
        @csrf

        <label>البريد الإلكتروني</label>
        <input type="email" name="email" class="form-control" required>

        <label class="mt-3">كلمة المرور</label>
        <input type="password" name="password" class="form-control" required>

        <div class="mt-3 mb-2">
            <input type="checkbox" required> أؤكد أنني لست روبوت 🤖
        </div>

        @if ($errors->any())
        <div class="alert alert-danger py-2">
            {{ $errors->first() }}
        </div>
        @endif

        <button type="submit" class="btn-login">دخول</button>
    </form>



    <div class="footer-text text-center mt-3">
  ليس لديك حساب؟ <a href="{{ route('club.register') }}">سجّل نادي جديد</a>
</div>

<div class="text-center mt-2">
    <a href="{{ route('password.request') }}" class="fw-bold" style="color:#0d47a1;">
        نسيت كلمة المرور؟
    </a>
</div>

</div>

</body>
</html>
