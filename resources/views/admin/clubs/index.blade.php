@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align: right;">

    <h3 class="mb-4 fw-bold">🏊‍♂️ إدارة النوادي المسجلة</h3>

    <!-- فلترة حسب الحالة -->
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

    <div class="table-responsive">
        <table id="clubsTable" class="table table-bordered table-striped table-hover text-center" style="width:100%">
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
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->nom }}</td>
                    <td>{{ $c->numero_agrement }}</td>
                    <td>{{ $c->date_expiration }}</td>

                    <td>
                        <span class="etat d-none">{{ $c->etat }}</span>
                        @if($c->etat == 'pending')
                            <span class="badge bg-warning text-dark">⏳ قيد الدراسة</span>
                        @elseif($c->etat == 'approved')
                            <span class="badge bg-success">✔ مقبول</span>
                        @else
                            <span class="badge bg-danger">❌ مرفوض</span>
                        @endif
                    </td>

                    <td>
                        @php $files = json_decode($c->attachments, true); @endphp
                        @if(is_array($files) && count($files))
                            @foreach($files as $f)
                                <a href="{{ asset($f) }}" target="_blank" class="btn btn-sm btn-outline-primary">📎</a>
                            @endforeach
                        @else
                            —
                        @endif
                    </td>

                    <td>{{ $c->note_admin ?? '—' }}</td>

                    <td>
                        @if($c->etat == 'pending')
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
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.bootstrap5.min.css">
@endpush

@push('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>

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

    var table = $('#clubsTable').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/ar.json' },
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "الكل"]],
        order: [[0, 'desc']],
        dom: "<'row'<'col-sm-4 text-start'l><'col-sm-4 text-center'B><'col-sm-4 text-end'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",

        buttons: [
            { extend: 'excelHtml5', text: '📊 إكسل', className: 'btn btn-success btn-sm' },
            { extend: 'pdfHtml5', text: '📄 PDF', className: 'btn btn-danger btn-sm' },
            { extend: 'print', text: '🖨 طباعة', className: 'btn btn-info btn-sm' },
            { extend: 'colvis', text: '📌 الأعمدة', className: 'btn btn-secondary btn-sm' }
        ]
    });

    // فلترة حسب الحالة
    $('#filterEtat').on('change', function () {
        table.column(4).search(this.value).draw();
    });

});
</script>
@endpush
