@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">
    <h3 class="mb-4">✏ تعديل النشاط</h3>
<form action="{{ route('admin.activities.update', $activity->id) }}"
      method="POST"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

        <div class="mb-3">
            <label class="form-label">اسم النشاط</label>
            <input type="text" name="title" class="form-control"
                   value="{{ $activity->title }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">نوع النشاط</label>
            <select name="activity_category_id" class="form-control">
                <option value="">— اختر نوع النشاط —</option>
                @foreach($activityCategories as $cat)
                    <option value="{{ $cat->id }}">
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">اللون المميز للنشاط</label>
            <input type="color" name="color" class="form-control form-control-color"
                   value="{{ $activity->color }}">
        </div>

        <div class="mb-3">
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-control" rows="3">{{ $activity->description }}</textarea>
        </div>

         <div class="mb-3">
    <label class="form-label fw-bold">حالة النشاط</label>
    <select name="is_active" class="form-control">
        <option value="1"
            {{ old('is_active', $activity->is_active ?? 1) == 1 ? 'selected' : '' }}>
            نشط
        </option>
        <option value="0"
            {{ old('is_active', $activity->is_active ?? 1) == 0 ? 'selected' : '' }}>
            غير نشط
        </option>
    </select>
</div>
{{-- أيقونة النشاط --}}
{{-- أيقونة النشاط الحالية --}}
@if(!empty($activity->icon))
    <div class="mb-3 text-center">
        <img src="{{ asset($activity->icon) }}"
             alt="Icon"
             class="icon-circle">
        <div class="small text-muted mt-1">
            الأيقونة الحالية
        </div>
    </div>
@endif


<div class="mb-3">
    <label class="form-label fw-bold">أيقونة النشاط (صورة)</label>

    <input type="file"
           name="icon"
           accept="image/*"
           class="form-control @error('icon') is-invalid @enderror">

    <small class="text-muted">
        في حال عدم اختيار صورة جديدة، سيتم الاحتفاظ بالأيقونة الحالية
    </small>

    @error('icon')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>



        <button class="btn btn-warning">💾 تحديث</button>
        <a href="{{ route('admin.activities.index') }}" class="btn btn-secondary">رجوع</a>

    </form>
</div>
@endsection
