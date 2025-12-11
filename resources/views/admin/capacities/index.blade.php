@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl; text-align:right">

    <h3 class="fw-bold mb-4">📊 السعات حسب المركّب والنشاط</h3>

    @if(session('success'))
        <div class="alert alert-success text-center fw-bold">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('admin.capacities.create') }}" class="btn btn-primary mb-3">
        ➕ إضافة سعة جديدة
    </a>

    <div class="table-responsive">
        <table id="capacityTable" class="table table-striped table-bordered table-hover text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>المركّب</th>
                    <th>النشاط</th>
                    <th>الموسم</th>
                    <th>السعة</th>
                    <th style="min-width: 130px">إجراءات</th>
                </tr>
            </thead>

            <tbody>
                @foreach($capacities as $cap)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $cap->complex->nom ?? '-' }}</td>
                    <td>{{ $cap->activity->title ?? '-' }}</td>
                    <td>{{ $cap->season->name ?? '-' }}</td>
                    <td>{{ $cap->capacity }}</td>

                    <td>
                        <a href="{{ route('admin.capacities.edit', $cap->id) }}"
                           class="btn btn-warning btn-sm">
                            ✏ تعديل
                        </a>

                        <form action="{{ route('admin.capacities.destroy', $cap->id) }}"
                              method="POST"
                              onsubmit="return confirm('هل تريد حذف هذا السجل؟');"
                              class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">🗑 حذف</button>
                        </form>
                    </td>

                </tr>
                @endforeach
            </tbody>

        </table>
    </div>

</div>

@endsection


{{-- =======================
      CSS - DataTables
======================= --}}
@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.1.2/css/buttons.bootstrap5.min.css">
@endpush


{{-- =======================
      JS - DataTables
======================= --}}
@push('js')
@include('admin.partials.datatable-script', ['tableId' => '#capacityTable'])
@endpush
