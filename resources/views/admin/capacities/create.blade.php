@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl; text-align:right">

    <h3 class="fw-bold mb-4">➕ إضافة سعة جديدة</h3>

    {{-- عرض الأخطاء --}}
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

    <form action="{{ route('admin.capacities.store') }}" method="POST">
        @csrf

        {{-- المركب --}}
        <label class="form-label fw-bold">🏟️ المركّب</label>
        <select name="complex_id" class="form-control mb-3" required>
            <option value="">-- اختر المركب --</option>
            @foreach($complexes as $c)
                <option value="{{ $c->id }}">{{ $c->nom }}</option>
            @endforeach
        </select>

        {{-- النشاط --}}
        <label class="form-label fw-bold">🏊‍♂️ النشاط</label>
        <select name="activity_id" class="form-control mb-3" required>
            <option value="">-- اختر النشاط --</option>
            @foreach($activities as $a)
                <option value="{{ $a->id }}">{{ $a->title }}</option>
            @endforeach
        </select>

      

        {{-- السعة --}}
        <label class="form-label fw-bold">👥 السعة القصوى</label>
        <input type="number" name="capacity" class="form-control mb-4" required min="0"
               placeholder="مثال: 25">

        {{-- الأزرار --}}
        <button type="submit" class="btn btn-primary px-4">💾 حفظ</button>

        <a href="{{ route('admin.capacities.index') }}" class="btn btn-secondary px-4">
            رجوع
        </a>

    </form>

</div>

@endsection
