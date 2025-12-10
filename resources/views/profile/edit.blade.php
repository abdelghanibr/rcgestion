@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">

    <h3 class="mb-4 fw-bold">👤 تعديل الملف الشخصي</h3>

    @if(session('success'))
        <div class="alert alert-success fw-bold text-center">{{ session('success') }}</div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- الاسم --}}
        <label class="form-label fw-bold">الاسم الكامل</label>
        <input type="text" name="name" class="form-control mb-3"
               value="{{ $user->name }}" required>

        {{-- البريد الإلكتروني --}}
        <label class="form-label fw-bold">البريد الإلكتروني</label>
        <input type="email" name="email" class="form-control mb-3"
               value="{{ $user->email }}" required>

        {{-- الهاتف --}}
        <label class="form-label fw-bold">رقم الهاتف</label>
        <input type="text" name="phone" class="form-control mb-3"
               value="{{ $user->phone }}">

        {{-- الصورة --}}
        <label class="form-label fw-bold">📸 الصورة الشخصية</label>
        <input type="file" name="photo" class="form-control mb-3">

        @if($user->photo)
            <img src="{{ asset('storage/'.$user->photo) }}" width="80" class="rounded mb-3">
        @endif

        <button class="btn btn-success fw-bold px-4">💾 حفظ</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary px-4">رجوع</a>

    </form>

</div>
@endsection
