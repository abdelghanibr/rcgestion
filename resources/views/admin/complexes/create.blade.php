@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align: right;">
    <h3 class="mb-4">➕ إضافة مركب رياضي جديد</h3>

    <form action="{{ route('admin.complexes.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">اسم المركب</label>
            <input type="text" name="nom" class="form-control" required>
            @error('nom') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">العنوان</label>
            <input type="text" name="adresse" class="form-control">
            @error('adresse') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">رقم الهاتف</label>
            <input type="text" name="telephone" class="form-control">
            @error('telephone') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">القدرة الاستيعابية</label>
            <input type="number" name="capacite" class="form-control" min="1">
            @error('capacite') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-primary">💾 حفظ</button>
        <a href="{{ route('admin.complexes.index') }}" class="btn btn-secondary">رجوع</a>
    </form>
</div>
@endsection

