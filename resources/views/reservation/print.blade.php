<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة الحجز</title>

<style>
/* ===== Reset ===== */
html, body {
    width: 210mm;
    height: 297mm;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden;
}

* {
    box-sizing: border-box;
}

/* ===== Page Setup ===== */
@page {
    size: A4;
    margin: 0;   /* 🔴 إلغاء هوامش المتصفح */
}

/* ===== Body ===== */
body {
    font-family: "Cairo", sans-serif;
    direction: rtl;
    background: #fff;
}

/* ===== A4 Container ===== */
.a4 {
    width: 210mm;
    height: 297mm;
    padding: 15mm;       /* هوامش داخلية فقط */
    margin: 0 !important;
    overflow: hidden;    /* يمنع صفحة ثانية */
    background: #fff;
}

/* ===== Titles ===== */
h2, h3 {
    text-align: center;
    margin: 4px 0;
    font-weight: 700;
}

/* ===== Sections ===== */
.section {
    margin-bottom: 10px;
}

/* ===== Box ===== */
.box {
    border: 1px solid #000;
    padding: 10px;
    border-radius: 6px;
}

/* ===== Tables ===== */
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

table th,
table td {
    border: 1px solid #000;
    padding: 5px;
    text-align: center;
}

/* ===== QR ===== */
.qr {
    margin-top: 10px;
    text-align: center;
}

.qr img {
    max-width: 95px;
}

/* ===== Footer ===== */
.footer {
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    font-size: 13px;
}

/* ===== Print ===== */
@media print {
    body {
        margin: 0 !important;
    }

    button,
    .no-print {
        display: none !important;
    }
}
</style>


</head>

<body>

@php
    $user = $reservation->user;

    // اسم المستفيد
    $beneficiaryName =
        ($user?->type === 'person')
            ? $user->name
            : ($user->name ?? '—');

    // الهاتف
    $phone =
        $user?->person->phone
        ?? $user->phone
        ?? '';

    // بيانات QR
    $qrData = [
        'reservation_id' => $reservation->id,
        'beneficiary' => $beneficiaryName,
        'type' => $user?->type,
        'phone' => $phone,
        'activity' => $reservation->complexActivity?->activity?->title,
        'start_date' => $reservation->start_date,
        'end_date' => $reservation->end_date,
    ];
@endphp

<div class="a4">

    {{-- العنوان --}}
    <h2>وصل حجز</h2>
    <h3>{{ $reservation->complexActivity?->activity?->title ?? '' }}</h3>

    {{-- معلومات المستفيد --}}
    <div class="section box">
        <strong>المستفيد:</strong> {{ $beneficiaryName }} <br>
        <strong>نوع الحساب:</strong> {{ $user?->type }} <br>
        <strong>الهاتف:</strong> {{ $phone }}
    </div>

    {{-- معلومات الحجز --}}
    <div class="section box">
        <strong>تاريخ البداية:</strong> {{ $reservation->start_date }} <br>
        <strong>تاريخ النهاية:</strong> {{ $reservation->end_date }} <br>
        <strong>السعر:</strong> {{ number_format($reservation->total_price ?? 0) }} دج
    </div>

    {{-- الجدول الزمني --}}
    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>اليوم</th>
                    <th>من</th>
                    <th>إلى</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservation->time_slots ?? [] as $slot)
                    <tr>
                        <td>{{ $reservation->getDayName($slot['day_number']) }}</td>
                        <td>{{ $slot['start'] }}</td>
                        <td>{{ $slot['end'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- QR Code --}}
    <div class="qr">
        <h4>QR معلومات الحجز</h4>
        <img
            src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode(json_encode($qrData)) }}"
            alt="QR Code">
    </div>

    {{-- التوقيع --}}
    <div class="footer">
        <div>توقيع المستفيد</div>
        <div>توقيع الإدارة</div>
    </div>

    {{-- زر الطباعة --}}
    <div style="text-align:center; margin-top:30px">
        <button onclick="window.print()">🖨️ طباعة</button>
    </div>

</div>

<script>
    // طباعة تلقائية إن أردت
    // window.onload = function () {
    //     window.print();
    // };
</script>

</body>
</html>
