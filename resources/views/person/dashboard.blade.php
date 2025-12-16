@extends('layouts.app')

@section('content')
<style>
    body { font-family: "Cairo", sans-serif !important; }
    .dash-box { background: #ffffff; border-radius: 16px; padding: 25px; box-shadow: 0 4px 16px rgba(0,0,0,0.08);}
    .dash-card { border-radius: 14px; padding: 20px; background: #f8fdf9; border: 1px solid #d6f5e1;
                 text-align: center; transition:.25s; }
    .dash-card:hover { transform: translateY(-4px); box-shadow:0 4px 14px rgba(0,0,0,0.1);}
    .btn-main { background:#1b5e20!important; color:#fff; border-radius:10px; padding:8px 18px; font-weight:700;}
</style>

<div class="container py-4" style="direction: rtl; text-align:right">
    
    <div class="dash-box mb-4" style="background:#1b5e20; color:white;">
        <h3 class="text-center mb-2">👋 أهلاً {{ Auth::user()->name }}</h3>
        <p class="text-center">
            مرحباً بك في منصة النشاطات الرياضية لولاية ميلة
        </p>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="dash-card">
                <h5>📄 ملفي</h5>
                <p class="text-muted">إدارة معلوماتك الشخصية</p>
                <a href="{{ route('profile.step', 1) }}" class="btn btn-main btn-sm">تعديل الملف</a>

                
       
            </div>
        </div>

        <div class="col-md-4">
            <div class="dash-card">
                <h5>⭐ النشاطات المتاحة</h5>
                <p class="text-muted">تصفح وقم بالحجز</p>
                <a href="{{ route('activities.index') }}" class="btn btn-main btn-sm">أستكشف النشاطات المتاحة</a>
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


    <div class="row g-4">

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <h6 class="text-muted">📋 إجمالي الحجوزات</h6>
                <h2 class="fw-bold text-primary">{{ $stats['total'] }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <h6 class="text-muted">💳 مدفوعة</h6>
                <h2 class="fw-bold text-success">{{ $stats['paid'] }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <h6 class="text-muted">⏳ قيد الانتظار</h6>
                <h2 class="fw-bold text-warning">{{ $stats['pending'] }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <h6 class="text-muted">❌ ملغاة</h6>
                <h2 class="fw-bold text-danger">{{ $stats['cancelled'] }}</h2>
            </div>
        </div>
    </div>

</div>

    <div class="dash-box mt-4">
    <h4 class="mb-3">📌 حالة ملفك</h4>

    @if($dossier)

    @php
        $attachments = json_decode($dossier->attachments ?? '[]', true);
        $hasFiles = is_array($attachments) && count($attachments) > 0;
        $hasNote  = !empty($dossier->note_admin);
    @endphp

    {{-- 🟡 حالة انتظار رفع الوثائق --}}
    @if(!$hasFiles)
        <div class="alert alert-info status-box">
            ⚠ ملفك غير مكتمل!
            <br>يرجى رفع الوثائق المطلوبة لإكمال معالجة الطلب.
            <br>

            @if($hasNote)
                <hr>
                <strong>📝 ملاحظة الإدارة:</strong>
                <div class="mt-1 small">
                    {{ $dossier->note_admin }}
                </div>
            @endif

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

            @if($hasNote)
                <hr>
                <strong>📝 سبب الرفض / ملاحظة الإدارة:</strong>
                <div class="mt-1 small">
                    {{ $dossier->note_admin }}
                </div>
            @endif

            <a href="{{ route('profile.step', 4) }}" class="btn btn-light btn-sm mt-2">
                ✏️ إعادة رفع الوثائق
            </a>
        </div>

    {{-- 🕒 حالة قيد الدراسة --}}
    @else
        <div class="alert alert-warning status-box">
            ⏳ ملفك قيد الدراسة حالياً 🔔

            @if($hasNote)
                <hr>
                <strong>📝 ملاحظة الإدارة:</strong>
                <div class="mt-1 small">
                    {{ $dossier->note_admin }}
                </div>
            @endif
        </div>
    @endif

@else
    {{-- لا يوجد ملف بعد --}}
    <div class="alert alert-info status-box">
        ⚠ لم تقم بإرسال ملفك بعد!
        <br>
        <a href="{{ route('profile.step', 1) }}" class="btn btn-primary btn-sm mt-2">
            🚀 أكمل البيانات الآن
        </a>
    </div>
@endif


</div>


<div class="dash-box mt-4">
    <h4 class="mb-3">📥 تحميل النماذج الرسمية</h4>

    <p class="text-muted mb-3">
        يرجى تحميل النماذج التالية، تعبئتها، ثم إعادة رفعها في ملفك الشخصي.
    </p>

    <div class="row g-3">

        {{-- 📄 نموذج التعهد  --}}
        <div class="col-md-6">
            <div class="dash-card">
                <h6>📄 نموذج التعهّد</h6>
                <p class="text-muted small">
                    خاص بالمشاركين البالغين
                </p>
                <a href="{{ asset('forms/engagement.pdf') }}"
                   target="_blank"
                   class="btn btn-outline-success btn-sm">
                    ⬇ تحميل النموذج
                </a>
            </div>
        </div>

        {{-- 📄 التصريح الأبوي (للقُصّر) --}}
        <div class="col-md-6">
            <div class="dash-card">
                <h6>📄 نموذج التصريح الأبوي</h6>
                <p class="text-muted small">
                    خاص بالمشاركين القُصّر
                </p>
                <a href="{{ asset('forms/parental_authorization.pdf') }}"
                   target="_blank"
                   class="btn btn-outline-success btn-sm">
                    ⬇ تحميل النموذج
                </a>
            </div>
        </div>

    </div>
</div>
</div>



@endsection
