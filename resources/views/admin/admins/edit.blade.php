@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl;">

    <h3 class="fw-bold mb-3">✏ تعديل بيانات المسؤول</h3>

    <form action="{{ route('admins.update', $admin->id) }}" method="POST">
        @csrf

        <label>الاسم:</label>
        <input type="text" name="name" class="form-control mb-2" value="{{ $admin->name }}" required>

        <label>البريد الإلكتروني:</label>
        <input type="email" name="email" class="form-control mb-2" value="{{ $admin->email }}" required>

        <label>كلمة المرور الجديدة (اختياري):</label>
        <input type="password" name="password" class="form-control mb-3">

        <button class="btn btn-primary">💾 حفظ التغييرات</button>
    </form>

</div>

@endsection
