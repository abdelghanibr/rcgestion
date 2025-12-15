@extends('layouts.app')

@section('content')
<div class="container" style="direction: rtl; text-align:right;">
    <h3 class="mb-3">➕ إضافة خطة تسعير</h3>

    <form method="POST" action="{{ route('admin.pricing_plans.store') }}">
        @csrf

        <label class="form-label">النشاط</label>
        <select name="activity_id" class="form-control mb-3" required>
            <option value="">اختر النشاط</option>
            @foreach($activities as $a)
            <option value="{{ $a->id }}">{{ $a->title }}</option>
            @endforeach
        </select>

        <label class="form-label">الفئة العمرية</label>
        <select name="age_category_id" class="form-control mb-3" required>
            <option value="">اختر الفئة</option>
            @foreach($categories as $c)
            <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>

        <label class="form-label">اسم الخطة</label>
        <input type="text" name="name" class="form-control mb-3" required>

        <div class="row mb-3">
            <div class="col">
                <label class="form-label">المدة</label>
                <input type="number" name="duration_value" class="form-control" required>
            </div>
            <div class="col">
                <label class="form-label">وحدة المدة</label>
                <select name="duration_unit" class="form-control">
                    <option value="day">يوم</option>
                    <option value="week">أسبوع</option>
                    <option value="month">شهر</option>
                    <option value="season">موسم</option>
                </select>
            </div>
        </div>

        <label class="form-label">عدد الحصص في الأسبوع</label>
        <input type="number" name="sessions_per_week" class="form-control mb-3">

        <label class="form-label">نوع الاشتراك</label>
       <select name="pricing_type" class="form-select" required>
    <option value="session">بالحصة</option>
    <option value="weekly">أسبوعيا</option>
    <option value="monthly">شهريًا</option>
    <option value="season">موسمي</option>
    <option value="ticket">بطاقة دخول</option>
</select>


        <label class="form-label">الجنس</label>
        <select name="sexe" class="form-control mb-3">
            <option value="X">مختلط</option>
            <option value="H">ذكور</option>
            <option value="F">إناث</option>
        </select>

        <label class="form-label">نوع العميل</label>
   <select name="type_client" class="form-select">
    <option value="person">أفراد</option>
    <option value="club">نوادي</option>
    <option value="company">شركات</option>
</select>


        <label class="form-label">السعر (دج)</label>
        <input type="number" name="price" class="form-control mb-3" required>

        <label class="form-label">بداية الصلاحية</label>
        <input type="date" name="valid_from" class="form-control mb-3" value="{{ date('Y-m-d') }}">

        <label class="form-label">نهاية الصلاحية</label>
        <input type="date" name="valid_to" class="form-control mb-3">

        <label class="form-label">مفعل؟</label>
        <select name="active" class="form-control mb-4">
            <option value="1">نعم</option>
            <option value="0">لا</option>
        </select>

        <button class="btn btn-primary">💾 حفظ</button>
        <a href="{{ route('admin.pricing_plans.index') }}" class="btn btn-secondary">رجوع</a>
    </form>

</div>
@endsection
