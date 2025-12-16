@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">

    <h3 class="fw-bold mb-4">✏ تعديل جدول</h3>

    {{-- عرض أخطاء التحقق --}}
    @if ($errors->any())
        <div class="alert alert-danger fw-bold">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>⚠ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


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
        <input type="hidden" name="complex_activity_id" id="complex_activity_id"
               value="{{ $schedule->complex_activity_id }}">


        {{-- الفئة العمرية --}}
        <div class="mb-3">
            <label class="fw-bold">🎯 الفئة العمرية</label>
            <select name="age_category_id" class="form-control" required>
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
            <input type="text" name="groupe" class="form-control" value="{{ $schedule->groupe }}" required>
        </div>

        {{-- الجنس --}}
        <div class="mb-3">
            <label class="fw-bold">الجنس</label>
            <select name="sex" class="form-control">
                <option value="H" {{ $schedule->sex == 'H' ? 'selected' : '' }}>ذكور</option>
                <option value="F" {{ $schedule->sex == 'F' ? 'selected' : '' }}>إناث</option>
                <option value="X" {{ $schedule->sex == 'X' ? 'selected' : '' }}>مختلط</option>
            </select>
        </div>

        {{-- العدد --}}
        <div class="mb-3">
            <label class="fw-bold">عدد الأماكن</label>
            <input type="number" name="nbr" class="form-control" value="{{ $schedule->nbr }}">
        </div>

        {{-- نوع السعر --}}
        <div class="mb-3">
            <label class="fw-bold">💰 نوع التسعير</label>
            <select name="type_prix" id="type_prix" class="form-control">
                <option value="pricing_plan" {{ $schedule->type_prix == 'pricing_plan' ? 'selected' : '' }}>
                    حسب خطة التسعير
                </option>
                <option value="fix" {{ $schedule->type_prix == 'fix' ? 'selected' : '' }}>
                    سعر ثابت
                </option>
            </select>
        </div>

        {{-- السعر الثابت --}}
        <div class="mb-3" id="fixed_price_box"
            style="display: {{ $schedule->type_prix == 'fix' ? 'block' : 'none' }};">
            <label class="fw-bold">💵 السعر الثابت (دج)</label>
            <input type="number" name="price" class="form-control" value="{{ $schedule->price }}">
        </div>

        {{-- user_id --}}
        <div class="mb-3">
            <label class="fw-bold">🔑 إسناد الجدول إلى (اختياري)</label>
            <select name="user_id" class="form-control">
                <option value="">— لا أحد —</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ $schedule->user_id == $u->id ? 'selected' : '' }}>
                        {{ $u->name }} ({{ $u->type }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- time slots --}}
        <input type="hidden" name="time_slots" id="time_slots" value="{{ $schedule->time_slots }}">


        <div class="alert alert-info fw-bold text-center">
            🗓️ يمكنك تعديل الفترات الزمنية من التقويم أسفله
        </div>

        <div class="card p-3 shadow-sm mb-4">
            <div id="calendar"></div>
        </div>

        <button class="btn btn-primary w-100 py-2 fw-bold">💾 حفظ التعديلات</button>

    </form>

</div>
@endsection


@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<style>
.selected-slot {
    background:#007bff !important;
    color:white !important;
    border-color:#004a99 !important;
}
</style>
@endpush


@push('js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>

// ✔ تحويل JSON string إلى Array إذا لزم الأمر
let selectedSlots = {!! $schedule->time_slots !!};
if (typeof selectedSlots === "string") {
    selectedSlots = JSON.parse(selectedSlots);
}

updateHiddenField();

// ✔ تحديث الحقل المخفي
function updateHiddenField() {
    document.getElementById("time_slots").value = JSON.stringify(selectedSlots);
}

// ✔ إظهار/إخفاء السعر الثابت
document.getElementById("type_prix").addEventListener("change", function(){
    document.getElementById("fixed_price_box").style.display =
        this.value === "fix" ? "block" : "none";
});


document.addEventListener('DOMContentLoaded', function () {

    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'timeGridWeek',
        locale: 'ar',
        direction: 'rtl',
        selectable: true,
        slotMinTime: "08:00:00",
        slotMaxTime: "22:00:00",
        slotDuration: "01:00:00",
        contentHeight: "auto",       // ❗ يمنع FullCalendar من تمديد آخر خط
    height: "auto",              // ❗ يجعل الارتفاع حسب المحتوى فقط

    expandRows: false,           // ❗ أهم سطر!! يمنع تمديد الصف الأخير نهائياً

        select(info) {
            const day = new Date(info.start).getDay();
            const start = info.startStr.slice(11,16);
            const end   = info.endStr.slice(11,16);

            selectedSlots.push({ day_number: day, start, end });

            calendar.addEvent({ start: info.start, end: info.end, classNames: ['selected-slot'] });

            updateHiddenField();
            calendar.unselect();
        },

        eventClick(info) {
            const day = new Date(info.event.start).getDay();
            const start = info.event.startStr.slice(11,16);

            selectedSlots = selectedSlots.filter(s => !(s.day_number === day && s.start === start));

            info.event.remove();
            updateHiddenField();
        }
    });

    // ✔ رسم الساعات القديمة
    selectedSlots.forEach(s => {
        const today = new Date().getDay();
        const d = new Date();

        d.setDate(d.getDate() + (s.day_number - today));
        const dayString = d.toISOString().slice(0,10);

        calendar.addEvent({
            start: dayString + "T" + s.start + ":00",
            end:   dayString + "T" + s.end   + ":00",
            classNames: ['selected-slot']
        });
    });


    calendar.render();
});


// ✔ تحديث complex_activity عند تغيير المركب أو النشاط
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
