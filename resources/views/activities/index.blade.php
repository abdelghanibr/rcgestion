@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl; text-align:right">

    <!-- الشريط الأزرق الأخضر -->
    <div class="p-3 mb-4"
         style="background: linear-gradient(to right, #0a4f88, #0a8a67);
                border-radius: 10px;
                color: #fff;
                font-weight:600;">
        <div class="d-flex justify-content-between align-items-center">
            <span>إدارة جميع النشاطات الخاصة بك هنا</span>
            <span style="font-size:20px;"><i class="fa-solid fa-wave-pulse"></i> نشاطاتي</span>
        </div>
    </div>


    <!-- الفلاتر + البحث -->
    <div class="d-flex justify-content-between mb-3">

        <!-- أزرار الفلترة -->
        <div class="d-flex gap-4" style="font-size:16px; font-weight:600;">
            <a href="{{ route('activities.index') }}" class="text-success text-decoration-none">
                <i class="fa-solid fa-list"></i> جميع النشاطات
            </a>

            <a href="#" class="text-warning text-decoration-none">
                <i class="fa-regular fa-clock"></i> قيد التنفيذ
            </a>

            <a href="#" class="text-success text-decoration-none">
                <i class="fa-solid fa-check-circle"></i> مكتملة
            </a>

            <a href="#" class="text-primary text-decoration-none">
                <i class="fa-solid fa-user"></i> نشاطاتي
            </a>
        </div>

        <!-- البحث -->
        <form method="GET" action="{{ route('activities.index') }}" class="d-flex">
            <input name="search"
                   type="text"
                   class="form-control"
                   placeholder="ابحث عن نشاط..."
                   style="width: 200px; border-radius:8px;">
            <button class="btn btn-primary ms-2">
                <i class="fa-solid fa-search"></i>
            </button>
        </form>
    </div>


    <!-- عرض النشاطات -->
    <div class="row g-4">
        @forelse ($activities as $a)
        <div class="col-md-4">

            <div class="card shadow-sm" style="border: 2px solid {{ $a->color }};">

              @if($a->icon)
<div style="height:180px; background:#f0f0f0; overflow:hidden;">
    <img src="{{ $a->icon ?? asset('images/default-activity.png') }}"
         alt="Activity Icon"
         style="width:100%; height:100%; object-fit:cover;"
         onerror="this.src='{{ asset('images/default-activity.png') }}'">
</div>
@else
    <span class="text-muted">🔄 لا توجد صورة</span>
@endif

                <div class="card-body">
                    <h5 class="fw-bold" style="color: {{ $a->color }};">{{ $a->title }}</h5>

                    <p class="text-muted">{{ Str::limit($a->description, 90) }}</p>

                  <a href="{{ route('activities.complexes', $a->id) }}" class="btn btn-success btn-sm">
    <i class="fa-solid fa-pen-to-square ms-1"></i>
    تسجيل في النشاط
</a>
                </div>
            </div>

        </div>
        @empty

        <div class="alert alert-info text-center">
            لا توجد نشاطات متاحة حالياً.
        </div>

        @endforelse
    </div>

</div>

@endsection
