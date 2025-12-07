@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">

    <div class="p-3 mb-4"
        style="background: linear-gradient(to right, #0a4f88, #0a8a67);
               border-radius: 10px;
               color: #fff;
               font-weight:600;">
        <div class="d-flex justify-content-between align-items-center">
            <span>إدارة جميع المركبات الخاصة بهذا النشاط</span>
            <span style="font-size:20px;">
                <i class="fa-solid fa-futbol ms-2"></i>
                مركباتي
            </span>
        </div>
    </div>

    <h4 class="fw-bold mb-4 text-primary">
        🏟️ المركبات المتاحة لنشاط :
        <span style="color: {{ $activity->color }};">{{ $activity->title }}</span>
    </h4>

    <div class="row g-4">

        @forelse($activity->complexes as $complex)
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-3"
                style="border-color: {{ $activity->color }}; border-radius:12px; overflow:hidden;">

                {{-- صورة المركبة --}}
                <div style="height:180px; background:#f0f0f0;">
                    <img src="{{ $complex->image ? asset('uploads/complexes/'.$complex->image) : asset('images/no-image.png') }}"
                         class="w-100 h-100"
                         style="object-fit:cover;">
                </div>

                {{-- محتوى البطاقة --}}
                <div class="p-3">

                    <h5 class="fw-bold" style="color: {{ $activity->color }};">
                        {{ $complex->nom }}
                    </h5>

                    <p class="text-muted small mb-1">
                        🧑‍🤝‍🧑 السعة: {{ $complex->capacite }} شخص
                    </p>

                    <p class="fw-bold text-success mb-3">
                        💵 السعر: {{ number_format($complex->prix,2) }} دج
                    </p>

                    <a href="{{ route('reservation.form', $complex->id) }}"
                       class="btn btn-success w-100">
                        <i class="fa-solid fa-pen-to-square ms-1"></i> تسجيل في المركبة
                    </a>

                </div>
            </div>
        </div>
        @empty

        <div class="alert alert-warning text-center">
            لا يوجد مركبات لهذا النشاط حالياً.
        </div>

        @endforelse

    </div>

</div>
@endsection
