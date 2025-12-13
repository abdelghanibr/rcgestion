@extends('layouts.app')

@section('content')
<style>
    body { font-family: "Cairo", sans-serif !important; }
    .dash-box { background: #ffffff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .dash-card { border-radius: 14px; padding: 20px; background: #f8fdf9;
                 border: 1px solid #d6f5e1; text-align: center; transition:.25s; }
    .dash-card:hover { transform: translateY(-4px); box-shadow:0 4px 14px rgba(0,0,0,0.1); }
    .btn-main { background:#1b5e20!important; color:#fff; border-radius:10px;
                padding:8px 18px; font-weight:700; }
    .stats-box { background:#fff; border-radius:8px; padding:8px 10px;
                 border:1px solid #ececec; font-size:13px; margin-top:10px; }
    .stats-box a { text-decoration:none; color:#000; font-weight:bold; }
    .stats-box:hover { background:#e0ffe5; cursor:pointer; }
</style>

<div class="container py-4" style="direction: rtl; text-align:right">

    <div class="dash-box mb-4" style="background:#0A7355; color:white;">
        <h3 class="text-center mb-2">🏢 مرحباً {{ Auth::user()->name }}</h3>
        <p class="text-center">
            إدارة موظفي الشركة والحجوزات الرياضية بسهولة
        </p>
    </div>

    <div class="row g-3">

        <!-- 📌 نفس ستايل النادي 👌 -->
        <div class="col-md-4">
            <div class="dash-card">
                <h5>👥 موظفو المؤسسة</h5>
                <p class="text-muted">إدارة المستخدمين التابعين للشركة</p>

              <a href="{{ route('entreprise.persons.index','لاعب') }}" class="stats-box text-decoration-none">
        <strong> اللاعبين:</strong> {{ $playersCount }}
    </a>

    <a href="{{ route('entreprise.persons.index','مدرب') }}" class="stats-box text-decoration-none">
        <strong> المدربين:</strong> {{ $coachsCount }}
    </a>

    <a href="{{ route('entreprise.persons.index','مسير') }}" class="stats-box text-decoration-none">
        <strong> المسيرين:</strong> {{ $managersCount }}
    </a>

    <a href="{{ route('profile.new') }}" class="btn btn-main btn-sm mt-2">
    إدارة اللاعبين
    </a>
           
    
            </div>
        </div>

        <!-- 🔸 نفس البطاقات الأخرى بدون تغيير -->
           <div class="col-md-4">
            <div class="dash-card">
                <h5>📅 النشاطات</h5>
                <p class="text-muted">المشاركة في مختلف النشاطات</p>
                <a href="{{ route('activities.index') }}" class="btn btn-main btn-sm">أستكشف النشاطات المتاحة</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="dash-card">
                <h5>⚙️ إعدادات الشركة</h5>
                <p class="text-muted">تحديث بيانات المؤسسة</p>
                <a href="#" class="btn btn-main btn-sm">تعديل المعلومات</a>
            </div>
        </div>
          <div class="col-md-4">
            <div class="dash-card">
                <h5>🎟️ حجوزاتي</h5>
                <p class="text-muted">عرض وتتبع حجوزاتك</p>
                <a href="{{ route('reservation.my-reservations') }}" class="btn btn-main btn-sm">عرض الحجوزات</a>
            </div>
        </div>

    </div>
@if($dossier)

    @php
        $attachments = json_decode($dossier->attachments ?? '[]', true);
        $hasFiles = is_array($attachments) && count($attachments) > 0;
    @endphp

    {{-- 🟡 حالة انتظار رفع الوثائق --}}
    @if(!$hasFiles)
        <div class="alert alert-info status-box">
            ⚠ ملفك غير مكتمل!
            <br>يرجى رفع الوثائق المطلوبة لإكمال معالجة الطلب.
            <br>
            <a href="{{ route('profile.step', 4) }}" class="btn btn-primary btn-sm mt-2">
                📤 استكمال رفع الوثائق
            </a>
        </div>

    {{-- 🟢 حالة القبول --}}
    @elseif($dossier->etat == 'approved')
        <div class="alert alert-success status-box">
            ✔ تم قبول ملفك! 🎉 يمكنك الآن الاستفادة من الخدمات
        </div>

    {{-- 🔴 حالة الرفض --}}
    @elseif($dossier->etat == 'rejected')
        <div class="alert alert-danger status-box">
            ❌ تم رفض ملفك. يرجى تعديل الوثائق وإعادة الرفع.
            <br>
            <a href="{{ route('profile.step', 4) }}" class="btn btn-light btn-sm mt-2">
                ✏️ إعادة رفع الوثائق
            </a>
        </div>

    {{-- 🕒 حالة قيد الدراسة --}}
    @else
        <div class="alert alert-warning status-box">
            ⏳ ملفك قيد الدراسة حالياً 🔔
        </div>
    @endif

@else
    {{-- لا يوجد دوسيي بعد --}}
    <div class="alert alert-info status-box">
        ⚠ لم تقم بإرسال ملفك بعد!
        <br>
        <a href="{{ route('profile.step', 1) }}" class="btn btn-primary btn-sm mt-2">
            🚀 أكمل البيانات الآن
        </a>
    </div>
@endif

</div>
@endsection
