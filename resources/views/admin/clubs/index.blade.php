@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align: right;">

    <h3 class="mb-4 fw-bold">🏊‍♂️ إدارة النوادي المسجلة</h3>

    {{-- ===== فلترة الحالة ===== --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <label class="form-label fw-bold">فلترة حسب الحالة</label>
            <select id="filterEtat" class="form-select form-select-sm">
                <option value="">كل الحالات</option>
                <option value="pending">⏳ قيد الدراسة</option>
                <option value="approved">✔ مقبول</option>
                <option value="rejected">❌ مرفوض</option>
            </select>
        </div>
    </div>

    {{-- ===== الجدول ===== --}}
    <div class="table-responsive">
        <table id="clubsTable"
               class="table table-bordered table-striped table-hover text-center align-middle w-100">

            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>اسم النادي</th>
                    <th>رقم الإعتماد</th>
                    <th>تاريخ نهاية الإعتماد</th>
                    <th>الحالة</th>
                    <th>المرفقات</th>
                    <th>ملاحظة المسؤول</th>
                    <th>إجراءات</th>
                </tr>
            </thead>

            <tbody>
            @foreach($clubs as $c)

                @php
                    $files = json_decode($c->attachments, true) ?? [];

                    $labels = [
                        'agreement'              => '🏛️ اعتماد النادي',
                        'statut'                 => '📜 القانون الأساسي',
                        'bureau_members'         => '👥 أعضاء المكتب المسير',
                        'coaches_certificates'   => '🎓 شهادات المدربين',
                        'federation_affiliation' => '🏅 شهادة الانخراط في الرابطة',
                        'insurance_certificate'  => '🛡️ شهادة التأمين',
                        'rules_book'             => '📘 دفتر الشروط',
                        'minutes_meeting'        => '📝 محضر الجمعية العامة',
                        'exploitation_request'   => '📄 طلب الاستغلال',
                    ];
                @endphp

                <tr>
                    <td>{{ $c->id }}</td>
                    <td class="fw-semibold">{{ $c->nom }}</td>
                    <td>{{ $c->numero_agrement }}</td>
                    <td>{{ $c->date_expiration }}</td>

                    {{-- الحالة --}}
                    <td>
                        <span class="etat d-none">{{ $c->etat }}</span>

                        @if($c->etat === 'pending')
                            <span class="badge bg-warning text-dark">⏳ قيد الدراسة</span>
                        @elseif($c->etat === 'approved')
                            <span class="badge bg-success">✔ مقبول</span>
                        @else
                            <span class="badge bg-danger">❌ مرفوض</span>
                        @endif
                    </td>

                    {{-- المرفقات --}}
                    <td class="text-start">
                        @if(count($files))
                            <div class="attachments-box">
                                @foreach($files as $key => $path)
                                    <div class="attachment-item">
                                        <span class="attachment-title">
                                            {{ $labels[$key] ?? '📎 وثيقة' }}
                                        </span>
                                        <a href="{{ asset($path) }}"
                                           target="_blank"
                                           class="btn btn-outline-primary btn-xs">
                                            ⬇ تحميل
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            —
                        @endif
                    </td>

                    {{-- الملاحظة --}}
                    <td class="text-start small">
                        {{ $c->note_admin ?? '—' }}
                    </td>

                    {{-- الإجراءات --}}
                    <td>
                        @if($c->etat === 'pending')
                            <a href="{{ route('admin.clubs.approve', $c->id) }}"
                               class="btn btn-success btn-sm"
                               onclick="return confirm('قبول النادي؟')">
                               ✔ قبول
                            </a>

                            <a href="{{ route('admin.clubs.reject', $c->id) }}"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('رفض النادي؟')">
                               ❌ رفض
                            </a>

                            <button class="btn btn-secondary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#noteModal{{ $c->id }}">
                                📝 ملاحظة
                            </button>
                        @else
                            —
                        @endif
                    </td>
                </tr>

                {{-- ===== Modal Note Admin ===== --}}
                <div class="modal fade" id="noteModal{{ $c->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">

                            <form action="{{ route('admin.clubs.note', $c->id) }}" method="POST">
                                @csrf

                                <div class="modal-header">
                                    <h5 class="modal-title">📝 ملاحظة المسؤول</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <textarea name="note_admin"
                                              class="form-control form-control-sm"
                                              rows="4"
                                              placeholder="اكتب ملاحظتك هنا...">{{ $c->note_admin }}</textarea>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success btn-sm">💾 حفظ</button>
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                        إلغاء
                                    </button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>

            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.bootstrap5.min.css">

<style>
table.dataTable {
    font-size: 12px;
}

table thead th {
    white-space: nowrap;
}

.attachments-box {
    background: #f8fafc;
    padding: 8px;
    border-radius: 10px;
}

.attachment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 8px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    margin-bottom: 5px;
}

.attachment-title {
    font-size: 12px;
    font-weight: 600;
}

.btn-xs {
    font-size: 11px;
    padding: 3px 8px;
}
</style>
@endpush
@push('js')
@include('admin.partials.datatable-script', ['tableId' => '#clubsTable'])
@endpush