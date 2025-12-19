@extends('layouts.app')

@section('content')
<div class="container-fluid" style="direction: rtl">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">👥 قائمة الأفراد</h4>
    </div>

    {{-- TABLE --}}
    <div class="card shadow-sm">
        <div class="card-body">

            <table id="capacityTable"
                   class="table table-bordered table-hover align-middle text-center w-100">

                <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>الاسم الكامل</th>
                    <th>الجنس</th>
                    <th>تاريخ الميلاد</th>
                    <th>الفئة العمرية</th>
                    <th>الهاتف</th>
                    <th>الصفة</th>
                    <th>المدينة</th>
                    <th>النادي</th>
                    <th>من دوي الإحتياجات خ</th>
                    <th>إجراءات</th>
                </tr>
                </thead>

                <tbody>
                @foreach($persons as $p)
                    <tr>
                        <td>{{ $p->id }}</td>

                        {{-- Name --}}
                        <td class="fw-bold">
                            {{ $p->firstname }} {{ $p->lastname }}
                        </td>

                        {{-- Gender --}}
                        <td>
                            {{ $p->gender ?? $p->sexe ?? '—' }}
                        </td>

                        {{-- Birth --}}
                        <td>
                            {{ $p->birth_date ?? $p->date_naissance ?? '—' }}
                        </td>

                        {{-- Age Category --}}
                        <td>
                            {{ $p->ageCategory->name ?? '—' }}
                        </td>

                        {{-- Phone --}}
                        <td>
                            {{ $p->phone ?? '—' }}
                        </td>
                        <td>
                            {{ $p->education ?? '—' }}
                        </td>
                        {{-- City --}}
                        <td>
                            {{ $p->birth_city ?? $p->wilaya ?? '—' }}
                        </td>

                        {{-- Club --}}
                        <td>
                         {{ $p->user->type ?? '—' }}

                        </td>

                        {{-- Handicap --}}
                        <td>
                            @if($p->handicap)
                                <span class="badge bg-warning">نعم</span>
                            @else
                                <span class="badge bg-success">لا</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                          <td class="text-nowrap">
                            
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>

        </div>
    </div>

</div>
@endsection

{{-- ================= DATATABLE ================= --}}
@push('js')
@include('admin.partials.datatable-script', ['tableId' => '#capacityTable'])
@endpush
