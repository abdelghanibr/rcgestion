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
                    <select class="form-select" name="season_id" id="season_select" required>
                        <option value="" disabled selected>اختر موسماً</option>
                        @foreach($seasons as $season)
                            <option value="{{ $season->id }}"
                                    data-start="{{ $season->date_debut }}"
                                    data-end="{{ $season->date_fin }}">
                                {{ $season->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="alert alert-info py-2 text-center fw-bold">
                💰 سيتم احتساب السعر تلقائياً بعد اختيار الجدول الزمني المناسب.
            </div>
        </div>

        {{-- 📋 الجداول المتاحة --}}
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
                                                                                     data-plan-id="{{ $plan?->id ?? '' }}"
                                                                                     data-plan-name="{{ $plan?->name ?? '' }}"
                                                                                 data-plan-type="{{ $plan?->pricing_type ?? '' }}"
                                                                                 data-plan-pricing-type="{{ $plan?->pricing_type ?? '' }}"
                                         data-plan-duration="{{ $planDuration }}"
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
                                            {{ number_format($schedule->calculated_price, 0, '.', ' ') }} دج
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

            <button class="btn btn-success w-100 fs-5 fw-bold" {{ $hasSchedules ? '' : 'disabled' }}>
                ✔ تأكيد الحجز
            </button>
        </div>
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

.price-chip {
    display: inline-block;
    padding: 0.35rem 1.1rem;
    border-radius: 999px;
    background-color: #e7f1ff;
    color: #0d6efd;
    font-weight: 600;
    font-size: 1rem;
    direction: ltr;
    unicode-bidi: embed;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('reserveForm');
    if (!form) {
        return;
    }

    const scheduleRadios = Array.from(document.querySelectorAll('.schedule-radio')); // جميع خيارات الجداول الزمنية
    const totalPriceInput = document.getElementById('total_price');// حقل السعر الإجمالي
    const priceHint = document.getElementById('price_hint');// تلميح السعر
    const seasonSelect = document.getElementById('season_select');// اختيار الموسم
    const pricingPlanInput = document.getElementById('pricing_plan_id');// حقل خطة التسعير المختارة
    const pricingCard = document.getElementById('pricingCard');// بطاقة تفاصيل خطة التسعير
    const planName = document.getElementById('plan_name');// اسم الخطة
    const planType = document.getElementById('plan_type');// نوع التسعير
    const planHours = document.getElementById('plan_hours');// عدد الحصص في الأسبوع
    const planPrice = document.getElementById('plan_price');// سعر الخطة
    const planDuration = document.getElementById('plan_duration');// مدة الاشتراك

    const isFiniteNumber = (value) => Number.isFinite(value) && value >= 0;// التحقق من كون القيمة رقمية موجبة

    const formatPrice = (value) => {// تنسيق السعر بالعملة المحلية
        if (!value || isNaN(value)) {
            return '';
        }
        return new Intl.NumberFormat('ar-DZ').format(Number(value)) + ' دج';
    };

    const resetPlanCard = () => {//
        planName.textContent = '-';
        planType.textContent = '-';
        planHours.textContent = '-';
        planPrice.textContent = '-';
        planDuration.textContent = '-';
    };

    const highlightOption = (radio) => {// تمييز الخيار المختار
        document.querySelectorAll('.schedule-option').forEach(option => option.classList.remove('selected'));
        const option = radio.closest('.schedule-option');
        if (option) {
            option.classList.add('selected');// إضافة فئة التمييز
    };

    const getSelectedSeason = () => {// جلب الموسم المختار
        if (!seasonSelect) {
            return null;
        }
        const selectedOption = seasonSelect.options[seasonSelect.selectedIndex];// جلب الخيار المختار
        if (!selectedOption || !selectedOption.dataset.start || !selectedOption.dataset.end) {// التحقق من وجود بيانات صالحة
            return null;
        }
        return {
            id: selectedOption.value,
            name: selectedOption.text,
            start: selectedOption.dataset.start,
            end: selectedOption.dataset.end
        };
    };

    const getSeasonMetrics = (season) => {// حساب مقاييس الموسم
        if (!season?.start || !season?.end) {
            return null;
        }

        const startDate = new Date(season.start);
        const endDate = new Date(season.end);

        if (Number.isNaN(startDate) || Number.isNaN(endDate) || endDate < startDate) {
            return null;
        }

        const diffMs = endDate.getTime() - startDate.getTime();
        const days = Math.max(1, Math.round(diffMs / 86400000) + 1);

        let months = (endDate.getFullYear() - startDate.getFullYear()) * 12 + (endDate.getMonth() - startDate.getMonth());
        if (endDate.getDate() >= startDate.getDate() || months === 0) {
            months += 1;
        }
        months = Math.max(1, months);
        const monthsCharged = Math.min(12, months);

        const weeks = Math.max(1, Math.ceil(days / 7));

        return {
            ...season,
            startDate,
            endDate,
            days,
            weeks,
            months,
            monthsCharged
        };
    };

    const calculatePlanPrice = (plan, seasonMetrics) => {
        if (!plan || !seasonMetrics) {
            return null;
        }

        const { weeks, months } = seasonMetrics;
        const durationValue = plan.durationValue > 0 ? plan.durationValue : 1;
        const basePrice = plan.price;
        const type = (plan.durationUnit || plan.pricingType || '').toLowerCase();

        switch (type) {
            case 'month':
            case 'monthly':
                return Math.ceil(months / durationValue) * basePrice;
            case 'week':
            case 'weekly':
                return Math.ceil(weeks / durationValue) * basePrice;
            case 'session':
                return weeks * (plan.sessionsPerWeek || 1) * basePrice;
            case 'ticket':
            default:
                return basePrice;
        }
    };

    const calculateFixedSchedulePrice = (basePrice, seasonMetrics) => {
        if (!seasonMetrics) {
            return null;
        }

        const multiplier = seasonMetrics.monthsCharged ?? seasonMetrics.months ?? 1;
        return Math.max(1, multiplier) * basePrice;
    };

    const updatePlanCard = (radio) => {
        const typePrix = radio.dataset.typePrix;
        pricingPlanInput.value = radio.dataset.planId || '';
        highlightOption(radio);

        if (typePrix === 'pricing_plan') {
            planName.textContent = radio.dataset.planName || '-';
            planType.textContent = radio.dataset.planType || '-';
            planHours.textContent = (radio.dataset.sessions || '0') + ' حصة / الأسبوع';
            planPrice.textContent = '-';
            planDuration.textContent = radio.dataset.planDuration || '-';
            pricingCard.style.display = 'block';
        } else {
            resetPlanCard();
            pricingCard.style.display = 'none';
        }
    };

    const updateTotals = () => {
        const selectedRadio = document.querySelector('input.schedule-radio:checked');
        if (!selectedRadio) {
            totalPriceInput.value = '';
            if (priceHint) {
                priceHint.textContent = '';
            }
            return;
        }

        const typePrix = selectedRadio.dataset.typePrix;
        const rawPrice = (selectedRadio.dataset.price ?? '').toString();
        const parsedPrice = parseFloat(rawPrice.replace(',', '.'));
        const basePrice = Number.isFinite(parsedPrice) ? parsedPrice : 0;
        const season = getSelectedSeason();
        const seasonMetrics = season ? getSeasonMetrics(season) : null;
        let computedPrice = null;
        let hintMessage = '';

        if (typePrix === 'pricing_plan') {
            if (!seasonMetrics) {
                hintMessage = 'اختر الموسم لحساب التكلفة الفعلية لهذه الخطة.';
            } else {
                computedPrice = calculatePlanPrice({
                    price: basePrice,
                    durationUnit: selectedRadio.dataset.planDurationUnit,
                    durationValue: parseInt(selectedRadio.dataset.planDurationValue || '1', 10),
                    pricingType: selectedRadio.dataset.planPricingType || selectedRadio.dataset.planType,
                    sessionsPerWeek: parseInt(selectedRadio.dataset.planSessions || selectedRadio.dataset.sessions || '1', 10)
                }, seasonMetrics);

                if (isFiniteNumber(computedPrice)) {
                    hintMessage = `التكلفة مقدّرة للفترة ${seasonMetrics.start} → ${seasonMetrics.end}.`;
                }
            }
        } else {
            if (!seasonMetrics) {
                hintMessage = 'اختر الموسم لحساب التكلفة الإجمالية لهذا الجدول.';
            } else {
                computedPrice = calculateFixedSchedulePrice(basePrice, seasonMetrics);
                const multiplier = seasonMetrics.monthsCharged ?? seasonMetrics.months ?? 1;
                const monthsLabel = multiplier >= 12
                    ? 'اشتراك سنوي (12 شهر)'
                    : `اشتراك ${multiplier} شهر`;
                hintMessage = `${monthsLabel}: ${multiplier} × السعر الشهري.`;
            }
        }

        const appliedPrice = isFiniteNumber(computedPrice) ? computedPrice : null;

        totalPriceInput.value = isFiniteNumber(appliedPrice) ? formatPrice(appliedPrice) : '';
        if (priceHint) {
            priceHint.textContent = hintMessage;
        }

        if (pricingCard && pricingCard.style.display !== 'none' && planPrice) {
            planPrice.textContent = isFiniteNumber(appliedPrice) ? formatPrice(appliedPrice) : '-';
        }
    };

    const updateSelection = (radio) => {
        updatePlanCard(radio);
        updateTotals();
    };

    if (!scheduleRadios.length) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            alert('⚠ لا توجد جداول متاحة حالياً. يرجى المحاولة لاحقاً.');
        });
        return;
    }

    scheduleRadios.forEach(radio => {
        radio.addEventListener('change', () => updateSelection(radio));
    });

    const firstEnabled = scheduleRadios.find(radio => !radio.disabled);
    if (firstEnabled) {
        firstEnabled.checked = true;
        updateSelection(firstEnabled);
    } else if (priceHint) {
        priceHint.textContent = 'لا توجد جداول متاحة حالياً.';
    }

    if (seasonSelect) {
        seasonSelect.addEventListener('change', updateTotals);
    }

    form.addEventListener('submit', function (e) {
        const selectedSchedule = document.querySelector('input[name="schedule_id"]:checked');
        if (!selectedSchedule) {
            e.preventDefault();
            alert('⚠ يرجى اختيار جدول قبل تأكيد الحجز.');
            return;
        }

        if (selectedSchedule.dataset.typePrix === 'pricing_plan') {
            const season = getSelectedSeason();
            if (!season) {
                e.preventDefault();
                alert('⚠ يرجى اختيار الموسم لحساب سعر الخطة.');
            }
        }
    });
});
</script>
@endpush
