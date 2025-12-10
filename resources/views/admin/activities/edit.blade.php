@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">
    <h3 class="mb-4">✏ تعديل النشاط</h3>

    <form action="{{ route('admin.activities.update', $activity->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">اسم النشاط</label>
            <input type="text" name="title" class="form-control"
                   value="{{ $activity->title }}" required>
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

        <button class="btn btn-warning">💾 تحديث</button>
        <a href="{{ route('admin.activities.index') }}" class="btn btn-secondary">رجوع</a>

    </form>
</div>
@endsection
