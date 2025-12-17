<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title> ديوان المركب المتعدد الرياضات لولاية ميلة</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/welcome.css') }}" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-main sticky-top">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center gap-2" href="#top">
            <img src="{{ asset('images/djs-logo.png') }}" alt="Logo"
                 style="width:48px; height:48px; object-fit:contain;">
            <div class="d-flex flex-column lh-sm text-start">
                <span class="fw-bold" style="font-size:15px;">وزارة الرياضة - ولاية ميلة</span>
                <span style="font-size:14px;">ديوان المركب المتعدد الرياضات</span>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainMenu">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="#about">حول المنصة</a></li>
                <li class="nav-item"><a class="nav-link" href="#news">المستجدات</a></li>
                <li class="nav-item"><a class="nav-link" href="#events">الفعاليات</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">الاتصال</a></li>
            </ul>
        </div>
    </div>
</nav>


<!-- HERO -->
<section class="hero" id="top">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-md-7">
                <h1 class="hero-title">فضاء رقمي للمنخرطين والرياضة للجميع بولاية ميلة</h1>
                <p class="hero-subtitle mt-3">
                    منصة إلكترونية حديثة لتنظيم الأنشطة الرياضية، متابعة المنخرطين، وحجز المرافق عن بعد.
                </p>
            </div>
            <div class="col-md-5">
                <div class="hero-card text-center">
                    <img src="{{ asset('images/djs-logo.png') }}" class="hero-logo mb-3" alt="Logo">
                    <h5>OP O W Mila</h5>
                    <p class="mb-0">مرافقة النشاطات الرياضية عبر كامل ولاية ميلة.</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- LOGIN / REGISTER BLOCK -->
