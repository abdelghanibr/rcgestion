@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">
    <h3 class="mb-4">➕ إضافة نشاط جديد</h3>

    <form action="{{ route('admin.activities.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">اسم النشاط</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">اللون المميز للنشاط</label>
            <input type="color" name="color" class="form-control form-control-color" value="#007bff">
        </div>

        <div class="mb-3">
            <label class="form-label">الوصف (اختياري)</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>

        <button class="btn btn-primary">💾 حفظ</button>
        <a href="{{ route('admin.activities.index') }}" class="btn btn-secondary">رجوع</a>

    </form>
</div>
@endsection
