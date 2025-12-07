@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right">

    <h3 class="mb-4">نموذج حجز المركبة: {{ $complex->nom }}</h3>

    <div class="card p-4">

        {{-- ✅ رسالة عند عدم وجود الدوسيي --}}
        @if(session('dossier_not_found'))
            <div class="alert alert-danger">
                ⚠️ عذراً، الدوسيي غير موجود أو رقم الحجز غير صحيح.
            </div>
        @endif

        {{-- ✅ رسالة عند عدم المصادقة على الدوسيي --}}
        @if(session('dossier_not_valid'))
            <div class="alert alert-warning">
                ⚠️ الدوسيي موجود لكن غير مُفعّل أو لم تتم المصادقة عليه بعد.
            </div>
        @endif

        {{-- الرسالة الأصلية --}}
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif


        {{-- معلومات المركبة --}}
        <p><strong>السعة:</strong> {{ $complex->capacite }} شخص</p>
        <p><strong>السعر:</strong> {{ number_format($complex->prix, 2) }} دج</p>


        {{-- الفورم --}}
        <form action="{{ route('reservation.store') }}" method="POST">
            @csrf

            <input type="hidden" name="complex_id" value="{{ $complex->id }}">

            <div class="mb-3">
                <label class="form-label">تاريخ الحجز</label>
                <input type="date" name="date_reservation" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">وقت البداية</label>
                <input type="time" name="heure_debut" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">وقت النهاية</label>
                <input type="time" name="heure_fin" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100">تأكيد الحجز</button>
        </form>
    </div>

</div>
@endsection
