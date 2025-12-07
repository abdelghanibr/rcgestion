@extends('layouts.app')

@section('content')
  <!-- 👑 Header Admin -->
<style>
    body { font-family: "Cairo", sans-serif !important; }

    .dash-header {
        background: #9d1421;
        color: #fff;
        border-radius: 16px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }

    .dash-card {
        border-radius: 16px;
        padding: 28px 20px;
        text-align: center;
        background: #ffffff;
        border: 2px solid #e8eef3;
        transition: .25s;
        cursor: pointer;
        box-shadow: 0 3px 10px rgba(0,0,0,0.06);
    }

    .dash-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        border-color: #0a4f88;
    }

    .dash-icon {
        font-size: 45px;
        margin-bottom: 15px;
        color: #0a4f88;
    }

    .dash-btn {
        background: #9d1421; !important;
        color: #fff !important;
        font-weight: bold;
        border-radius: 10px;
        padding: 6px 14px;
    }

    .count-box {
        background: #f1f7fc;
        padding: 6px 12px;
        font-size: 14px;
        margin-top: 10px;
        border-radius: 8px;
        font-weight: 600;
        border: 1px solid #d8e4ef;
    }
</style>

<div class="container py-4" style="direction: rtl; text-align:right;">

    <!-- 👑 Header Admin -->
    <div class="dash-header mb-4">
        <h3 class="fw-bold">🎯 أهلاً بك مدير النظام {{ Auth::user()->name }}!</h3>
        <p class="mb-0">يمكنك هنا إدارة الملفات، النوادي وعضويات المشتركين بسهولة</p>
    </div>

    <div class="row g-4">

        <!-- 📂 إدارة ملفات المشتركين -->
        <div class="col-md-4">
            <a href="{{ route('admin.dossiers.index') }}" class="text-decoration-none text-dark">
                <div class="dash-card">
                    <div class="dash-icon">🗂️</div>
                    <h5 class="fw-bold">ملفات المشتركين</h5>
                    <p class="text-muted">قبول – رفض – متابعة الطلبات</p>
                    <div class="count-box">الإجمالي: {{ $dossiersCount }}</div>
                </div>
            </a>
        </div>

        <!-- 🏊‍♂️ إدارة النوادي -->
        <div class="col-md-4">
            <a href="{{ route('admin.clubs.index') }}" class="text-decoration-none text-dark">
                <div class="dash-card">
                    <div class="dash-icon">🏊‍♂️</div>
                    <h5 class="fw-bold">النوادي الرياضية</h5>
                    <p class="text-muted">تنظيم واعتماد النوادي</p>
                    <div class="count-box">عدد النوادي: {{ $clubsCount }}</div>
                </div>
            </a>
        </div>

        <!-- 🧑‍🤝‍🧑 إدارة الأفراد -->
        <div class="col-md-4">
            <a href="#" class="text-decoration-none text-dark">
                <div class="dash-card">
                    <div class="dash-icon">👥</div>
                    <h5 class="fw-bold">الأفراد</h5>
                    <p class="text-muted">متابعة ملفات الأعضاء</p>
                    <div class="count-box">عدد الأفراد: {{ $personsCount }}</div>
                </div>
            </a>
        </div>

    <!-- 👑 المسؤولون -->
<div class="col-md-4">
    <div class="dash-card" style="background: #fff7ed; border: 1px solid #ffd8a8;">
        <div class="dash-icon mb-2">
            <i class="fa-solid fa-user-shield" style="font-size: 32px; color:#d9480f"></i>
        </div>

        <h5 class="fw-bold" style="color:#d9480f;">المسؤولون 👑</h5>
        <p class="text-muted">تحكم كامل في حسابات الإدارة</p>

        <div class="count-box mb-3">
            <span style="font-size: 15px;">عدد المسؤولين :</span>
            <span class="badge bg-warning text-dark">{{ $adminsCount ?? 0 }}</span>
        </div>

        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('admins.index') }}" class="btn btn-warning btn-sm fw-bold">
                👥 عرض المسؤولين
            </a>

            <a href="{{ route('admins.create') }}" class="btn btn-success btn-sm fw-bold">
                ➕ إضافة مسؤول
            </a>
        </div>
    </div>
</div>


    </div>

</div>

@endsection