<div class="container my-5">
    <h2 class="section-title mb-4 text-center">👇 اختر نوع الحساب للدخول أو التسجيل</h2>

    <div class="row g-4 justify-content-center">

        <!-- PERSON -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card-modern text-center">
                <i class="fa-solid fa-user fa-3x text-primary mb-3"></i>
                <h5 class="fw-bold">حساب فردي</h5>
                <p class="text-muted small mb-3">للأشخاص الراغبين في ممارسة الرياضة وحجز الحصص.</p>
                <a class="btn btn-primary w-100 mb-2" href="{{ route('person.login') }}">دخول كفرد</a>
               
            </div>
        </div>

        <!-- CLUB -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card-modern text-center">
                <i class="fa-solid fa-people-group fa-3x text-success mb-3"></i>
                <h5 class="fw-bold">نادي رياضي</h5>
                <p class="text-muted small mb-3">للأندية المعتمدة لإدارة لاعبيها وبرمجة التدريبات.</p>
                <a class="btn btn-success w-100 mb-2" href="{{ route('club.login') }}">دخول نادي</a>
            
            </div>
        </div>

        <!-- COMPANY -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card-modern text-center">
                <i class="fa-solid fa-building fa-3x text-warning mb-3"></i>
                <h5 class="fw-bold">مؤسسة / شركة</h5>
                <p class="text-muted small mb-3">مخصص للمؤسسات الراغبة بحجز المرافق لموظفيها.</p>
                <a class="btn btn-warning text-white w-100 mb-2" href="{{ route('entreprise.login') }}">دخول مؤسسة</a>
                
            </div>
        </div>

        <!-- ADMIN -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card-modern text-center">
                <i class="fa-solid fa-shield-halved fa-3x text-danger mb-3"></i>
                <h5 class="fw-bold">تسجيل دخول الإدارة</h5>
                <p class="text-muted small mb-3">مخصص فقط للمسؤلين  عن النظام وعمال الإدارة.</p> 
                <a class="btn btn-danger w-100" href="{{ route('admin.login') }}">دخول كـ Admin</a>
            </div>
        </div>

    </div>

 <h2 class="section-title" id="news">آخر المستجدات</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card-modern">
                <i class="fa-solid fa-bullhorn"></i>
                <h5>إطلاق منصة تسيير المنخرطين</h5>
                <p class="text-muted">تمكين المنخرطين من متابعة وضعيتهم عن بعد.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-modern">
                <i class="fa-solid fa-dumbbell"></i>
                <h5>دعم المدارس الرياضية</h5>
                <p class="text-muted">متابعة ملفات الجمعيات والنوادي بصفة رقمية.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-modern">
                <i class="fa-solid fa-calendar-days"></i>
                <h5>تقويم الأنشطة</h5>
                <p class="text-muted">برمجة الفعاليات الرياضية على مدار السنة.</p>
            </div>
        </div>
    </div>

    <!-- EVENTS -->
    <h2 class="section-title" id="events">الفعاليات القادمة</h2>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card-modern text-start">
                <h5><i class="fa-solid fa-person-running me-2"></i>ماراطون ولاية ميلة</h5>
                <p class="text-muted">جوان 2025 – مدينة ميلة</p>
                <p class="text-muted">فعالية مفتوحة للرياضيين والمنخرطين.</p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card-modern text-start">
                <h5><i class="fa-solid fa-chalkboard-users me-2"></i>اليوم الدراسي للنوادي</h5>
                <p class="text-muted">سبتمبر 2025 – معهد حيحي ميلة</p>
                <p class="text-muted">تبادل الخبرات والبرمجة الرياضية.</p>
            </div>
        </div>
    </div>

    <!-- CONTACT -->
    <h2 class="section-title" id="contact">معلومات الاتصال</h2>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card-modern text-start">
                <p>
                    <i class="fa-solid fa-location-dot me-2 text-danger"></i>
                    ديوان المركب المتعدد الرياضات – ولاية ميلة
                </p>
                <p><i class="fa-solid fa-phone me-2 text-success"></i>031-00-00-00</p>
                <p><i class="fa-solid fa-envelope me-2 text-primary"></i>contact@opow-mila.dz</p>
                <p><i class="fa-solid fa-building-columns me-2 text-warning"></i>OPOW MILA</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-modern">
                <h5 class="mb-3">نموذج اتصال (تجريبي)</h5>
                <form>
                    <input type="text" class="form-control mb-2" placeholder="الاسم الكامل">
                    <input type="email" class="form-control mb-2" placeholder="البريد الإلكتروني">
                    <textarea class="form-control mb-2" rows="3" placeholder="الرسالة"></textarea>
                    <button type="button" class="btn btn-success w-100" disabled>إرسال</button>
                </form>
            </div>
        </div>
    </div>

</div>

</div>


    <!-- NEWS -->
   

<!-- FOOTER -->
<footer class="footer">
    <div class="container">

        <!-- Social -->
        <div>
            <h6>تابعنا</h6>
            <div class="social">
                <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#"><i class="fa-brands fa-twitter"></i></a>
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
            </div>
        </div>

        <!-- Contact -->
        <div>
            <h6>تواصل معنا</h6>
            <p><i class="fa-solid fa-location-dot"></i> الجزائر – وزارة الشباب</p>
            <p><i class="fa-solid fa-envelope"></i> contact@mjeunesse.gov.dz</p>
        </div>

        <!-- Links -->
        <div>
            <h6>روابط مهمة</h6>
            <p><a href="#">الموقع الرسمي للوزارة</a></p>
            <p><a href="#">منصة مشاركة</a></p>
            <p><a href="#">بوابة الفضاءات الشبانية</a></p>
        </div>

        <!-- Logo -->
        <div>
            <img src="images/logo.png" alt="Logo" style="max-width:110px">
            <p class="mt-3">
                وزارة الشباب تعمل على تمكين الشباب وتعزيز مشاركتهم الفاعلة.
            </p>
        </div>

    </div>

    <div class="footer-bottom">
        © 2025 – جميع الحقوق محفوظة | وزارة الشباب الجزائرية
    </div>
</footer>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/welcome.js') }}"></script>

</body>
</html>
