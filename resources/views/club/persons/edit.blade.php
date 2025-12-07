@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">

    <h4 class="mb-3 text-primary">✏ تعديل بيانات المستخدم</h4>

    <form action="{{ route('club.persons.update', $person->id) }}" method="POST">
        @csrf

        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">الاسم:</label>
                <input type="text" name="firstname" class="form-control" value="{{ $person->firstname }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">اللقب:</label>
                <input type="text" name="lastname" class="form-control" value="{{ $person->lastname }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">تاريخ الميلاد:</label>
                <input type="date" name="birth_date" class="form-control" value="{{ $person->birth_date }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">الجنس:</label>
                <select name="gender" class="form-control" required>
                    <option {{ $person->gender=='ذكر'?'selected':'' }}>ذكر</option>
                    <option {{ $person->gender=='أنثى'?'selected':'' }}>أنثى</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">التصنيف:</label>
<select name="education" class="form-control" required>
    @foreach(['لاعب','مدرب','مسير','آخر'] as $role)
    <option value="{{ $role }}" {{ $person->education == $role ? 'selected' : '' }}>
        {{ $role }}
    </option>
    @endforeach
</select>

                </select>
            </div>

        </div>

        <button type="submit" class="btn btn-success mt-3">💾 حفظ التعديلات</button>
    </form>

</div>
@endsection
