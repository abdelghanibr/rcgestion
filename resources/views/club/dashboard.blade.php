@extends('layouts.app')

@section('content')
<style>
    body { font-family: "Cairo", sans-serif !important; }
    .dash-box { background: #ffffff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .dash-card { border-radius: 14px; padding: 20px; background: #f8fdf9;
                 border: 1px solid #d6f5e1; text-align: center; transition:.25s; }
    .dash-card:hover { transform: translateY(-4px); box-shadow:0 4px 14px rgba(0,0,0,0.1); }
    .btn-main { background:#0a4f88!important; color:#fff; border-radius:10px;
                padding:8px 18px; font-weight:700; }
    .stats-box { background:#fff; border-radius:8px; padding:8px 10px;
                 border:1px solid #ececec; font-size:13px; margin-top:10px; }
</style>

<div class="container py-4" style="direction: rtl; text-align:right">

    <!-- Header -->
    <div class="dash-box mb-4" style="background:#0a4f88; color:white;">
        <h3 class="text-center mb-2">⚽ أهلاً {{ Auth::user()->name }}</h3>
        <p class="text-center">
            إدارة فريقك الرياضي وتنظيم التدريبات والانخراطات بكل سهولة
        </p>
    </div>

    <!-- Cards Section -->
    <div class="row g-3">

        <!-- 🧑‍🤝‍🧑 لاعبو النادي -->
        <div class="col-md-4">
            <div class="dash-card">
                <h5>🧑‍🤝‍🧑 لاعبو النادي</h5>
                <p class="text-muted">إدارة قوائم اللاعبين وتسجيل المنخرطين</p>

                <!-- 🆕 إحصائيات مُضافة -->
       
    <strong> اللاعبين:</strong> {{ $playersCount }}
</a>


    <strong> المدربين:</strong> {{ $coachsCount }}
</a>


    <strong> المسيرين:</strong> {{ $managersCount }}
</a>


               <a href="{{ route('club.persons.index') }}" class="btn btn-main btn-sm mt-2">
    إدارة اللاعبين
</a>

            </div>
        </div>

        <!-- 📅 التدريبات -->
        <div class="col-md-4">
            <div class="dash-card">
                <h5>📅 النشاطات</h5>
                <p class="text-muted">المشاركة في مختلف النشاطات</p>
                <a href="{{ route('activities.index') }}" class="btn btn-main btn-sm">أستكشف النشاطات المتاحة</a>
            </div>
        </div>

        <!-- 🎟️ الحجوزات -->
        <div class="col-md-4">
            <div class="dash-card">
                <h5>🎟️ الحجوزات</h5>
                <p class="text-muted">إدارة حجوزات القاعات والملاعب</p>
                <a href="{{ route('reservation.my-reservations') }}" class="btn btn-main btn-sm">عرض الحجوزات</a>
            </div>
        </div>

    </div>

    <div class="dash-box mt-4">
    <h4 class="mb-3">📌 حالة ملفك</h4>

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


</div>
@endsection
