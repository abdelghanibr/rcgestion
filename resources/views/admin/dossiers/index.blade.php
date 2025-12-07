@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align: right;">

    <h3 class="mb-4 fw-bold">إدارة ملفات المشتركين</h3>

    <!-- الفلاتر -->
    <div class="row mb-4">
        <div class="col-md-3">
            <label class="form-label fw-bold">فلتر الحالة</label>
            <select id="filterEtat" class="form-select form-select-sm">
                <option value="">كل الحالات</option>
                <option value="pending">قيد الدراسة</option>
                <option value="approved">مقبول</option>
                <option value="rejected">مرفوض</option>
            </select>
        </div>
    </div>

    <!-- الجدول -->
    <div class="table-responsive">
        <table id="dossiersTable" class="table table-bordered table-striped table-hover text-center" style="width:100%">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>صاحب الملف</th>
                    <th>النادي / الشخص</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th>المرفقات</th>
                    <th>ملاحظة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dossiers as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td>{{ $d->person->firstname ?? '' }} {{ $d->person->lastname ?? '' }}</td>
                    <td>{{ $d->person->user->name ?? '---' }}</td>
                    <td>
                        <span class="etat d-none">{{ $d->etat }}</span>
                        @if($d->etat == 'pending')
                            <span class="badge bg-warning">قيد الدراسة</span>
                        @elseif($d->etat == 'approved')
                            <span class="badge bg-success">مقبول</span>
                        @else
                            <span class="badge bg-danger">مرفوض</span>
                        @endif
                    </td>
                    <td>{{ $d->created_at->format('d-m-Y') }}</td>
                    <td>
                        @php $files = json_decode($d->attachments, true); @endphp
                        @if(is_array($files) && count($files))
                            @foreach($files as $f)
                                <a href="{{ asset($f) }}" target="_blank" class="btn btn-sm btn-outline-primary">تحميل</a>
                            @endforeach
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $d->note_admin ?? '—' }}</td>
                    <td>
                        @if($d->etat == 'pending')
                            <a href="{{ route('admin.dossiers.approve', $d->id) }}" class="btn btn-success btn-sm" onclick="return confirm('قبول الملف؟')">قبول</a>
                            <a href="{{ route('admin.dossiers.reject', $d->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('رفض الملف؟')">رفض</a>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('css')
<!-- DataTables + Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.bootstrap5.min.css">
@endpush

@push('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables core -->
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>

<!-- Buttons + Export dependencies -->
<script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.colVis.min.js"></script>

<script>
$(document).ready(function () {

    var table = $('#dossiersTable').DataTable({
        language: { url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/ar.json" },
        responsive: true,
        pageLength: 10,
        order: [[0, "desc"]],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "الكل"]],

        dom:
            "<'row mb-3'<'col-md-4'l><'col-md-4 text-center'B><'col-md-4'f>>" +
            "<'row'<'col-12'tr>>" +
            "<'row mt-3'<'col-md-6'i><'col-md-6'p>>",

        buttons: [
            { extend: 'excelHtml5', text: '📊 إكسل', className: 'btn btn-success btn-sm' },
            { extend: 'pdfHtml5',  text: '📄 PDF', className: 'btn btn-danger btn-sm' },
            { extend: 'print',     text: '🖨 طباعة', className: 'btn btn-info btn-sm' },
            { extend: 'colvis',    text: '👁 إظهار الأعمدة', className: 'btn btn-secondary btn-sm' }
        ]
    });

    // فلتر حسب الحالة
    $('#filterEtat').on('change', function () {
        table.column(3).search(this.value).draw();
    });

});
</script>
@endpush
