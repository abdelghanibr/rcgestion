@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">

    <h3 class="fw-bold mb-4">✏ تعديل جدول رقم {{ $schedule->id }}</h3>

    <form action="{{ route('admin.schedules.update', $schedule->id) }}" method="POST" id="scheduleForm">
        @csrf
        @method('PUT')

        {{-- المركب --}}
        <div class="mb-3">
            <label class="fw-bold">🏟️ المركب</label>
            <select name="complex_id" id="complex" class="form-control" required>
                @foreach($complexes as $c)
                    <option value="{{ $c->id }}" {{ $selected_complex == $c->id ? 'selected' : '' }}>
                        {{ $c->nom }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- النشاط --}}
        <div class="mb-3">
            <label class="fw-bold">🤸 النشاط</label>
            <select name="activity_id" id="activity" class="form-control" required>
                @foreach($activities as $a)
                    <option value="{{ $a->id }}" {{ $selected_activity == $a->id ? 'selected' : '' }}>
                        {{ $a->title }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- complex_activity_id --}}
        <input type="hidden" name="complex_activity_id" id="complex_activity_id" value="{{ $schedule->complex_activity_id }}">

        {{-- الفئة العمرية --}}
        <div class="mb-3">
            <label class="fw-bold">🎯 الفئة العمرية</label>
            <select name="age_category_id" class="form-control">
                @foreach($ageCategories as $cat)
                    <option value="{{ $cat->id }}" {{ $schedule->age_category_id == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- المجموعة --}}
        <div class="mb-3">
            <label class="fw-bold">👥 المجموعة</label>
            <input type="text" name="groupe" value="{{ $schedule->groupe }}" class="form-control">
        </div>

        {{-- الجنس --}}
        <div class="mb-3">
            <label class="fw-bold">الجنس</label>
            <select name="sex" class="form-control">
                <option value="H" {{ $schedule->sex=='H' ? 'selected' : '' }}>ذكور</option>
                <option value="F" {{ $schedule->sex=='F' ? 'selected' : '' }}>إناث</option>
                <option value="X" {{ $schedule->sex=='X' ? 'selected' : '' }}>مختلط</option>
            </select>
        </div>

        {{-- العدد --}}
        <div class="mb-3">
            <label class="fw-bold">عدد الأماكن</label>
            <input type="number" name="nbr" class="form-control" value="{{ $schedule->nbr }}">
        </div>

        {{-- time_slots --}}
        <input type="hidden" name="time_slots" id="time_slots">

        <div class="alert alert-info fw-bold text-center">
            🗓️ عدّل الأيام والساعات الخاصة بالمجموعة من التقويم أسفله
        </div>

        <div class="card p-3 shadow-sm mb-4">
            <h5 class="fw-bold mb-2">📅 التقويم – تعديل الساعات</h5>
            <div id="calendar"></div>
        </div>

        <button class="btn btn-primary w-100 py-2 fw-bold">💾 حفظ التعديلات</button>

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
    font-weight: bold;
}
</style>
@endpush


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>

// ----------------------------
// 1) معالجة time_slots من الـ DB
// ----------------------------
let selectedSlots = [];

try {
    const raw = @json($schedule->time_slots ?? '[]');
    selectedSlots = typeof raw === "string" ? JSON.parse(raw) : raw;
} catch (e) {
    selectedSlots = [];
}

function updateHiddenField() {
    document.getElementById("time_slots").value = JSON.stringify(selectedSlots);
}


// ----------------------------
// 2) إعداد التقويم
// ----------------------------
document.addEventListener('DOMContentLoaded', function () {

    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'timeGridWeek',
        locale: 'ar',
        direction: 'rtl',
        firstDay: 0,
        selectable: true,
        slotMinTime: "08:00:00",
        slotMaxTime: "22:00:00",
        slotDuration: "01:00",
        allDaySlot: false,

        // إضافة ساعة جديدة
        select(info) {
            const slot = {
                day_number: new Date(info.startStr).getDay(),
                start: info.startStr.slice(11, 16),
                end: info.endStr.slice(11, 16)
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

        // حذف ساعة عند الضغط عليها
        eventClick(info) {
            const st = info.event.startStr.slice(11, 16);
            const dn = new Date(info.event.startStr).getDay();

            selectedSlots = selectedSlots.filter(s => !(s.start === st && s.day_number === dn));

            info.event.remove();
            updateHiddenField();
        }
    });

    // ----------------------------
    // 3) رسم الساعات القديمة من قاعدة البيانات
    // ----------------------------
    selectedSlots.forEach(s => {

        if (!s || !s.start || !s.end) return;

        const today = calendar.getDate();
        const base = new Date(today);
        base.setDate(base.getDate() - base.getDay() + s.day_number);

        const start = new Date(base);
        const end = new Date(base);

        start.setHours(...s.start.split(':'));
        end.setHours(...s.end.split(':'));

        calendar.addEvent({
            start,
            end,
            classNames: ['selected-slot']
        });
    });

    updateHiddenField();
    calendar.render();
});


// ----------------------------
// 4) AJAX لجلب complex_activity_id
// ----------------------------
document.getElementById("complex").addEventListener("change", loadCombo);
document.getElementById("activity").addEventListener("change", loadCombo);

function loadCombo() {
    const c = document.getElementById("complex").value;
    const a = document.getElementById("activity").value;
    if (!c || !a) return;

    fetch(`/admin/get-complex-activity?complex_id=${c}&activity_id=${a}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById("complex_activity_id").value = data.id ?? "";
        });
}

</script>
@endpush
