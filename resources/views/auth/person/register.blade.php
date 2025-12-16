<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل فرد جديد</title>
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
        .form-error { color:#b71c1c; font-size:.9rem; margin-top:4px;}
        /* ===============================
   PRIVACY BLUE ZONE
================================ */
.privacy-zone{
    background: linear-gradient(135deg, #0a4f88, #2563eb);
    border-radius:18px;
    padding:18px 20px;
    color:#ffffff;
    box-shadow:0 12px 30px rgba(0,0,0,.15);
}

.privacy-header{
    display:flex;
    align-items:center;
    gap:10px;
    font-weight:900;
    font-size:15px;
    margin-bottom:10px;
}

.privacy-header i{
    font-size:20px;
    color:#e0f2fe;
}

.privacy-content{
    background:rgba(255,255,255,0.12);
    border-radius:14px;
    padding:14px 16px;
    font-size:14px;
    line-height:1.8;
}

.privacy-content p{
    margin-bottom:10px;
    color:#f0f9ff;
}

.privacy-zone .form-check-input{
    border:2px solid #e0f2fe;
}

.privacy-zone .form-check-input:checked{
    background-color:#22c55e;
    border-color:#22c55e;
}

.privacy-zone .form-check-label{
    color:#ffffff;
}

.privacy-link{
    color:#a7f3d0;
    font-weight:900;
    text-decoration:underline;
}

.privacy-link:hover{
    color:#ffffff;
}

    </style>
</head>

<body>

<div class="register-box">

    <div class="text-center mb-3">
        <img src="{{ asset('images/djs-logo.png') }}" width="90">
        <h3 class="fw-bold mt-2">تسجيل فرد جديد</h3>
    </div>

    <form method="POST" action="{{ route('person.register.post') }}">
        @csrf

        <div class="row g-4">

            <div class="col-md-6">
                <label class="form-label">الإسم الكامل</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" required>
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
                <label>
                    <input type="checkbox" required> أؤكد أنني لست روبوت 🤖
                </label>
            </div>
<div class="privacy-zone mt-4">

    <div class="privacy-header">
        <i class="fa-solid fa-shield-halved"></i>
        <span>حماية المعطيات الشخصية</span>
    </div>

    <div class="privacy-content">
        <p>
            أصرّح بموافقتي الصريحة على جمع ومعالجة معطياتي الشخصية
            طبقًا لأحكام <strong>القانون الجزائري رقم 18-07</strong>
            المتعلق بحماية الأشخاص الطبيعيين في مجال معالجة المعطيات
            ذات الطابع الشخصي.
        </p>

        <div class="form-check mt-3">
            <input class="form-check-input @error('privacy_policy') is-invalid @enderror"
                   type="checkbox"
                   name="privacy_policy"
                   id="privacy_policy"
                   value="1"
                   required>

            <label class="form-check-label fw-bold" for="privacy_policy">
                أوافق على
                <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal"
                   class="privacy-link">
                    سياسة حماية المعطيات الشخصية
                </a>
            </label>

            @error('privacy_policy')
                <div class="invalid-feedback d-block mt-1">
                    يجب الموافقة على سياسة حماية المعطيات الشخصية
                </div>
            @enderror
        </div>
    </div>

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
        لديك حساب بالفعل؟ <a href="{{ route('person.login') }}">تسجيل الدخول</a>
    </p>

</div>

</body>
</html>
