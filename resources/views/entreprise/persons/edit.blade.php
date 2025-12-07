@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl; text-align:right">

    <h3 class="mb-4 text-primary">✏ تعديل بيانات المستخدم</h3>

    <form action="{{ route('entreprise.persons.update', $person->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">الاسم:</label>
            <input type="text" name="firstname" value="{{ $person->firstname }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">اللقب:</label>
            <input type="text" name="lastname" value="{{ $person->lastname }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">تاريخ الميلاد:</label>
            <input type="date" name="birth_date" value="{{ $person->birth_date }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">الجنس:</label>
            <select name="gender" class="form-control" required>
                <option value="ذكر" {{ $person->gender == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                <option value="أنثى" {{ $person->gender == 'أنثى' ? 'selected' : '' }}>أنثى</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">التصنيف:</label>
            <select name="education" class="form-control" required>
                @foreach(['لاعب','مدرب','مسير','آخر'] as $role)
                <option value="{{ $role }}" {{ $person->education == $role ? 'selected':'' }}>
                    {{ $role }}
                </option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-success">💾 حفظ التعديلات</button>
        <a href="{{ route('entreprise.persons.index', $person->education) }}" class="btn btn-secondary">
            ⬅ رجوع
        </a>

    </form>
</div>

@endsection
