@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align: right;">

    <h3 class="mb-4 fw-bold">🎯 الأنشطة الرياضية</h3>

    <div class="mb-3 text-end">
        <a href="{{ route('admin.activities.create') }}" class="btn btn-primary">
            ➕ إضافة نشاط
        </a>
    </div>

    <div class="table-responsive">
        <table id="activitiesTable" class="table table-bordered table-striped table-hover text-center">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>الرمز</th>
                    <th>النشاط</th>
                    <th>اللون</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activities as $a)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{!! $a->icon ?? '🏅' !!}</td>
                    <td>{{ $a->title }}</td>
                    <td>
                        <span style="background: {{ $a->color }}; padding:6px 12px; border-radius:6px;">
                            {{ $a->color }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.activities.edit', $a->id) }}" class="btn btn-sm btn-warning">✏ تعديل</a>

                        <form action="{{ route('admin.activities.destroy', $a->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button onclick="return confirm('حذف النشاط؟')" class="btn btn-sm btn-danger">🗑 حذف</button>
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
@include('admin.partials.datatable-script', ['tableId' => '#activitiesTable'])
@endpush
