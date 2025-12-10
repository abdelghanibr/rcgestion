@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">

    <h3 class="mb-4 fw-bold">✏ تعديل خطة التسعير</h3>

    <form method="POST" action="{{ route('admin.pricing_plans.update', $plan->id) }}">
        @csrf
        @method('PUT')

        {{-- النشاط --}}
        <label class="form-label fw-bold">🔹 النشاط</label>
        <select name="activity_id" class="form-control mb-3" required>
            @foreach($activities as $a)
                <option value="{{ $a->id }}" {{ $plan->activity_id == $a->id ? 'selected' : '' }}>
                    {{ $a->title }}
                </option>
            @endforeach
        </select>

        {{-- الفئة العمرية --}}
        <label class="form-label fw-bold">👥 الفئة العمرية</label>
        <select name="age_category_id" class="form-control mb-3" required>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ $plan->age_category_id == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
            @endforeach
        </select>

        {{-- الاسم --}}
        <label class="form-label fw-bold">🏷️ اسم الخطة</label>
        <input type="text" name="name" class="form-control mb-3"
               value="{{ $plan->name }}" required>

        {{-- نوع التسعير --}}
        <label class="form-label fw-bold">🧾 نوع التسعير</label>
        <select name="pricing_type" class="form-control mb-3" required>
            <option value="session" {{ $plan->pricing_type=='session' ? 'selected' : '' }}>حسب الحصة</option>
            <option value="weekly" {{ $plan->pricing_type=='weekly' ? 'selected' : '' }}>أسبوعي</option>
            <option value="monthly" {{ $plan->pricing_type=='monthly' ? 'selected' : '' }}>شهري</option>
            <option value="season" {{ $plan->pricing_type=='season' ? 'selected' : '' }}>موسمي</option>
            <option value="ticket" {{ $plan->pricing_type=='ticket' ? 'selected' : '' }}>تذكرة / يومي</option>
        </select>

        {{-- المدة --}}
        <div class="row mb-3">
            <div class="col">
                <label class="form-label fw-bold">⏱️ المدة</label>
                <input type="number" name="duration_value"
                       class="form-control" min="1"
                       value="{{ $plan->duration_value }}" required>
            </div>
            <div class="col">
                <label class="form-label fw-bold">📍 وحدة المدة</label>
                <select name="duration_unit" class="form-control" required>
                    <option value="day" {{ $plan->duration_unit=='day' ? 'selected' : '' }}>يوم</option>
                    <option value="week" {{ $plan->duration_unit=='week' ? 'selected' : '' }}>أسبوع</option>
                    <option value="month" {{ $plan->duration_unit=='month' ? 'selected' : '' }}>شهر</option>
                    <option value="season" {{ $plan->duration_unit=='season' ? 'selected' : '' }}>موسم</option>
                </select>
            </div>
        </div>

        {{-- عدد الحصص في الأسبوع --}}
        <label class="form-label fw-bold">🔥 عدد الحصص الأسبوعية</label>
        <input type="number" name="sessions_per_week" class="form-control mb-3"
               min="0" max="20" value="{{ $plan->sessions_per_week }}">

        {{-- الجنس --}}
        <label class="form-label fw-bold">🧍 الجنس</label>
        <select name="sexe" class="form-control mb-3" required>
            <option value="H" {{ $plan->sexe=='H' ? 'selected' : '' }}>ذكور</option>
            <option value="F" {{ $plan->sexe=='F' ? 'selected' : '' }}>إناث</option>
        </select>

        {{-- نوع العميل --}}
        <label class="form-label fw-bold">💼 نوع العميل</label>
        <select name="type_client" class="form-control mb-3" required>
            <option value="person" {{ $plan->type_client=='person' ? 'selected' : '' }}>أفراد</option>
            <option value="club" {{ $plan->type_client=='club' ? 'selected' : '' }}>نادي</option>
            <option value="company" {{ $plan->type_client=='company' ? 'selected' : '' }}>شركة</option>
        </select>

        {{-- السعر --}}
        <label class="form-label fw-bold">💵 السعر (دج)</label>
        <input type="number" name="price" step="0.01" class="form-control mb-3"
               value="{{ $plan->price }}" required>

        {{-- صلاحية --}}
        <div class="row mb-3">
            <div class="col">
                <label class="form-label fw-bold">📅 بداية الصلاحية</label>
                <input type="date" name="valid_from" class="form-control"
                       value="{{ $plan->valid_from }}">
            </div>
            <div class="col">
                <label class="form-label fw-bold">📅 نهاية الصلاحية</label>
                <input type="date" name="valid_to" class="form-control"
                       value="{{ $plan->valid_to }}">
            </div>
        </div>

        {{-- مفعل --}}
        <label class="form-label fw-bold">⚙️ مفعل؟</label>
        <select name="active" class="form-control mb-4" required>
            <option value="1" {{ $plan->active == 1 ? 'selected' : '' }}>✔ نعم</option>
            <option value="0" {{ $plan->active == 0 ? 'selected' : '' }}>✘ لا</option>
        </select>

        <button type="submit" class="btn btn-warning fw-bold">💾 تحديث</button>
        <a href="{{ route('admin.pricing_plans.index') }}" class="btn btn-secondary">رجوع</a>

    </form>

</div>
@endsection
