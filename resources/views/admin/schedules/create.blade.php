@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">

    <h3 class="fw-bold mb-4">➕ إضافة جدول جديد</h3>

    @if ($errors->any())
        <div class="alert alert-danger fw-bold">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>⚠ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.schedules.store') }}" method="POST" id="scheduleForm">
        @csrf

        {{-- المركب --}}
        <div class="mb-3">
            <label class="fw-bold">🏟️ المركب</label>
            <select name="complex_id" id="complex" class="form-control" required>
                <option value="">-- اختر المركب --</option>
                @foreach($complexes as $c)
                    <option value="{{ $c->id }}">{{ $c->nom }}</option>
                @endforeach
            </select>
        </div>

        {{-- النشاط --}}
        <div class="mb-3">
            <label class="fw-bold">🤸 النشاط</label>
            <select name="activity_id" id="activity" class="form-control" required>
                <option value="">-- اختر النشاط --</option>
                @foreach($activities as $a)
                    <option value="{{ $a->id }}">{{ $a->title }}</option>
                @endforeach
            </select>
        </div>

        <input type="hidden" name="complex_activity_id" id="complex_activity_id">

        {{-- الفئة العمرية --}}
        <div class="mb-3">
            <label class="fw-bold">🎯 الفئة العمرية</label>
            <select name="age_category_id" class="form-control" required>
                @foreach($ageCategories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- المجموعة --}}
        <div class="mb-3">
            <label class="fw-bold">👥 المجموعة</label>
            <input type="text" name="groupe" class="form-control" required>
        </div>

        {{-- الجنس --}}
        <div class="mb-3">
            <label class="fw-bold">الجنس</label>
            <select name="sex" class="form-control">
                <option value="H">ذكور</option>
                <option value="F">إناث</option>
                <option value="X">مختلط</option>
            </select>
        </div>

        {{-- عدد الأماكن --}}
        <div class="mb-3">
            <label class="fw-bold">عدد الأماكن</label>
            <input type="number" name="nbr" class="form-control">
        </div>

        {{-- نوع التسعيرة --}}
        <div class="mb-3">
            <label class="fw-bold">💰 نوع التسعيرة</label>
            <select name="type_prix" id="type_prix" class="form-control" required>
                <option value="pricing_plan">حسب خطة التسعير</option>
                <option value="fix">سعر ثابت</option>
            </select>
        </div>

        {{-- السعر الثابت --}}
        <div class="mb-3" id="fixed_price_box" style="display:none;">
            <label class="fw-bold">💵 السعر الثابت (دج)</label>
            <input type="number" name="price" class="form-control">
        </div>

        {{-- Time Slots --}}
        <input type="hidden" name="time_slots" id="time_slots">

        <div class="alert alert-info text-center fw-bold">
            🟥 الأوقات الحمراء = مشغولة مسبقًا <br>
            🟦 الأوقات الزرقاء = اختياراتك
        </div>

        <div class="card p-3 shadow-sm mb-4">
            <div id="calendar"></div>
        </div>

        <button class="btn btn-success w-100 py-2 fw-bold">💾 حفظ الجدول</button>
    </form>

</div>


@endsection
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">

<style>
.selected-slot {
    background: #007bff !important;
    color: white !important;
    border-color: #004a99 !important;
}
/* تقليل ارتفاع آخر سطر في FullCalendar */
.fc-scroller {
    padding-bottom: 0 !important;
    margin-bottom: 0 !important;
}
/* إصلاح تمدد آخر صف في تقويم FullCalendar */
.fc-timegrid-slot-minor,
.fc-timegrid-slot-major {
    height: 28px !important;   /* اجعل السطر صغير */
    min-height: 28px !important;
    max-height: 28px !important;
    padding: 0 !important;
}

/* حل خاص لمنع الصف الأخير من التمدد */
.fc-timegrid-slots tr:last-child td {
    height: 20px !important;
    min-height: 20px !important;
    max-height: 20px !important;
    padding: 0 !important;
}

/* منع FullCalendar من صنع مساحة فارغة كبيرة أسفل */
.fc-timegrid-body {
    height: auto !important;
}

.fc-scroller-liquid {
    max-height: 620px !important; /* يمكنك تعديل الرقم */
}
.fc-bg-event {
    background-color: #dc3545 !important;
    opacity: 0.45 !important;
}

</style>
@endpush

@push('js')

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
let selectedSlots = [];
let calendar;

function updateHiddenField() {
    document.getElementById("time_slots").value = JSON.stringify(selectedSlots);
}

document.addEventListener('DOMContentLoaded', function () {

    calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'timeGridWeek',
        locale: 'ar',
        direction: 'rtl',
        firstDay: 0,
        selectable: true,
        selectOverlap: false, // ❌ يمنع التداخل
        slotMinTime: "05:00:00",
        slotMaxTime: "23:00:00",
        slotDuration: "01:00:00",
        allDaySlot: false,
        expandRows: false,
        height: "auto",

        select(info) {

            // ❌ منع اختيار وقت مشغول
            const conflict = calendar.getEvents().some(ev =>
                ev.display === 'background' &&
                info.start < ev.end &&
                info.end > ev.start
            );

            if (conflict) {
                alert('⛔ هذا التوقيت مشغول مسبقاً');
                calendar.unselect();
                return;
            }

            const slot = {
                day_number: new Date(info.start).getDay(),
                start: info.startStr.slice(11,16),
                end:   info.endStr.slice(11,16)
            };

            selectedSlots.push(slot);

            calendar.addEvent({
                start: info.start,
                end: info.end,
                classNames: ['selected-slot'],
                title: 'توقيت مختار'
            });

            updateHiddenField();
            calendar.unselect();
        },

        eventClick(info) {
            if (info.event.display === 'background') return;

            selectedSlots = selectedSlots.filter(s =>
                s.start !== info.event.startStr.slice(11,16)
            );
            info.event.remove();
            updateHiddenField();
        }
    });

    calendar.render();
});

// ===============================
// تحميل الأوقات المشغولة تلقائياً
// ===============================
document.getElementById("complex").addEventListener("change", loadOccupied);
document.getElementById("activity").addEventListener("change", loadOccupied);

function loadOccupied() {

    const complex = document.getElementById("complex").value;
    const activity = document.getElementById("activity").value;

    if (!complex || !activity) return;

    // 🧹 حذف الأحداث السابقة
    calendar.getEvents().forEach(e => e.remove());

    fetch(`{{ route('admin.schedules.occupied') }}?complex_id=${complex}&activity_id=${activity}`)
        .then(res => res.json())
        .then(events => {

            events.forEach(ev => {
                calendar.addEvent(ev);
            });

        });
}
</script>


@endpush
