@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl; text-align:right">

    <!-- الشريط الأزرق الأخضر -->
    <div class="p-3 mb-4"
         style="background: linear-gradient(to right, #0a4f88, #0a8a67);
                border-radius: 10px;
                color: #fff;
                font-weight:600;">
        <div class="d-flex justify-content-between align-items-center">
            <span>إدارة جميع النشاطات الخاصة بك هنا</span>
            <span style="font-size:20px;">
                <i class="fa-solid fa-wave-pulse"></i> نشاطاتي
            </span>
        </div>
    

    {{-- ===================== --}}
    {{--  فلترة حسب الفئة فقط --}}
    {{-- ===================== --}}
     
<form method="GET"
      action="{{ route('activities.index') }}"
      class="d-flex gap-2">

    {{-- البحث --}}
    <input name="search"
           type="text"
           class="form-control"
           value="{{ request('search') }}"
           placeholder="ابحث عن نشاط..."
           style="width: 220px; border-radius:8px;"
           oninput="this.form.submit()">

    {{-- الفئة --}}
    <select name="category_id"
            class="form-select"
            style="width: 220px; border-radius:8px;"
            onchange="this.form.submit()">
        <option value="">كل الفئات</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}"
                {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
            </option>
        @endforeach
    </select>

</form>
 
</div>



    <!-- عرض النشاطات -->
    <div class="row g-4" id="activitiesContainer">

        @forelse ($activities as $a)
        <div class="col-md-4 activity-card"
             data-category="{{ strtolower($a->category->name ?? '') }}">

            <div class="card shadow-sm" style="border: 2px solid {{ $a->color }};">

                {{-- الصورة --}}
                @if($a->icon)
                    <div style="height:180px; background:#f0f0f0; overflow:hidden;">
                        <img src="{{ $a->icon }}"
                             alt="Activity Icon"
                             style="width:100%; height:100%; object-fit:cover;"
                             onerror="this.src='{{ asset('images/default-activity.png') }}'">
                    </div>
                @else
                    <div style="height:180px;
                                background:#f0f0f0;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                color:#888;">
                        <i class="fa-regular fa-image fa-2x"></i>
                        <span class="ms-2">لا توجد صورة</span>
                    </div>
                @endif

                <div class="card-body">
                    <h5 class="fw-bold" style="color: {{ $a->color }};">
                        {{ $a->title }}
                    </h5>

                    <p class="text-muted">
                        {{ Str::limit($a->description, 90) }}
                    </p>

                    <p class="text-muted">
                        الرقم / {{ $a->id }}
                    </p>

                    <span class="badge bg-primary mb-2">
                        {{ $a->activityCategory->name ?? 'بدون فئة' }}
                    </span>

                    <br>

                    <a href="{{ route('activities.complexes', $a->id) }}"
                       class="btn btn-success btn-sm mt-2">
                        <i class="fa-solid fa-pen-to-square ms-1"></i>
                        تسجيل في النشاط
                    </a>
                </div>
            </div>
        </div>

        @empty
            <div class="alert alert-info text-center">
                لا توجد نشاطات متاحة حالياً.
            </div>
        @endforelse

    </div>

</div>

{{-- ===================== --}}
{{--  JS فلترة حسب الفئة --}}
{{-- ===================== --}}



<script>
document.getElementById('categoryFilter').addEventListener('change', function () {

    let selected = this.value;
    let cards = document.querySelectorAll('.activity-card');

    cards.forEach(card => {
        let category = card.dataset.category;

        if (selected === '' || category === selected) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

@endsection
