@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">
    <h3 class="mb-4">➕ إضافة نشاط جديد</h3>

    {{-- ✅ عرض جميع الأخطاء --}}
    @if ($errors->any())
        <div class="alert alert-danger fw-bold">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>⚠ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.activities.store') }}"
      method="POST"
      enctype="multipart/form-data">
        @csrf

        {{-- اسم النشاط --}}
        <div class="mb-3">
            <label class="form-label">اسم النشاط</label>
            <input type="text"
                   name="title"
                   value="{{ old('title') }}"
                   class="form-control @error('title') is-invalid @enderror"
                   required>

            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- نوع النشاط --}}
        <div class="mb-3">
            <label class="form-label fw-bold">نوع النشاط</label>
            <select name="activity_category_id"
                    class="form-control @error('activity_category_id') is-invalid @enderror">
                <option value="">— اختر نوع النشاط —</option>
                @foreach($activityCategories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ old('activity_category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>

            @error('activity_category_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- حالة النشاط --}}
        <div class="mb-3">
            <label class="form-label fw-bold">حالة النشاط</label>
            <select name="is_active"
                    class="form-control @error('is_active') is-invalid @enderror">
                <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>
                    نشط
                </option>
                <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>
                    غير نشط
                </option>
            </select>

            @error('is_active')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- اللون --}}
        <div class="mb-3">
            <label class="form-label">اللون المميز للنشاط</label>
            <input type="color"
                   name="color"
                   value="{{ old('color', '#007bff') }}"
                   class="form-control form-control-color @error('color') is-invalid @enderror">

            @error('color')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- الوصف --}}
        <div class="mb-3">
            <label class="form-label">الوصف (اختياري)</label>
            <textarea name="description"
                      rows="3"
                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>

            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
{{-- أيقونة النشاط --}}
@if(!empty($activity->icon))
    <div class="mb-3 text-center">
        <img src="{{ $activity->icon }}"
             alt="Icon"
             class="icon-circle">
    </div>
@endif

<div class="mb-3">
    <label class="form-label fw-bold">أيقونة النشاط (صورة)</label>

    <input type="file"
           name="icon"
           accept="image/*"
           class="form-control @error('icon') is-invalid @enderror">

    <small class="text-muted">
        الصيغ المسموحة: JPG, PNG — الحجم الأقصى: 4MB
    </small>

    @error('icon')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

        {{-- Buttons --}}
        <button class="btn btn-primary">💾 حفظ</button>
        <a href="{{ route('admin.activities.index') }}" class="btn btn-secondary">رجوع</a>

    </form>
</div>
@endsection
 @push('css')
<style>
.icon-circle {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #e5e7eb;
    box-shadow: 0 4px 10px rgba(0,0,0,.1);
}
</style>
@push('js')
<script>
function previewIcon(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('iconPreview');
            img.src = e.target.result;
            img.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

