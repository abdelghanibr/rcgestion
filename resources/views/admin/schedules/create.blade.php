@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">

    <h3 class="mb-4">➕ إضافة جدول جديد</h3>

    <form action="{{ route('admin.schedules.store') }}" method="POST">
        @csrf

        {{-- اختيار المركب --}}
     <form method="POST" action="{{ route('admin.schedules.store') }}">
    @csrf

    <div class="mb-3">
        <label>المركب</label>
        <select name="complex_id" id="complex" class="form-control">
            <option value="">-- اختر المركب --</option>
            @foreach($complexes as $complex)
                <option value="{{ $complex->id }}">{{ $complex->nom }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>النشاط</label>
        <select name="activity_id" id="activity" class="form-control">
            <option value="">-- اختر النشاط --</option>
            @foreach($activities as $activity)
                <option value="{{ $activity->id }}">{{ $activity->title }}</option>
            @endforeach
        </select>
    </div>

    <input type="hidden" name="complex_activity_id" id="complex_activity_id">


        {{-- hidden field --}}
        <input type="text" name="complex_activity_id" id="complex_activity_id">

        {{-- الفئة العمرية --}}
        <div class="mb-3">
            <label>الفئة العمرية</label>
            <select name="age_category_id" class="form-control">
                @foreach($ageCategories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- المجموعة --}}
        <div class="mb-3">
            <label>المجموعة</label>
            <input type="text" name="groupe" class="form-control" required>
        </div>

        {{-- اليوم --}}
      <div class="mb-3">
    <label>اليوم</label>
    <select name="day_of_week" class="form-control" required>
        <option value="Dim">الأحد</option>
        <option value="Lun">الإثنين</option>
        <option value="Mar">الثلاثاء</option>
        <option value="Mer">الأربعاء</option>
        <option value="Jeu">الخميس</option>
        <option value="Ven">الجمعة</option>
        <option value="Sam">السبت</option>
    </select>
</div>

        {{-- الساعات --}}
        <div class="row">
            <div class="col">
                <label>من</label>
                <input type="time" name="heure_debut" class="form-control">
            </div>
            <div class="col">
                <label>إلى</label>
                <input type="time" name="heure_fin" class="form-control">
            </div>
        </div>

        {{-- العدد --}}
        <div class="mt-3">
            <label>عدد الأماكن</label>
            <input type="number" name="nbr" class="form-control">
        </div>

        {{-- الجنس --}}
        <div class="mb-3 mt-3">
            <label>الجنس</label>
            <select name="sex" class="form-control">
                <option value="H">ذكور</option>
                <option value="F">إناث</option>
                <option value="X">مختلط</option>
            </select>
        </div>

        <button class="btn btn-success px-4">💾 حفظ</button>
    </form>

</div>





@endsection
