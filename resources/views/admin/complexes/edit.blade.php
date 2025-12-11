@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">✏ تعديل بيانات المركب</h3>

    <form action="{{ route('admin.complexes.update', $complex->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">اسم المركب</label>
            <input type="text" name="nom" class="form-control" value="{{ $complex->nom }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">العنوان</label>
            <input type="text" name="adresse" class="form-control" value="{{ $complex->adresse }}">
        </div>
<div class="mb-3">
    <label class="form-label">الطاقة الاستيعابية للبالغين</label>
    <input type="number" name="capacite_ma" class="form-control"
           value="{{ $complex->capacite_ma }}">
</div>

<div class="mb-3">
    <label class="form-label">الطاقة الاستيعابية للقصر</label>
    <input type="number" name="capacite_mi" class="form-control"
           value="{{ $complex->capacite_mi }}">
</div>

        <div class="mb-3">
            <label class="form-label">الهاتف</label>
            <input type="text" name="phone" class="form-control" value="{{ $complex->telephone }}">
        </div>

       

        <button type="submit" class="btn btn-warning">💾 تحديث</button>
        <a href="{{ route('admin.complexes.index') }}" class="btn btn-secondary">رجوع</a>
    </form>
</div>
@endsection
