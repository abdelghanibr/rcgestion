<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء حساب جديد</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: #e8f5e9;
            padding: 35px 0;
        }
        .register-box {
            background: white;
            width: 90%;
            max-width: 900px;
            margin: auto;
            padding: 40px 35px;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .form-error {
            color: #b71c1c;
            font-size: 0.9rem;
            margin-top: 4px;
        }
        .is-invalid {
            border: 1px solid red !important;
        }
    </style>

</head>

<body>

<div class="register-box">

    <div class="text-center mb-3">
        <img src="{{ asset('images/djs-logo.png') }}" width="90" class="mb-2">
        <h2>وزارة الشباب</h2>
        <h4>منصة النشاطات الشبانية</h4>
    </div>

    <form method="POST" action="{{ route('register.post') }}">
        @csrf

        <div class="row g-4">

            <!-- الاسم -->
            <div class="col-md-6">
                <label class="form-label">الاسم</label>
                <input type="text" name="firstname"
                       class="form-control @error('firstname') is-invalid @enderror"
                       value="{{ old('firstname') }}" required>
                @error('firstname')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- اللقب -->
            <div class="col-md-6">
                <label class="form-label">اللقب</label>
                <input type="text" name="lastname"
                       class="form-control @error('lastname') is-invalid @enderror"
                       value="{{ old('lastname') }}" required>
                @error('lastname')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- البريد -->
            <div class="col-md-6">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required>
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- اسم المستخدم -->
            <div class="col-md-6">
                <label class="form-label">اسم المستخدم</label>
                <input type="text" name="username"
                       class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username') }}" required>
                @error('username')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- كلمة المرور -->
            <div class="col-md-6">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       required>
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- تأكيد كلمة المرور -->
            <div class="col-md-6">
                <label class="form-label">تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="col-12">
                <label>
                    <input type="checkbox" required> أوافق على الشروط والأحكام
                </label>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-success w-100">تسجيل</button>
            </div>

        </div>

    </form>

</div>

</body>
</html>
