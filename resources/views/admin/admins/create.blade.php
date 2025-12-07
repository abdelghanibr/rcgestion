@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl;">

    <h3 class="fw-bold mb-3">➕ إضافة مسؤول جديد</h3>

    <form action="{{ route('admins.store') }}" method="POST">
        @csrf

        <label>الاسم:</label>
        <input type="text" name="name" class="form-control mb-2" required>

        <label>البريد الإلكتروني:</label>
        <input type="email" name="email" class="form-control mb-2" required>

        <label>كلمة المرور:</label>
        <input type="password" name="password" class="form-control mb-3" required>

        <button class="btn btn-success">💾 حفظ</button>
    </form>

</div>
@endsection
