@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl; text-align:right;">

    {{-- 🟦 Header --}}
    <div class="p-3 mb-4"
         style="background: linear-gradient(to right, #0a4f88, #0a8a67);
                border-radius: 10px;
                color: #fff;
                font-weight:600;">
        <div class="d-flex justify-content-between align-items-center">
            <span>📋 حجوزاتي</span>

            <a href="{{ route('activities.index') }}" class="btn btn-light fw-bold">
                ➕ حجز جديد
            </a>
        </div>
    </div>

    {{-- 🔍 فلاتر --}}
    <div class="card p-3 shadow-sm mb-3">
        <div class="row g-3">

            <div class="col-md-3">
                <label>النشاط</label>
                <select id="filterActivity" class="form-control">
                    <option value="">الكل</option>
                    @foreach($activities as $a)
                        <option value="{{ $a->title }}">{{ $a->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>الموسم</label>
                <select id="filterSeason" class="form-control">
                    <option value="">الكل</option>
                    @foreach($seasons as $s)
                        <option value="{{ $s->name }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>الحالة</label>
                <select id="filterStatus" class="form-control">
                    <option value="">الكل</option>
                    <option value="pending">قيد الانتظار</option>
                    <option value="confirmed">مؤكد</option>
                    <option value="rejected">مرفوض</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>الدفع</label>
                <select id="filterPayment" class="form-control">
                    <option value="">الكل</option>
                    <option value="paid">مدفوع</option>
                    <option value="unpaid">غير مدفوع</option>
                </select>
            </div>

        </div>
    </div>

    {{-- 📊 Table --}}
    <div class="card p-3 shadow-sm">

        <table id="reservationsTable"
               class="table table-bordered table-striped text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>النشاط</th>
                    <th>الموسم</th>
                    <th>من</th>
                    <th>إلى</th>
                    <th>الساعات</th>
                    <th>السعر</th>
                    <th>الحالة</th>
                    <th>الدفع</th>
                    <th>التحكم</th>
                </tr>
            </thead>

            <tbody>
                @foreach($reservations as $r)
                <tr>
                    <td>{{ $r->id }}</td>

                    <td>{{ optional(optional($r->complexActivity)->activity)->title ?? '—' }}</td>

                    <td>{{ optional($r->season)->name ?? '—' }}</td>

                    <td>{{ $r->start_date?->format('Y-m-d') }}</td>
                    <td>{{ $r->end_date?->format('Y-m-d') }}</td>

                    <td>{{ $r->duration_hours ?? '—' }}</td>

                    <td>{{ number_format($r->total_price ?? 0) }} دج</td>

                    {{-- الحالة --}}
                    <td>
                        <span class="badge
                            {{ $r->status == 'confirmed' ? 'bg-success' :
                               ($r->status == 'pending' ? 'bg-warning' : 'bg-danger') }}">
                            {{ $r->status }}
                        </span>
                    </td>

                    {{-- الدفع --}}
                    <td>
                        <span class="badge {{ $r->payment_status == 'paid' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $r->payment_status }}
                        </span>
                    </td>

                    {{-- التحكم --}}
                    <td>
                        <button class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#renewModal{{ $r->id }}">
                            🔁 تجديد
                        </button>
                    </td>
                </tr>

                {{-- 🔁 Modal التجديد --}}
                <div class="modal fade" id="renewModal{{ $r->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <form action="{{ route('reservations.renew.store', $r->id) }}" method="POST">
                            @csrf

                            <div class="modal-content" style="direction: rtl">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">🔁 تجديد الحجز</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">

                                    <div class="alert alert-info">
                                        <strong>النشاط:</strong>
                                        {{ optional(optional($r->complexActivity)->activity)->title }}
                                        <br>
                                        <strong>السعر السابق:</strong>
                                        {{ number_format($r->total_price) }} دج
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>📅 من</label>
                                            <input type="date" name="start_date"
                                                   class="form-control"
                                                   min="{{ now()->toDateString() }}"
                                                   required>
                                        </div>

                                        <div class="col-md-6">
                                            <label>📅 إلى</label>
                                            <input type="date" name="end_date"
                                                   class="form-control" required>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="pay_now"
                                               value="1"
                                               id="payNow{{ $r->id }}">
                                        <label class="form-check-label" for="payNow{{ $r->id }}">
                                            💳 الدفع الآن
                                        </label>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                                        إلغاء
                                    </button>
                                    <button class="btn btn-success">
                                        ✅ تأكيد التجديد
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>

    </div>
</div>

@endsection

@push('js')

@include('admin.partials.datatable-script', ['tableId' => '#reservationsTable'])

<script>
$(document).ready(function() {

    let table = $('#reservationsTable').DataTable();

    $('#filterActivity, #filterSeason, #filterStatus, #filterPayment')
        .on('change', function () {
            table.draw();
        });

    $.fn.dataTable.ext.search.push(
        function(settings, data) {

            let activity = $('#filterActivity').val();
            let season   = $('#filterSeason').val();
            let status   = $('#filterStatus').val();
            let payment  = $('#filterPayment').val();

            if (activity && data[1] !== activity) return false;
            if (season && data[2] !== season) return false;
            if (status && data[7] !== status) return false;
            if (payment && data[8] !== payment) return false;

            return true;
        }
    );
});
</script>

@endpush
