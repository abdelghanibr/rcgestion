@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">

    <h3 class="mb-4 fw-bold">🏟️ المركبات الرياضية</h3>

    <div class="mb-3 text-end">
        <a href="{{ route('admin.complexes.create') }}" class="btn btn-primary">
            ➕ إضافة مركب
        </a>
    </div>

    <div class="table-responsive">
        <table id="complexesTable" class="table table-bordered table-striped table-hover text-center">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>العنوان</th>
                    <th>الهاتف</th>
                    <th>الطاقة الاستيعابية</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($complexes as $c)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $c->nom }}</td>
                    <td>{{ $c->adresse ?? '—' }}</td>
                    <td>{{ $c->telephone ?? '—' }}</td>
                    <td>{{ $c->capacite ?? '—' }}</td>
                    <td>
                        <a href="{{ route('admin.complexes.edit', $c->id) }}" class="btn btn-sm btn-warning">✏ تعديل</a>

                        <form action="{{ route('admin.complexes.destroy', $c->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button onclick="return confirm('حذف المركب؟')" class="btn btn-sm btn-danger">🗑 حذف</button>
                        </form>
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
@include('admin.partials.datatable-script', ['tableId' => '#complexesTable'])
@endpush
