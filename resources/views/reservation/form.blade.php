@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right">
    @php
        $typeLabel = match (auth()->user()->type) {
            'person' => 'فرد',
            'club' => 'نادي',
            'company' => 'مؤسسة',
            default => 'مستخدم'
        };
        $ageCategoryName = optional(optional(auth()->user()->person)->ageCategory)->name;
        $hasSchedules = $schedules->isNotEmpty();
    @endphp

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show fw-bold text-center" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show fw-bold text-center" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show fw-bold" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>⚠ {{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('reservation.store') }}" method="POST" id="reserveForm">
        @csrf
        <input type="hidden" name="complex_activity_id" value="{{ $complexActivity->id }}">
        <input type="hidden" name="type_client" value="{{ auth()->user()->type_client }}">
        <input type="hidden" name="pricing_plan_id" id="pricing_plan_id">

        {{-- 🧾 معلومات المستعمل و الحجز --}}
        <div class="card shadow-sm p-3 rounded-4 mb-4">
            <h5 class="fw-bold text-primary mb-3">🔹 معلومات الحجز</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">👤 اسم المستعمل</label>
                    <input class="form-control bg-light" value="{{ auth()->user()->name }}" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="fw-bold">🏷️ نوع المستعمل</label>
                    <input class="form-control bg-light" value="{{ $typeLabel }}" readonly>
                </div>

                @if(auth()->user()->type === 'person')
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">🎯 الفئة العمرية</label>
                        <input class="form-control bg-light" value="{{ $ageCategoryName ?? 'غير محدد' }}" readonly>
                    </div>
                @endif

                <div class="col-md-6 mb-3">
                    <label class="fw-bold">🏟️ المركب</label>
                    <input class="form-control bg-light" value="{{ $complex->nom }}" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="fw-bold">🤸 النشاط</label>
                    <input class="form-control bg-light" value="{{ $activity->title }}" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="fw-bold">📅 الموسم</label>
                    <select class="form-select" name="season_id" id="season_select" required onchange="reloadWithSeason(this.value)">
                        <option value="" disabled {{ !$selectedSeasonId ? 'selected' : '' }}>اختر موسماً أولاً</option>
                        @foreach($seasons as $season)
                            <option value="{{ $season->id }}"
                                    {{ $selectedSeasonId == $season->id ? 'selected' : '' }}
                                    data-start="{{ $season->date_debut }}"
                                    data-end="{{ $season->date_fin }}">
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <script>
                function reloadWithSeason(seasonId) {
                    if (seasonId) {
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('season_id', seasonId);
                        window.location.href = currentUrl.toString();
                    }
                }
            </script>

            @if(!$selectedSeasonId)
                <div class="alert alert-warning py-2 text-center fw-bold">
                    ⚠️ يرجى اختيار الموسم أولاً لعرض الجداول المتاحة.
                </div>
            @else
                <div class="alert alert-info py-2 text-center fw-bold">
                    💰 سيتم احتساب السعر تلقائياً بعد اختيار الجدول الزمني المناسب.
                </div>
            @endif
        </div>

        {{-- 📋 الجداول المتاحة --}}
        @if($selectedSeasonId)
            <div class="card shadow-sm p-3 rounded-4 mb-4">
                <h5 class="fw-bold text-secondary mb-3">📋 اختر الجدول الذي يناسبك</h5>

                @if(!$hasSchedules)
                    <div class="alert alert-warning fw-bold text-center mb-0">
                        🚧 لا توجد جداول زمنية مفعّلة لهذا النشاط حالياً.
                    </div>
                @else
                    <div class="d-flex flex-column gap-3">
                    @foreach($schedules as $schedule)
                        @php
                            $plan = $schedule->applied_plan;// جلب خطة التسعير المطبقة على الجدول
                            $planDurationUnit = optional($plan)->duration_unit;// جلب وحدة مدة الخطة
                            $planUnit = match ($planDurationUnit) {// تحويل وحدة المدة إلى اللغة العربية
                                'month', 'monthly' => 'شهر',
                                'week', 'weekly' => 'أسبوع',
                                'season' => 'موسم',
                                default => $planDurationUnit
                            };
                            $planDurationValue = optional($plan)->duration_value ?? 1;// جلب قيمة مدة الخطة
                            $planDuration = $plan ? trim($planDurationValue . ' ' . ($planUnit ?: 'فترة')) : '';
                            $isPlan = $schedule->type_prix === 'pricing_plan';// التحقق مما إذا كانت خطة التسعير مستخدمة
                            $planUnavailable = $isPlan && !$plan;// التحقق من عدم توفر خطة تسعير مطابقة
                            $hasPrice = !is_null($schedule->calculated_price);// التحقق من وجود سعر محسوب
                            $isDisabled = $planUnavailable || !$hasPrice;// تعطيل الخيار إذا لم تتوفر خطة أو سعر
                            $sexLabel = match ($schedule->sex) {
                                'H' => 'ذكور',
                                'F' => 'إناث',
                                default => 'مختلط',
                            };
                        @endphp

                        <label class="schedule-option card border-0 shadow-sm p-3 m-0 {{ $isDisabled ? 'schedule-option--disabled' : '' }}">
                            <div class="d-flex flex-column flex-md-row align-items-start gap-3">
                                <div class="form-check mt-1">
                            <input class="form-check-input schedule-radio"
       type="radio"
       name="schedule_id"
       value="{{ $schedule->id }}"
       {{ $isDisabled ? 'disabled' : '' }}

       data-type-prix="{{ $schedule->type_prix }}"
       data-price="{{ $schedule->calculated_price ?? '' }}"

       {{-- pricing plan (فقط إذا type_prix = pricing_plan) --}}
       data-plan-id="{{ $plan?->id ?? '' }}"
       data-plan-name="{{ $plan?->name ?? '' }}"
       data-plan-type="{{ $plan?->pricing_type ?? '' }}"
       data-plan-duration-unit="{{ $plan?->duration_unit ?? '' }}"
       data-plan-duration-value="{{ $plan?->duration_value ?? '' }}"
       data-plan-sessions="{{ $plan?->sessions_per_week ?? $schedule->sessions_count }}"

       data-sessions="{{ $schedule->sessions_count }}"
       data-pricing-note="{{ $schedule->pricing_note ?? '' }}">
        
                            </div>

                                <div class="flex-grow-1 w-100">
                                    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                        <span class="fw-bold text-primary">{{ $schedule->groupe }}</span>
                                        @if($schedule->ageCategory)
                                            <span class="badge bg-light text-dark">{{ $schedule->ageCategory->name }}</span>
                                        @endif
                                        <span class="badge bg-secondary text-white">{{ $sexLabel }}</span>
                                        <span class="badge bg-info text-white">{{ $schedule->sessions_count }} حصة / الأسبوع</span>
                                        @if(!is_null($schedule->available_places))
                                            <span class="badge bg-success text-white">
                                                متبقي {{ $schedule->available_places }} من {{ $schedule->nbr ?? '?' }}
                                            </span>
                                        @endif
                                    </div>

                                    <ul class="list-unstyled small mb-2">
                                        @forelse($schedule->formatted_slots as $slot)
                                            <li>🕒 {{ $slot }}</li>
                                        @empty
                                            <li class="text-muted">لا توجد خانات زمنية محددة بعد.</li>
                                        @endforelse
                                    </ul>

                                    @if($planUnavailable)
                                        <div class="alert alert-warning py-1 px-2 mb-0 small fw-bold">
                                            ⚠ لا توجد خطة تسعير مطابقة لعدد الحصص في هذا الجدول.
                                        </div>
                                    @elseif(!$hasPrice)
                                        <div class="alert alert-warning py-1 px-2 mb-0 small fw-bold">
                                            ⚠ لم يتم تحديد سعر لهذا الجدول حتى الآن.
                                        </div>
                                    @endif
                                </div>

                                <div class="text-md-end text-center w-100 w-md-auto">
                                    <p class="mb-1 text-muted fw-bold">السعر</p>
                                    <span class="price-chip">
                                        @if($hasPrice)
                                            {{ number_format($schedule->calculated_price) }}  دج . 
                                        @else
                                            —
                                        @endif
                                    </span>
                                    <div class="small mt-2">
                                        @if($isPlan)
                                            خطة: {{ $plan?->name ?? 'غير متاحة' }}
                                        @else
                                            تسعيرة ثابتة
                                        @endif
                                        @if($schedule->pricing_note)
                                            <div class="text-muted">{{ $schedule->pricing_note }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
            </div>

            {{-- 💸 خطة التسعير --}}
            <div class="card shadow-sm p-3 rounded-4 mb-4" id="pricingCard" style="display:none;">
            <h5 class="fw-bold text-dark mb-3">📌 تفاصيل خطة التسعير</h5>

            <table class="table table-bordered table-striped text-center mb-0">
                <tbody>
                    <tr>
                        <td class="fw-bold">اسم الخطة</td>
                        <td id="plan_name">-</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">نوع التسعير</td>
                        <td id="plan_type">-</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">عدد الحصص / الأسبوع</td>
                        <td id="plan_hours">-</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">السعر</td>
                        <td id="plan_price">-</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">مدة الاشتراك</td>
                        <td id="plan_duration">-</td>
                    </tr>
                </tbody>
            </table>
            </div>

            {{-- 💵 السعر و التأكيد --}}
            <div class="card shadow-sm p-4 rounded-4">
                <label class="fw-bold">🔥 السعر الإجمالي (دج)</label>
                <input type="text"
                       id="total_price"
                       class="form-control bg-light text-center fw-bold fs-5 mb-3"
                       readonly>
                <p class="text-muted small mb-3" id="price_hint"></p>

                <button class="btn btn-success w-100 fs-5 fw-bold" {{ ($hasSchedules && $selectedSeasonId) ? '' : 'disabled' }}>
                    ✔ تأكيد الحجز
                </button>
            </div>
        @endif
    </form>
</div>
@endsection

@push('styles')
<style>
.schedule-option {
    cursor: pointer;
    border: 2px solid transparent;
    transition: border-color .2s ease, transform .2s ease;
    border-radius: 18px;
}

.schedule-option:hover:not(.schedule-option--disabled) {
    border-color: #0d6efd;
    transform: translateY(-2px);
}

.schedule-option--disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.schedule-option.selected {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.schedule-option .form-check-input {
    width: 1.2rem;
    height: 1.2rem;
}

.price-chip{
    display: inline-block;
    padding: 6px 14px;
    border: 2px solid #16a34a;      /* أخضر */
    color: #16a34a;
    border-radius: 999px;           /* شكل كبسولة */
    font-weight: 700;
    font-size: 0.95rem;
    background-color: #ecfdf5;      /* أخضر فاتح */
}

</style>
@endpush
@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const radios = document.querySelectorAll('.schedule-radio');
    const totalPrice = document.getElementById('total_price');
    const priceHint = document.getElementById('price_hint');

    const pricingCard = document.getElementById('pricingCard');
    const pricingPlanInput = document.getElementById('pricing_plan_id');

    const planName = document.getElementById('plan_name');
    const planType = document.getElementById('plan_type');
    const planHours = document.getElementById('plan_hours');
    const planPrice = document.getElementById('plan_price');
    const planDuration = document.getElementById('plan_duration');

    const seasonSelect = document.getElementById('season_select');

    const formatPrice = v =>
        Number.isFinite(v) ? new Intl.NumberFormat('ar-DZ').format(v) + ' دج' : '';

    /* ===============================
       RESET PRICING PLAN CARD
    =============================== */
    const resetPlanCard = () => {
        pricingPlanInput.value = '';
        pricingCard.style.display = 'none';
        planName.textContent = '-';
        planType.textContent = '-';
        planHours.textContent = '-';
        planPrice.textContent = '-';
        planDuration.textContent = '-';
    };

    /* ===============================
       HIGHLIGHT SELECTED SCHEDULE
    =============================== */
    const highlight = (radio) => {
        document.querySelectorAll('.schedule-option')
            .forEach(o => o.classList.remove('selected'));
        radio.closest('.schedule-option')?.classList.add('selected');
    };

    /* ===============================
       GET SEASON (FOR PLANS ONLY)
    =============================== */
    const getSeason = () => {
        if (!seasonSelect || !seasonSelect.value) return null;
        const opt = seasonSelect.options[seasonSelect.selectedIndex];
        return {
            start: opt.dataset.start,
            end: opt.dataset.end
        };
    };

    const calcSeasonMonths = (start, end) => {
        const s = new Date(start);
        const e = new Date(end);
        let months = (e.getFullYear() - s.getFullYear()) * 12 + (e.getMonth() - s.getMonth());
        if (e.getDate() >= s.getDate()) months++;
        return Math.max(1, months);
    };

    /**
     * حساب السعر التناسبي للشهر الأول إذا بدأ الاشتراك بعد اليوم الأول
     * Calculate prorated price for first month if subscription starts mid-month
     */
    const calculateProratedFirstMonth = (monthlyPrice) => {
        const today = new Date();
        const dayOfMonth = today.getDate();
        
        // إذا بدأ الاشتراك في اليوم الأول، لا حاجة للتقسيم التناسبي
        if (dayOfMonth === 1) {
            return monthlyPrice;
        }
        
        // حساب عدد الأيام في الشهر الحالي
        const year = today.getFullYear();
        const month = today.getMonth();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        // الأيام المتبقية في الشهر (بما فيها يوم البداية)
        const remainingDays = daysInMonth - dayOfMonth + 1;
        
        // حساب السعر التناسبي
        const proratedPrice = (monthlyPrice / daysInMonth) * remainingDays;
        
        return Math.round(proratedPrice * 100) / 100; // تقريب لرقمين عشريين
    };

    /* ===============================
       MAIN HANDLER
    =============================== */
    const onSelectSchedule = (radio) => {
        highlight(radio);

        const typePrix = radio.dataset.typePrix; // fixed | pricing_plan
        const basePrice = parseFloat(radio.dataset.price || 0);

        console.log('onSelectSchedule called:', {
            typePrix,
            basePrice,
            totalPriceElement: totalPrice,
            priceHintElement: priceHint
        });

        /* ===============================
           ✅ FIX PRICE (الحل النهائي)
        =============================== */
        if (typePrix !== 'pricing_plan') {
            resetPlanCard(); // ❌ لا خطة
            
            // تطبيق التقسيم التناسبي للشهر الأول
            const proratedPrice = calculateProratedFirstMonth(basePrice);
            
            totalPrice.value = formatPrice(proratedPrice);
            
            if (proratedPrice < basePrice) {
                const today = new Date();
                const daysInMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();
                const remainingDays = daysInMonth - today.getDate() + 1;
                priceHint.textContent = `💵 سعر تناسبي: ${remainingDays} يوم من ${daysInMonth} يوم في الشهر الحالي`;
            } else {
                priceHint.textContent = '💵 سعر ثابت حسب الجدول';
            }
            
            console.log('Fixed price set:', totalPrice.value);
            return;
        }

        /* ===============================
           ✅ PRICING PLAN
        =============================== */
        const season = getSeason();
        if (!season) {
            totalPrice.value = '';
            priceHint.textContent = '⚠ يرجى اختيار الموسم لحساب سعر الخطة';
            resetPlanCard();
            return;
        }

        const months = calcSeasonMonths(season.start, season.end);

        const durationUnit = radio.dataset.planDurationUnit;
        const durationValue = parseInt(radio.dataset.planDurationValue || 1);
        const sessions = parseInt(radio.dataset.planSessions || 1);

        let computed = basePrice;

        switch (durationUnit) {
            case 'month':
            case 'monthly':
                // تطبيق التقسيم التناسبي للشهر الأول
                computed = calculateProratedFirstMonth(basePrice);
                break;

            case 'week':
            case 'weekly':
                computed = Math.ceil((months * 4) / durationValue) * basePrice;
                break;

            case 'session':
                computed = months * sessions * basePrice;
                break;
        }

        pricingPlanInput.value = radio.dataset.planId || '';
        pricingCard.style.display = 'block';

        planName.textContent = radio.dataset.planName || '-';
        planType.textContent = radio.dataset.planPricingType || '-';
        planHours.textContent = sessions + ' حصة / الأسبوع';
        planDuration.textContent = durationValue + ' ' + durationUnit;
        planPrice.textContent = formatPrice(computed);

        totalPrice.value = formatPrice(computed);
        
        // عرض رسالة توضيحية للسعر التناسبي
        if ((durationUnit === 'month' || durationUnit === 'monthly') && computed < basePrice) {
            const today = new Date();
            const daysInMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();
            const remainingDays = daysInMonth - today.getDate() + 1;
            priceHint.textContent = `📅 سعر تناسبي للشهر الأول: ${remainingDays} يوم من ${daysInMonth} يوم`;
        } else {
            priceHint.textContent = '📅 السعر محسوب حسب خطة التسعير والموسم';
        }
    };

    /* ===============================
       EVENTS
    =============================== */
    radios.forEach(r => {
        r.addEventListener('change', () => onSelectSchedule(r));
    });

    if (seasonSelect) {
        seasonSelect.addEventListener('change', () => {
            const selected = document.querySelector('.schedule-radio:checked');
            if (selected && selected.dataset.typePrix === 'pricing_plan') {
                onSelectSchedule(selected);
            }
        });
    }

    /* ===============================
       AUTO-SELECT FIRST SCHEDULE
    =============================== */
    console.log('Auto-selection check:', {
        radiosCount: radios.length,
        seasonSelectExists: !!seasonSelect,
        seasonValue: seasonSelect?.value
    });
    
    const firstRadio = Array.from(radios).find(r => !r.disabled);
    console.log('First enabled radio:', firstRadio);
    
    if (firstRadio && seasonSelect && seasonSelect.value) {
        console.log('Auto-selecting first schedule...');
        firstRadio.checked = true;
        onSelectSchedule(firstRadio);
    }

});
</script>
@endpush
