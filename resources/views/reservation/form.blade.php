@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right">

    {{-- 🧾 معلومات المستعمل و الحجز --}}
    <div class="card shadow-sm p-3 rounded-4 mb-4">
        <h5 class="fw-bold text-primary mb-3">🔹 معلومات الحجز</h5>

        <input type="hidden" name="complex_activity_id" value="{{ $complexActivity->id }}" form="reserveForm">
        <input type="hidden" id="selected_slots" name="selected_slots" form="reserveForm">
        <input type="hidden" name="type_client" value="{{ auth()->user()->type_client }}" form="reserveForm">

        <div class="row">

            {{-- اسم المستعمل --}}
            <div class="col-md-6 mb-3">
                <label class="fw-bold">👤 اسم المستعمل</label>
                <input class="form-control bg-light" value="{{ auth()->user()->name }}" readonly>

                <div class="mb-3">
    <label class="fw-bold">🏷️ نوع المستعمل</label>
    <input class="form-control bg-light"
           value="@if(auth()->user()->type == 'person')
                        فرد
                  @elseif(auth()->user()->type == 'club')
                        نادي
                  @else
                        مؤسسة
                  @endif"
           readonly>
</div>
            </div>
{{-- نوع المستعمل --}}
{{-- نوع المستعمل --}}



            {{-- الفئة العمرية إن كان زبون فردي --}}
            @if(auth()->user()->type_client == 'person')
            <div class="col-md-6 mb-3">
                <label class="fw-bold">🎯 الفئة العمرية</label>
                <input class="form-control bg-light" value="{{ auth()->user()->ageCategory->name }}" readonly>
            </div>
            @endif

            {{-- المركب --}}
            <div class="col-md-6 mb-3">
                <label class="fw-bold">🏟️ المركب</label>
                <input class="form-control bg-light" value="{{ $complex->nom }}" readonly>
                 <div class="col-md-6 mb-3">
                <label class="fw-bold">🤸 النشاط</label>
                <input class="form-control bg-light" value="{{ $activity->title }}" readonly>
            </div>
            </div>

            {{-- النشاط --}}
           

            {{-- الموسم --}}
            <div class="col-md-6 mb-3">
                <label class="fw-bold">📅 الموسم</label>
                <select class="form-select" name="season_id" form="reserveForm" required>
                    <option disabled selected>اختر موسماً</option>
                    @foreach($seasons as $season)
                        <option value="{{ $season->id }}">{{ $season->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="alert alert-info py-2 text-center fw-bold">
            💰 سيتم احتساب السعر تلقائيًا بناءً على عدد الساعات المختارة
        </div>
    </div>

    {{-- 🗓️ التقويم --}}
    <div class="card shadow-sm p-3 rounded-4 mb-4">
        <h5 class="fw-bold text-secondary mb-3">🗓️ اختر الساعات المناسبة لك</h5>
        <div id="calendar"></div>
    </div>
{{-- 💸 خطة التسعير --}}
<div class="card shadow-sm p-3 rounded-4 mb-4" id="pricingCard" >
    <h5 class="fw-bold text-dark mb-3">📌 خطة التسعير المعتمدة</h5>

    <table class="table table-bordered table-striped text-center">
        <tbody>
            <tr>
                <td class="fw-bold">النوع</td>
                <td id="plan_type"></td>
            </tr>
            <tr>
                <td class="fw-bold">عدد الساعات/الأسبوع</td>
                <td id="plan_hours"></td>
            </tr>
            <tr>
                <td class="fw-bold">السعر</td>
                <td id="plan_price"></td>
            </tr>
            <tr>
                <td class="fw-bold">مدة الاشتراك</td>
                <td id="plan_duration"></td>
            </tr>
        </tbody>
    </table>
</div>
{{-- رسائل الجلسة (نجاح/خطأ) --}}
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

{{-- أخطاء التحقق من صحة النموذج --}}
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

    {{-- 💵 السعر و التأكيد --}}
    <div class="card shadow-sm p-4 rounded-4">
        <form action="{{ route('reservation.store') }}" method="POST" id="reserveForm">
            @csrf

            <label class="fw-bold">🔥 السعر الإجمالي (دج)</label>
            <input type="text"
                id="total_price"
                class="form-control bg-light text-center fw-bold fs-5 mb-3"
                readonly>

            <button class="btn btn-success w-100 fs-5 fw-bold">
                ✔ تأكيد الحجز
            </button>
        </form>
    </div>

</div>


@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<style>
    /* تصميم عام */
.fc-theme-standard .fc-scrollgrid {
    border-radius: 10px;
    overflow: hidden;
}

.selected-slot {
    background-color: #007bff !important;
    border-color: #0056b3 !important;
    color: #fff !important;
    font-weight: bold;
}

/* 🔥 إخفاء التاريخ نهائياً */
.fc-col-header-cell-cushion > span:not(:first-child) {
    display: none !important;
}

/* تكبير و تنسيق اسم اليوم */
.fc-col-header-cell-cushion span:first-child {
    font-size: 18px;
    font-weight: bold;
    color: #000;
}

/* تقليل ارتفاع الترويسة */
.fc-col-header {
    height: 35px !important;
}
/* اخفاء كل محتوى رأس العمود */
.fc-col-header-cell-cushion * {
    display: none !important;
}
.schedule-allowed {
    opacity: .5 !important;
}

/* إظهار فقط أسماء الأيام بالعربية */
.fc-col-header-cell-cushion span:contains("الأحد"),
.fc-col-header-cell-cushion span:contains("الاثنين"),
.fc-col-header-cell-cushion span:contains("الثلاثاء"),
.fc-col-header-cell-cushion span:contains("الأربعاء"),
.fc-col-header-cell-cushion span:contains("الخميس"),
.fc-col-header-cell-cushion span:contains("الجمعة"),
.fc-col-header-cell-cushion span:contains("السبت") {
    display: inline !important;
    font-size: 18px;
    font-weight: bold;
    color: #000;
}


</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
    document.getElementById('reserveForm').addEventListener('submit', function() {


         if (selectedSlots.length === 0) {
        e.preventDefault();
        alert("⚠ يرجى اختيار توقيت واحد على الأقل!");
        return;
    }


    document.getElementById('selected_slots').value = JSON.stringify(selectedSlots);
});
document.addEventListener('DOMContentLoaded', function () {

    const price = {{ $complexActivity->price ?? 0 }};
    let selectedSlots = [];
    function updatePricingCard() {
    const hours = selectedSlots.length;
    const plans = @json($pricingPlans ?? []);
    const userReservations = @json($userReservations ?? []);
    if (plans.length === 0) return;

    // اختيار خطة التسعير المناسبة
    let plan = plans.find(p => p.sessions_per_week == hours);

    if (!plan) {
        // إخفاء البطاقة إذا لا يوجد خطة مناسبة
        document.getElementById('pricingCard').style.display = 'none';
        return;
    }

    // عرض تفاصيل الخطة
    document.getElementById('plan_type').innerText = plan.pricing_type;
    document.getElementById('plan_hours').innerText = plan.sessions_per_week + " ساعات";
    document.getElementById('plan_price').innerText = plan.price + " دج";
    document.getElementById('plan_duration').innerText =
        plan.duration_value + " " + (plan.duration_unit == 'month' ? "شهر" : "موسم");

    document.getElementById('pricingCard').style.display = 'block';
}
function updateInputs() {
    document.getElementById('selected_slots').value = JSON.stringify(selectedSlots);
    updatePrice();
    updatePricingCard(); // 👈 إضافة هنا
}

    // alert($schedules);
    // 🔁 تحديث الحقل المخفي + السعر
    function updateInputs() {
        document.getElementById('selected_slots').value = JSON.stringify(selectedSlots);
        updatePrice();
    }

    function updatePrice() {
        const totalHours = selectedSlots.length;
        document.getElementById('total_price').value = totalHours > 0
            ? (totalHours * price) + " دج"
            : "";
    }

    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'timeGridWeek',
        locale: 'ar',
        direction: 'rtl',
        firstDay: 0,
        selectable: true,          // 👈 تفعيل select
        selectMirror: true,
        slotMinTime: "08:00:00",
        slotMaxTime: "22:00:00",
        slotDuration: "01:00:00",  // 👈 كل خانة = ساعة
        allDaySlot: false,
        height: "auto",




        

        // 🟦 عند تحديد خانة (select) نضيف ساعة جديدة
  select: function(info) {
    const dateStr = info.startStr.slice(0, 10);
    const slotStart = info.startStr;
    const slotEnd   = info.endStr;

    const dayIndex = new Date(info.startStr).getDay(); // رقم اليوم 0-6
    const hoursStart = info.startStr.slice(11,16); // HH:MM
    const hoursEnd   = info.endStr.slice(11,16);
     const schedules = @json($schedules ?? []);

//if (schedules.length > 0) {
//    alert(JSON.stringify(schedules, null, 2));
//} else {
   // alert("⚠️ لا توجد بيانات في schedules !");
//}


    // البحث عن schedule مطابق في قاعدة البيانات
    const schedule = schedules.find(s =>
        s.day_number == dayIndex 
      //  s.heure_debut.slice(0,5) === hoursStart &&
       // s.heure_fin.slice(0,5) === hoursEnd
    );

    if (!schedule) {
        console.error("❌ Schedule introuvable!", dayIndex, hoursStart, hoursEnd);
        alert("⚠ خطأ: التوقيت غير مسجل في قاعدة البيانات!");
       calendar.unselect();
        return;
    }

    const slot = {
        date: dateStr,
        start: slotStart,
        end:   slotEnd,
        schedule_id: schedule.id // 🎯 هنا المفتاح
    };

    const daySlots = selectedSlots.filter(s => s.date === dateStr);

    if (!selectedSlots.some(s => s.start === slot.start)) {

        const uniqueDays = [...new Set(selectedSlots.map(s => s.date))];

        if (!uniqueDays.includes(dateStr) && uniqueDays.length >= 4) {
            alert("⚠ الحد الأقصى 4 أيام في الأسبوع");
            calendar.unselect();
            return;
        }

        if (daySlots.length >= 2) {
            alert("⚠ يمكنك اختيار ساعتين فقط في اليوم");
            calendar.unselect();
            return;
        }

        selectedSlots.push(slot);

        calendar.addEvent({
            start: slotStart,
            end:   slotEnd,
            classNames: ['selected-slot']
        });
    }

    updateInputs();
    calendar.unselect();
},


        // 🟥 عند الضغط على حدث، نلغيه (deselection)
        eventClick: function(info) {
            const idStart = info.event.startStr; // نفس الـ start الذي خزّناه
            selectedSlots = selectedSlots.filter(s => s.start !== idStart);
            info.event.remove(); // إزالة الحدث من التقويم
            updateInputs();
        }
    });

// 🟢 جلب جدول الساعات من الـ Backend
const schedules = @json($schedules ?? []);

console.log("📌 ساعات متاحة من الـDB:", schedules);

// 🟢 رسم الساعات المتاحة فور تحميل التقويم
schedules.forEach(s => {
    const calendarDate = calendar.getDate(); // اليوم الحالي
    const startOfWeek = new Date(calendarDate);
    startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay()); // بداية الأسبوع

    // ⏱️ إعداد تاريخ ووقت الحدث
    const start = new Date(startOfWeek);
    start.setDate(start.getDate() + parseInt(s.day_number));
    start.setHours(...s.heure_debut.split(":"));

    const end = new Date(startOfWeek);
    end.setDate(end.getDate() + parseInt(s.day_number));
    end.setHours(...s.heure_fin.split(":"));

    // 🎨 رسم الحدث كخلفية خضراء هادئة
    calendar.addEvent({
        start: start,
        end: end,
        display: 'background',
        backgroundColor: '#27ae60',
        borderColor: '#145a32',
        classNames: ['schedule-allowed']
    });
});





    calendar.render();
});
</script>
@endpush
