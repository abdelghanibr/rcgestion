@extends('layouts.app')

@section('content')
<style>
/* ===============================
   GLOBAL
================================ */
body {
    font-family: "Cairo", sans-serif !important;
    background: #f6f8fb;
}

/* ===============================
   VARIABLES
================================ */
:root{
    --club-blue:#0a4f88;
    --club-blue-dark:#083d65;
    --club-blue-soft:#e7f1fb;

    --club-green:#16a34a;
    --club-green-soft:#eafaf1;

    --club-border:#e5e7eb;
    --club-muted:#6b7280;

    --club-radius:18px;
    --club-shadow:0 10px 28px rgba(0,0,0,.08);
}

/* ===============================
   HEADER
================================ */
.dash-box{
    background: linear-gradient(135deg, var(--club-blue), #d0d6daff);
    color:#fff;
    border-radius: var(--club-radius);
    padding:28px;
    box-shadow:var(--club-shadow);
}

/* ===============================
   CARDS GRID (3 per row)
================================ */
.cards-wrapper{
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-top: 20px;
}

/* Responsive */
@media (max-width: 992px){
    .cards-wrapper{
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 576px){
    .cards-wrapper{
        grid-template-columns: 1fr;
    }
}

/* Card */
.club-players-card{
    background:#fff;
    border-radius:18px;
    padding:24px;
    box-shadow:0 12px 30px rgba(0,0,0,0.08);
    display:flex;
    flex-direction:column;
    height:100%;
    transition:.3s;
}
.club-players-card:hover{
    transform:translateY(-6px);
    box-shadow:0 18px 40px rgba(0,0,0,0.12);
}

/* Header */
.card-header{
    display:flex;
    align-items:center;
    gap:14px;
    margin-bottom:18px;
}
.icon-circle{
    width:52px;
    height:52px;
    background:linear-gradient(135deg,#0a4f88,#1e88e5);
    color:#fff;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
}
.card-header h5{
    margin:0;
    font-weight:800;
    color:#0a4f88;
}
.card-header p{
    margin:0;
    font-size:14px;
    color:#6b7280;
}

/* Stats */
.card-stats{
    display:flex;
    justify-content:space-between;
    background:#f8fafc;
    border-radius:14px;
    padding:14px;
    margin-bottom:20px;
}
.stat{
    text-align:center;
    flex:1;
}
.stat .number{
    font-size:22px;
    font-weight:800;
    color:#0a4f88;
}
.stat .label{
    font-size:13px;
    color:#6b7280;
}

/* Button */
.btn-manage{
    margin-top:auto;
    background:#0a4f88;
    color:#fff;
    padding:12px;
    border-radius:14px;
    text-align:center;
    font-weight:700;
    text-decoration:none;
    transition:.25s;
}
.btn-manage:hover{
    background:#083d65;
    color:#fff;
}


/* ===============================
   GENERIC CARD
================================ */
.dash-card,
.club-players-card{
    background:#fff;
    border:1px solid var(--club-border);
    border-radius: var(--club-radius);
    padding:22px;
    text-align:center;
    box-shadow:var(--club-shadow);
    transition:.3s;
    display:flex;
    flex-direction:column;
    height:100%;
}

.dash-card:hover,
.club-players-card:hover{
    transform:translateY(-6px);
    box-shadow:0 16px 38px rgba(0,0,0,.12);
}

/* ===============================
   CARD HEADER (ICON)
================================ */
.card-header{
    display:flex;
    align-items:center;
    gap:14px;
    margin-bottom:18px;
    justify-content:center;
}

.icon-circle{
    width:54px;
    height:54px;
    border-radius:50%;
    background:linear-gradient(135deg,var(--club-blue),#1e88e5);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:26px;
}

.card-header h5{
    margin:0;
    font-weight:800;
    color:var(--club-blue);
}

.card-header p{
    margin:0;
    font-size:14px;
    color:var(--club-muted);
}

/* ===============================
   STATS
================================ */
.card-stats{
    display:flex;
    justify-content:space-between;
    background:#f8fafc;
    border-radius:14px;
    padding:14px 10px;
    margin-bottom:18px;
}

.stat{
    flex:1;
    text-align:center;
}

.stat .number{
    font-size:22px;
    font-weight:800;
    color:var(--club-blue);
}

.stat .label{
    font-size:13px;
    color:var(--club-muted);
}

/* ===============================
   BUTTONS
================================ */
.btn-main,
.btn-manage,
.btn-club-primary{
    background:var(--club-blue);
    color:#fff !important;
    border:none;
    border-radius:14px;
    padding:10px 18px;
    font-weight:800;
    transition:.25s;
    margin-top:auto;
}

.btn-main:hover,
.btn-manage:hover,
.btn-club-primary:hover{
    background:var(--club-blue-dark);
}

.btn-club-outline{
    border:2px solid var(--club-blue);
    color:var(--club-blue);
    background:#fff;
    border-radius:14px;
    font-weight:800;
    padding:10px 18px;
}

.btn-club-outline:hover{
    background:var(--club-blue);
    color:#fff;
}

/* ===============================
   DOSSIER CARD
================================ */
.club-card{
    background:#fff;
    border:1px solid var(--club-border);
    border-radius: var(--club-radius);
    box-shadow:var(--club-shadow);
    margin-top:22px;
}


.club-header {
    background: linear-gradient(194deg, #6b7280, #dee2e6);
    color: #fff;
    padding: 16px 20px;
    border-radius: var(--club-radius) var(--club-radius) 0 0;
    font-weight: 900;
}

/* ===============================
   BADGES
================================ */
.badge-approved{
    background:var(--club-green);
    color:#fff;
    padding:.6rem 1.4rem;
    border-radius:30px;
    font-size:.9rem;
    font-weight:800;
}

/* ===============================
   STATUS BOX
================================ */
.status-box{
    border-radius:16px;
    padding:18px;
    font-weight:800;
    box-shadow:var(--club-shadow);
}

/* ===============================
   DOWNLOAD SECTION
================================ */
.download-card{
    background:var(--club-blue-soft);
    border:2px dashed var(--club-blue);
    border-radius:16px;
    padding:20px;
    text-align:center;
    transition:.3s;
}

.download-card:hover{
    background:#dbeafe;
    transform:translateY(-4px);
}

.download-card i{
    font-size:30px;
    color:var(--club-blue);
    margin-bottom:10px;
}

/* ===============================
   TABLE
================================ */
.table{
    border-radius:14px;
    overflow:hidden;
}

.table thead{
    background:#111827;
    color:#fff;
}
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
    <div class="cards-wrapper">

    <!-- 🧑‍🤝‍🧑 لاعبو النادي -->
    <div class="club-players-card">
        <div class="card-header">
            <div class="icon-circle">👥</div>
            <div>
                <h5>لاعبو النادي</h5>
                <p>إدارة قوائم اللاعبين وتسجيل المنخرطين</p>
            </div>
        </div>

        <div class="card-stats">
            <div class="stat">
                <div class="number">{{ $playersCount }}</div>
                <div class="label">لاعبين</div>
            </div>
            <div class="stat">
                <div class="number">{{ $coachsCount }}</div>
                <div class="label">مدربين</div>
            </div>
            <div class="stat">
                <div class="number">{{ $managersCount }}</div>
                <div class="label">مسيرين</div>
            </div>
        </div>

        <a href="{{ route('club.persons.index') }}" class="btn-manage">
            ⚙️ إدارة اللاعبين
        </a>
    </div>

    <!-- 📅 النشاطات -->
    <div class="club-players-card">
        <div class="card-header">
            <div class="icon-circle">📅</div>
            <div>
                <h5>النشاطات</h5>
                <p>المشاركة في مختلف النشاطات</p>
            </div>
        </div>

        <div class="card-stats">
            <div class="stat">
                <div class="number">—</div>
                <div class="label">نشاطات</div>
            </div>
        </div>

        <a href="{{ route('activities.index') }}" class="btn-manage">
            استكشاف النشاطات
        </a>
    </div>

    <!-- 🎟️ الحجوزات -->
    <div class="club-players-card">
        <div class="card-header">
            <div class="icon-circle">🎟️</div>
            <div>
                <h5>الحجوزات</h5>
                <p>إدارة حجوزات القاعات والملاعب</p>
            </div>
        </div>

        <div class="card-stats">
            <div class="stat">
                <div class="number">—</div>
                <div class="label">حجوزات</div>
            </div>
        </div>

        <a href="{{ route('reservation.my-reservations') }}" class="btn-manage">
            عرض الحجوزات
        </a>
    </div>

</div>

{{-- ================= 📁 Dossier Club ================= --}}
<div class="club-card">
    <div class="club-header p-3 fw-bold">
        📁 ملف النادي
    </div>

    <div class="p-3 text-center">
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
       

        <div class="d-grid gap-2 mt-3">
            <a href="{{ route('club.dossier.index') }}"
               class="btn btn-club-outline">
               👁 عرض الملف
            </a>

            <a href="{{ route('club.dossier.edit') }}"
               class="btn btn-club-primary">
               ✏️ تعديل / إكمال الملف
            </a>
        </div>
    </div>
</div>


   
{{-- ================= 📄 تحميل النماذج ================= --}}
<div class="club-card mt-4">
    <div class="club-header">
        📄 النماذج الرسمية (دفتر الشروط)
    </div>

    <div class="p-4">
        <div class="row g-3">

            <div class="col-md-4">
                <div class="download-card">
                    <i class="bi bi-file-earmark-text"></i>
                    <h6 class="fw-bold mt-2">دفتر الشروط</h6>
                    <a href="{{ asset('docs/daftar_chorout.pdf') }}"
                       class="btn btn-club-outline btn-sm mt-2"
                       download>
                       ⬇ تحميل
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="download-card">
                    <i class="bi bi-file-earmark-check"></i>
                    <h6 class="fw-bold mt-2">نموذج الانخراط</h6>
                    <a href="{{ asset('docs/engagement.pdf') }}"
                       class="btn btn-club-outline btn-sm mt-2"
                       download>
                       ⬇ تحميل
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="download-card">
                    <i class="bi bi-file-earmark-person"></i>
                    <h6 class="fw-bold mt-2">تصريح أبوي</h6>
                    <a href="{{ asset('docs/autorisation_parentale.pdf') }}"
                       class="btn btn-club-outline btn-sm mt-2"
                       download>
                       ⬇ تحميل
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>


</div>
@endsection
