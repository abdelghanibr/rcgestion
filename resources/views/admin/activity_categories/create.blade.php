@extends('layouts.app')

@section('content')
<div class="container" style="direction: rtl">

<h4 class="fw-bold mb-3">➕ إضافة صنف نشاط</h4>

<form method="POST" action="{{ route('activity-categories.store') }}">
@csrf

<div class="mb-3">
    <label class="fw-bold">اسم الصنف</label>
    <input name="name" class="form-control" required>
</div>


<div class="mb-3">
    <label class="fw-bold">اللون</label>
    <input name="color" type="color" class="form-control form-control-color">
</div>

<button class="btn btn-success">💾 حفظ</button>
<a href="{{ route('activity-categories.index') }}"
   class="btn btn-secondary">رجوع</a>

</form>
</div>
@endsection
