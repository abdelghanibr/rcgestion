@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl; text-align:right;">

    <div class="p-3 mb-4"
         style="background: linear-gradient(to right, #0a4f88, #0a8a67);
                border-radius: 10px;
                color: #fff;
                font-weight:600;">
        <div class="d-flex justify-content-between align-items-center">
            <span>📅 إدارة الجداول الزمنية (Schedules)</span>

            <a href="{{ route('admin.schedules.create') }}" class="btn btn-light fw-bold">
                + إضافة جدول
            </a>
        </div>
    </div>

    {{-- 🔍 فلاتر البحث --}}
    <div class="card p-3 shadow-sm mb-3">

        <div class="row g-3">

            <div class="col-md-3">
                <label>المركب</label>
                <select id="filterComplex" class="form-control">
                    <option value="">الكل</option>
                    @foreach($complexes as $c)
                    <option value="{{ $c->nom }}">{{ $c->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>النشاط</label>
                <select id="filterActivity" class="form-control">
                    <option value="">الكل</option>
                    @foreach($activities as $a)
                    <option value="{{ $a->title }}">{{ $a->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>اليوم</label>
                <select id="filterDay" class="form-control">
                    <option value="">الكل</option>
                    <option value="الأحد">الأحد</option>
                    <option value="الإثنين">الإثنين</option>
                    <option value="الثلاثاء">الثلاثاء</option>
                    <option value="الأربعاء">الأربعاء</option>
                    <option value="الخميس">الخميس</option>
                    <option value="الجمعة">الجمعة</option>
                    <option value="السبت">السبت</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>الجنس</label>
                <select id="filterSex" class="form-control">
                    <option value="">الكل</option>
                    <option value="ذكور">ذكور</option>
                    <option value="إناث">إناث</option>
                    <option value="مختلط">مختلط</option>
                </select>
            </div>

        </div>

    </div>

    <div class="card p-3 shadow-sm">

        <table id="schedulesTable" class="table table-bordered table-striped text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>المركب</th>
                    <th>النشاط</th>
                    <th>الفئة العمرية</th>
                    <th>المجموعة</th>
                    <th>اليوم</th>
                    <th>من</th>
                    <th>إلى</th>
                    <th>الجنس</th>
                    <th>العدد</th>
                    <th>التحكم</th>
                </tr>
            </thead>

            <tbody>
                @foreach($schedules as $s)

                <tr>
                    <td>{{ $s->id }}</td>
                    <td>{{ $s->complexActivity->complex->nom ?? '—' }}</td>
                    <td>{{ $s->complexActivity->activity->title ?? '—' }}</td>
                    <td>{{ $s->ageCategory->name ?? '—' }}</td>
                    <td>{{ $s->groupe }}</td>

                    {{-- اليوم --}}
                    <td>
                        @php
                            $days = [
                                'dimanche' => 'الأحد',
                                'lundi' => 'الإثنين',
                                'mardi' => 'الثلاثاء',
                                'mercredi' => 'الأربعاء',
                                'jeudi' => 'الخميس',
                                'vendredi' => 'الجمعة',
                                'samedi' => 'السبت'
                            ];
                        @endphp
                        {{ $days[$s->day_of_week] ?? $s->day_of_week }}
                    </td>

                    <td>{{ $s->heure_debut }}</td>
                    <td>{{ $s->heure_fin }}</td>

                    <td>
                        @if($s->sex == 'H') ذكور
                        @elseif($s->sex == 'F') إناث
                        @else مختلط
                        @endif
                    </td>

                    <td>{{ $s->nbr ?? '—' }}</td>

                    <td>
                        <a href="{{ route('admin.schedules.edit', $s->id) }}"
                            class="btn btn-warning btn-sm">✏ تعديل</a>

                        <form action="{{ route('admin.schedules.destroy', $s->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger btn-sm"
                                onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                🗑 حذف
                            </button>
                        </form>
                    </td>

                </tr>
                @endforeach
            </tbody>

        </table>

    </div>
</div>

@endsection

@push('js')

@include('admin.partials.datatable-script', ['tableId' => '#schedulesTable'])

<script>
$(document).ready(function() {
    let table = $('#schedulesTable').DataTable();

    // 🔍 فلاتر البحث
    $('#filterComplex, #filterActivity, #filterDay, #filterSex').on('change', function () {
        table.draw();
    });

    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {

            let complex = $('#filterComplex').val();
            let activity = $('#filterActivity').val();
            let day = $('#filterDay').val();
            let sex = $('#filterSex').val();

            let col_complex = data[1];
            let col_activity = data[2];
            let col_day = data[5];
            let col_sex = data[8];

            if (complex && col_complex !== complex) return false;
            if (activity && col_activity !== activity) return false;
            if (day && col_day !== day) return false;
            if (sex && col_sex !== sex) return false;

            return true;
        }
    );
});
</script>

@endpush
