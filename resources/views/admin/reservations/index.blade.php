@extends('layouts.app')

@section('content')
<div class="container-fluid" style="direction: rtl">

{{-- ================= FILTRES (ADMIN ONLY) ================= --}}
@auth
@if(auth()->user()->type === 'admin')

<div class="card mb-3">
    <div class="card-body">

        <div class="d-flex flex-wrap gap-3 align-items-end">

            {{-- COMPLEX --}}
            <div style="min-width:220px">
                <label class="fw-bold mb-1">المركب</label>
                <select id="filterComplex" class="form-control">
                    <option value="">الكل</option>
                    @foreach($complexes as $complex)
                        <option value="{{ $complex->id }}">
                            {{ $complex->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ACTIVITY --}}
            <div style="min-width:220px">
                <label class="fw-bold mb-1">النشاط</label>
                <select id="filterActivity" class="form-control">
                    <option value="">الكل</option>
                    @foreach($activities as $activity)
                        <option value="{{ $activity->id }}">
                            {{ $activity->title }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

    </div>
</div>

@endif
@endauth

{{-- ================= MESSAGE SUCCESS ================= --}}
@if(session('success'))
    <div class="alert alert-success fw-bold">
        {{ session('success') }}
    </div>
@endif

{{-- ================= TABLE ================= --}}
<div class="card shadow-sm">
<div class="card-body">

<h4 class="mb-3 fw-bold">📅 قائمة الحجوزات</h4>

<table id="schedulesTable"
       class="table table-bordered table-hover align-middle text-center">
<thead class="table-dark">
<tr>
    <th>#</th>
    <th>المستخدم</th>
    <th>النوع</th>
    <th>المركب</th>
    <th>النشاط</th>
    
    <th>تاريخ البداية</th>
    <th>تاريخ النهاية</th>
    <th>الحصص (يوم / تاريخ / وقت)</th>
    <th>الأماكن</th>
    <th>السعر</th>
    <th>الحالة</th>
    <th>الدفع</th>
    <th>إجراءات</th>
</tr>
</thead>

<tbody>
@foreach($reservations as $r)
<tr
    data-complex="{{ $r->complexActivity->complex->id ?? '' }}"
    data-activity="{{ $r->complexActivity->activity->id ?? '' }}"
    data-user-type="{{ $r->user->type ?? '' }}"
>


<td>{{ $r->id }}</td>

<td>{{ $r->user->name ?? '—' }}</td>
<td>
    <span class="badge bg-info">
        {{ match($r->user->type ?? '') {
            'club'    => 'نادي',
            'company' => 'مؤسسة',
            default   => 'فرد'
        } }}
    </span>
</td>
<td>{{ $r->complexActivity->activity->title ?? '—' }}</td>

<td>{{ $r->complexActivity->complex->nom ?? '—' }}</td>


<td>{{ \Carbon\Carbon::parse($r->start_date)->format('Y-m-d') }}</td>
<td>{{ \Carbon\Carbon::parse($r->end_date)->format('Y-m-d') }}</td>



{{-- TIME SLOTS --}}
<td class="text-start">

@if(!empty($r->time_slots))
    @foreach($r->time_slots as $slot)
        @php
            $daysArabic = [
                0 => 'الأحد',
                1 => 'الإثنين',
                2 => 'الثلاثاء',
                3 => 'الأربعاء',
                4 => 'الخميس',
                5 => 'الجمعة',
                6 => 'السبت',
                7 => 'السبت',
            ];

            $dayName = $daysArabic[$slot['day_number'] ?? null] ?? '';
        @endphp

        <div class="badge bg-secondary d-block mb-1 text-start">
            📅 {{ $dayName }}<br>
            ⏱ {{ $slot['start'] ?? '?' }} → {{ $slot['end'] ?? '?' }}
        </div>
    @endforeach
@else
    <span class="text-muted">
        ⏱ {{ $r->start_time }} → {{ $r->end_time }}
    </span>
@endif




</td>

<td>{{ $r->qty_places }}</td>

<td>{{ number_format($r->total_price,0,',',' ') }} دج</td>

<td>
    @php
        $statusClass = match($r->statut) {
            'confirmee' => 'success',
            'annulee'   => 'danger',
            default     => 'warning',
        };
    @endphp
    <span class="badge bg-{{ $statusClass }}">
        {{ ucfirst($r->statut) }}
    </span>
</td>

<td>
    <span class="badge bg-{{ $r->payment_status==='paid'?'success':'secondary' }}">
        {{ $r->payment_status==='paid'?'مدفوع':'غير مدفوع' }}
    </span>
</td>

<td class="text-nowrap">
    <button class="btn btn-sm btn-outline-dark"
            onclick="printReservation({{ $r->id }})">🖨️</button>

    

    <form action="{{ route('reservations.destroy',$r) }}"
          method="POST" class="d-inline">
        @csrf @method('DELETE')
        <button onclick="return confirm('حذف؟')"
                class="btn btn-sm btn-danger">🗑</button>
    </form>
</td>

</tr>
@endforeach
</tbody>
</table>

</div>
</div>
</div>
@endsection

@push('css')

<style>
form {
    display:block!important;
    opacity:1!important;
    visibility:visible!important;
}
</style>
@endpush

@push('js')

@include('admin.partials.datatable-script', ['tableId' => '#schedulesTable'])

<script>
document.addEventListener('DOMContentLoaded', function () {

    const filterComplex  = document.getElementById('filterComplex');
    const filterActivity = document.getElementById('filterActivity');
    const rows = document.querySelectorAll('#schedulesTable tbody tr');

    if (!filterComplex || !filterActivity) return;

    function applyFilters() {
        const c = filterComplex.value;
        const a = filterActivity.value;

        rows.forEach(row => {
            const rc = row.dataset.complex;
            const ra = row.dataset.activity;

            let show = true;
            if (c && rc !== c) show = false;
            if (a && ra !== a) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    filterComplex.addEventListener('change', applyFilters);
    filterActivity.addEventListener('change', applyFilters);
});

function printReservation(id) {
    window.open("{{ url('/reservations') }}/"+id+"/print","_blank","width=900,height=1200");
}
</script>
@endpush

