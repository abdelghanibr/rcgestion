@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right; max-width:900px">

    <h4 class="mb-4">💳 إتمام الدفع</h4>

    <div class="row g-4">

        {{-- ===================== --}}
        {{-- معلومات المستخدم --}}
        {{-- ===================== --}}
        <div class="col-md-5">
            <div class="card shadow-sm p-3">
                <h6 class="fw-bold mb-3">👤 معلومات المستخدم</h6>

                <p>
                    <strong>الاسم:</strong>
                    {{ auth()->user()->name ?? 'غير متوفر' }}
                </p>

                <p>
                    <strong>البريد الإلكتروني:</strong>
                    {{ auth()->user()->email ?? 'غير متوفر' }}
                </p>

                <p>
                    <strong>نوع الحساب:</strong>
                    @switch(auth()->user()->type)
                        @case('person')
                            فرد
                            @break
                        @case('club')
                            نادي
                            @break
                        @case('company')
                        @case('entreprise')
                            مؤسسة
                            @break
                        @default
                            غير معروف
                    @endswitch
                </p>

                @if(auth()->user()->phone)
                    <p>
                        <strong>الهاتف:</strong>
                        {{ auth()->user()->phone }}
                    </p>
                @endif

                {{-- معلومات إضافية حسب النوع --}}
                @if(auth()->user()->type === 'club' && isset($club))
                    <p>
                        <strong>اسم النادي:</strong>
                        {{ $club->name ?? 'غير متوفر' }}
                    </p>
                @endif
            </div>
        </div>

        {{-- ===================== --}}
        {{-- معلومات الدفع --}}
        {{-- ===================== --}}
        <div class="col-md-7">
            <div class="card p-4 shadow-sm">

                <h6 class="fw-bold mb-3">🧾 تفاصيل الحجز</h6>

                <p>
                    <strong>رقم الحجز:</strong>
                    {{ $reservation->id }}
                </p>

                <p>
                    <strong>المبلغ:</strong>
                    {{ number_format($reservation->total_price, 2) }} دج
                </p>

                <p>
                    <strong>حالة الدفع:</strong>
                    @if($reservation->payment_status === 'paid')
                        <span class="badge bg-success">مدفوع</span>
                    @elseif($reservation->payment_status === 'pending')
                        <span class="badge bg-warning">قيد الانتظار</span>
                    @else
                        <span class="badge bg-danger">فشل</span>
                    @endif
                </p>

                <hr>

                <form method="POST" action="#">
                    @csrf

                    {{-- لاحقًا: Dahabia / BaridiMob / CIB --}}
                    <button class="btn btn-success w-100 fw-bold">
                        ✅ تأكيد الدفع
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection

