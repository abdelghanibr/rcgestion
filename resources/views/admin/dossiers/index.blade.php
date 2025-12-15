@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align: right;">

    <h3 class="mb-3 fw-bold">📁 إدارة ملفات المشتركين</h3>

    {{-- ===== الفلاتر ===== --}}
{{-- ===== الفلاتر ===== --}}
<div class="row mb-3 g-2 align-items-end">

    <div class="col-md-3">
        <label class="form-label fw-bold small">فلتر الحالة</label>
        <select id="filterEtat" class="form-select form-select-sm">
            <option value="">كل الحالات</option>
            <option value="pending">قيد الدراسة</option>
            <option value="approved">مقبول</option>
            <option value="rejected">مرفوض</option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold small">صاحب الملف</label>
        <input type="text" id="filterOwner"
               class="form-control form-control-sm"
               placeholder="بحث بالاسم">
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold small">الحساب</label>
        <input type="text" id="filterAccount"
               class="form-control form-control-sm"
               placeholder="بحث بالحساب">
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold small">العمر</label>
        <input type="number" id="filterAge"
               class="form-control form-control-sm"
               placeholder="مثال: 18">
    </div>

</div>


    {{-- ===== الجدول ===== --}}
    <div class="table-responsive">
        <table id="dossiersTable"
               class="table table-bordered table-striped table-hover text-center align-middle w-100">

            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>صاحب الملف</th>
                    <th>الحساب</th>
                    <th>العمر</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th>المرفقات</th>
                    <th>ملاحظة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>

            <tbody>
            @foreach($dossiers as $d)

                @php
                    $age = ($d->person && $d->person->birth_date)
                        ? \Carbon\Carbon::parse($d->person->birth_date)->age
                        : null;

                    $files = json_decode($d->attachments, true) ?? [];

                    $labels = [
                        'medical_certificate'      => '🩺 شهادة طبية',
                        'birth_certificate'        => '🧾 شهادة الميلاد',
                        'photo'                    => '📷 صورة شمسية',
                        'parental_authorization'   => '✍️ تصريح أبوي',
                        'guardian_id_card'         => '🪪 بطاقة تعريف الولي',
                        'national_id_card'         => '🪪 بطاقة تعريف وطنية',
                        'engagement'               => '📄 تعهّد',
                    ];
                @endphp

                <tr>
                    <td>{{ $d->id }}</td>

                    <td class="fw-semibold small">
                        {{ $d->person->firstname ?? '' }}
                        {{ $d->person->lastname ?? '' }}
                    </td>

                    <td class="small">{{ $d->person->user->name ?? '—' }}</td>

                    {{-- العمر --}}
                    <td>
                        @if($age !== null)
                            <span class="badge bg-info small">{{ $age }} سنة</span>
                        @else
                            —
                        @endif
                    </td>

                    {{-- الحالة --}}
                    <td>
                        <span class="etat d-none">{{ $d->etat }}</span>

                        @if($d->etat === 'pending')
                            <span class="badge bg-warning small">قيد الدراسة</span>
                        @elseif($d->etat === 'approved')
                            <span class="badge bg-success small">مقبول</span>
                        @else
                            <span class="badge bg-danger small">مرفوض</span>
                        @endif
                    </td>

                    <td class="small">{{ $d->created_at->format('d-m-Y') }}</td>

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

                   <td class="small text-start">
    @if($d->note_admin)
        <span class="text-muted">{{ $d->note_admin }}</span>
    @else
        —
    @endif
</td>


                    {{-- الإجراءات --}}
                    <td>
                        @if($d->etat === 'pending')
                            <a href="{{ route('admin.dossiers.approve', $d->id) }}"
                               class="btn btn-success btn-xs"
                               onclick="return confirm('قبول الملف؟')">
                                قبول
                            </a>

                            <a href="{{ route('admin.dossiers.reject', $d->id) }}"
                               class="btn btn-danger btn-xs"
                               onclick="return confirm('رفض الملف؟')">
                                رفض
                            </a>
                            <button class="btn btn-secondary btn-xs"
        data-bs-toggle="modal"
        data-bs-target="#noteModal{{ $d->id }}">
    📝 ملاحظة
</button>

                        @else
                            —
                        @endif
                    </td>
                </tr>

                <div class="modal fade" id="noteModal{{ $d->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form action="{{ route('admin.dossiers.note', $d->id) }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">📝 ملاحظة إدارية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label class="form-label fw-bold">الملاحظة</label>
                    <textarea name="note_admin"
                              class="form-control form-control-sm"
                              rows="4"
                              placeholder="اكتب ملاحظتك هنا...">{{ $d->note_admin }}</textarea>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-sm">
                        💾 حفظ
                    </button>
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
    font-size: 12px;
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

.attachment-item:last-child {
    margin-bottom: 0;
}

.attachment-title {
    font-size: 12px;
    font-weight: 600;
}

.btn-xs {
    font-size: 11px;
    padding: 3px 8px;
}

.dataTables_filter input {
    font-size: 12px !important;
}
</style>
@endpush
@push('js')
@include('admin.partials.datatable-script', ['tableId' => '#dossiersTable'])
@endpush
