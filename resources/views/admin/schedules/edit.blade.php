@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction: rtl; text-align:right;">

    <h3 class="mb-4">✏ تعديل الجدول</h3>

    <form action="{{ route('admin.schedules.update', $schedule->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- اختيار المركب --}}
        <div class="mb-3">
            <label>المركب</label>
            <select id="complex" class="form-control" required>
                @foreach($complexes as $cx)
                <option value="{{ $cx->id }}" {{ $selected_complex == $cx->id ? 'selected' : '' }}>
                    {{ $cx->name }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- اختيار النشاط --}}
        <div class="mb-3">
            <label>النشاط</label>
            <select id="activity" class="form-control" required>
                @foreach($activities as $a)
                <option value="{{ $a->id }}" {{ $selected_activity == $a->id ? 'selected' : '' }}>
                    {{ $a->title }}
                </option>
                @endforeach
            </select>
        </div>

        <input type="hidden" name="complex_activity_id" id="complex_activity_id" value="{{ $schedule->complex_activity_id }}">

        {{-- الفئة العمرية --}}
        <div class="mb-3">
            <label>الفئة العمرية</label>
            <select name="age_category_id" class="form-control">
                @foreach($ageCategories as $cat)
                <option value="{{ $cat->id }}" {{ $schedule->age_category_id == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- المجموعة --}}
        <div class="mb-3">
            <label>المجموعة</label>
            <input type="text" name="groupe" class="form-control" value="{{ $schedule->groupe }}">
        </div>

        {{-- اليوم --}}
        <div class="mb-3">
            <label>اليوم</label>
            <select name="day_of_week" class="form-control">
                @foreach(['dimanche'=>'الأحد','lundi'=>'الإثنين','mardi'=>'الثلاثاء','mercredi'=>'الأربعاء','jeudi'=>'الخميس','vendredi'=>'الجمعة','samedi'=>'السبت'] as $key => $label)
                <option value="{{ $key }}" {{ $schedule->day_of_week == $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- الساعات --}}
        <div class="row">
            <div class="col">
                <label>من</label>
                <input type="time" name="heure_debut" class="form-control" value="{{ $schedule->heure_debut }}">
            </div>
            <div class="col">
                <label>إلى</label>
                <input type="time" name="heure_fin" class="form-control" value="{{ $schedule->heure_fin }}">
            </div>
        </div>

        {{-- العدد --}}
        <div class="mt-3">
            <label>عدد الأماكن</label>
            <input type="number" name="nbr" class="form-control" value="{{ $schedule->nbr }}">
        </div>

        {{-- الجنس --}}
        <div class="mb-3 mt-3">
            <label>الجنس</label>
            <select name="sex" class="form-control">
                <option value="H" {{ $schedule->sex == 'H' ? 'selected' : '' }}>ذكور</option>
                <option value="F" {{ $schedule->sex == 'F' ? 'selected' : '' }}>إناث</option>
                <option value="X" {{ $schedule->sex == 'X' ? 'selected' : '' }}>مختلط</option>
            </select>
        </div>

        <button class="btn btn-primary px-4">💾 حفظ التعديلات</button>

    </form>

</div>

<script>
function loadCombo() {
    let complex = document.getElementById("complex").value;
    let activity = document.getElementById("activity").value;

    if (complex && activity) {
        fetch(`{{ route('admin.getComplexActivity') }}?complex_id=${complex}&activity_id=${activity}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById("complex_activity_id").value = data.id;
            });
    }
}

document.getElementById("complex").addEventListener("change", loadCombo);
document.getElementById("activity").addEventListener("change", loadCombo);
</script>

@endsection
