<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل نادي رياضي جديد</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Cairo', sans-serif; background:#e8f5e9; padding:30px;}
        .register-box {
            background:white; width:95%; max-width:900px; margin:auto;
            padding:40px; border-radius:18px;
            box-shadow:0 8px 25px rgba(0,0,0,0.12);
        }
    </style>
</head>

<body>

<div class="register-box">

    <div class="text-center mb-3">
        <img src="{{ asset('images/djs-logo.png') }}" width="90">
        <h3 class="fw-bold mt-2">تسجيل نادي رياضي</h3>
    </div>

    <form method="POST" action="{{ route('club.register.post') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-4">

            <div class="col-md-6">
                <label class="form-label">اسم النادي</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">رقم الاعتماد</label>
                <input type="text" name="numero_agrement" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">تاريخ انتهاء الاعتماد</label>
                <input type="date" name="date_expiration" class="form-control" required>
            </div>

            <div class="col-md-12">
                <label class="form-label">نسخة من وثيقة الاعتماد 📎</label>
               <input type="file" name="attachments[]" class="form-control" multiple>
            </div>

            <div class="col-md-6">
                <label class="form-label">كلمة المرور</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <!-- Schema Protection -->
            <div class="col-12">
                <label><input type="checkbox" required> أؤكد أنني لست روبوت 🤖</label>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-success w-100">إنشاء حساب</button>
            </div>

        </div>
@if ($errors->any())
    <div class="alert alert-danger mt-2">
        {{ $errors->first() }}
    </div>
@endif

    </form>

    <p class="text-center mt-3">
        لديك حساب؟ <a href="{{ route('club.login') }}">تسجيل الدخول</a>
    </p>

</div>

</body>
</html>
