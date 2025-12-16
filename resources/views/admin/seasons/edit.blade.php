@extends('layouts.app')

@section('content')
<div class="container" style="max-width:700px">

<h4 class="fw-bold mb-4">✏ تعديل الموسم</h4>

<form method="POST" action="{{ route('seasons.update',$season) }}">
@csrf @method('PUT')

<div class="mb-3">
    <label class="fw-bold">الاسم</label>
    <input name="name" class="form-control"
           value="{{ $season->name }}" required>
</div>

<div class="mb-3">
    <label class="fw-bold">نوع الموسم</label>
    <select name="type_season" class="form-control">
        @foreach($types as $key=>$label)
            <option value="{{ $key }}"
                @selected($season->type_season==$key)>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label>تاريخ البداية</label>
        <input type="date" name="date_debut"
               value="{{ $season->date_debut }}" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
        <label>تاريخ النهاية</label>
        <input type="date" name="date_fin"
               value="{{ $season->date_fin }}" class="form-control">
    </div>
</div>

<button class="btn btn-primary w-100">💾 تحديث</button>

</form>
</div>
@endsection
