@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">

    <h3 class="fw-bold mb-4">➕ إضافة جدول جديد</h3>

    {{-- فورم إنشاء جدول --}}
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

        {{-- hidden --}}
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

        {{-- العدد --}}
        <div class="mb-3">
            <label class="fw-bold">عدد الأماكن</label>
            <input type="number" name="nbr" class="form-control">
        </div>

        {{-- time_slots --}}
        <input type="hidden" name="time_slots" id="time_slots">

        <div class="alert alert-info fw-bold text-center">
            🗓️ اختر الأيام والساعات الخاصة بالمجموعة من التقويم أسفله
        </div>

        <div class="card p-3 shadow-sm mb-4">
            <div id="calendar"></div>
        </div>
        <div class="mb-3">
    <label class="fw-bold">💰 نوع التسعيرة</label>
    <select name="type_prix" id="type_prix" class="form-control" required>
        <option value="pricing_plan">حسب خطة التسعير</option>
        <option value="fixed">سعر ثابت</option>
    </select>
</div>

<div class="mb-3" id="fixed_price_box" style="display:none;">
    <label class="fw-bold">💵 السعر الثابت (دج)</label>
    <input type="number" name="price" class="form-control">
</div>


        <button class="btn btn-success w-100 py-2 fw-bold">💾 حفظ الجدول</button>
    </form>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">

<style>
.selected-slot {
    background-color: #007bff !important;
    border-color: #004a99 !important;
    color: white !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
let selectedSlots = [];

function updateHiddenField() {
    document.getElementById("time_slots").value = JSON.stringify(selectedSlots);
}

document.addEventListener('DOMContentLoaded', function () {

    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'timeGridWeek',
        locale: 'ar',
        direction: 'rtl',
        firstDay: 0,
        selectable: true,
        slotMinTime: "08:00:00",
        slotMaxTime: "22:00:00",
        slotDuration: "01:00:00",
        allDaySlot: false,

        select: function(info) {

            const start = info.startStr;
            const end   = info.endStr;
            const day   = new Date(start).getDay();

            const slot = {
                day_number: day,
                start: start.slice(11, 16),
                end: end.slice(11, 16)
            };

            selectedSlots.push(slot);

            calendar.addEvent({
                start: info.start,
                end: info.end,
                classNames: ['selected-slot']
            });

            updateHiddenField();
            calendar.unselect();
        },

        eventClick: function(info) {
            const start = info.event.startStr.slice(11, 16);

            selectedSlots = selectedSlots.filter(s => s.start !== start);
            info.event.remove();

            updateHiddenField();
        }
    });

    calendar.render();
});


// 🔍 AJAX لجلب complex_activity_id
document.getElementById("complex").addEventListener("change", loadCombo);
document.getElementById("activity").addEventListener("change", loadCombo);

function loadCombo() {
    const complex = document.getElementById("complex").value;
    const activity = document.getElementById("activity").value;

    if (!complex || !activity) return;

    fetch(`/admin/get-complex-activity?complex_id=${complex}&activity_id=${activity}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById("complex_activity_id").value = data.id ?? "";
        });
}
</script>
@endpush
