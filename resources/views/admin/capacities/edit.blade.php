@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl; text-align:right">

    <h3 class="fw-bold mb-4">✏ تعديل السعة</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>⚠ يوجد أخطاء:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.capacities.update', $capacity->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- المركب الرياضي --}}
        <label class="form-label fw-bold">🏟️ المركّب</label>
        <select name="complex_id" class="form-control mb-3" required>
            @foreach($complexes as $c)
                <option value="{{ $c->id }}" {{ $capacity->complex_id == $c->id ? 'selected' : '' }}>
                    {{ $c->nom }}
                </option>
            @endforeach
        </select>

        {{-- النشاط --}}
        <label class="form-label fw-bold">🏊‍♂️ النشاط</label>
        <select name="activity_id" class="form-control mb-3" required>
            @foreach($activities as $a)
                <option value="{{ $a->id }}" {{ $capacity->activity_id == $a->id ? 'selected' : '' }}>
                    {{ $a->title }}
                </option>
            @endforeach
        </select>

     

        {{-- السعة --}}
        <label class="form-label fw-bold">👥 السعة</label>
        <input type="number" name="capacity" class="form-control mb-4"
               value="{{ $capacity->capacity }}" required min="0">

        {{-- الأزرار --}}
        <button class="btn btn-warning px-4">💾 تحديث</button>

        <a href="{{ route('admin.capacities.index') }}"
           class="btn btn-secondary px-4">
           رجوع
        </a>

    </form>

</div>

@endsection
