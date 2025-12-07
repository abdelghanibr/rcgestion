@extends('layouts.app')

@section('content')

<div class="container py-4" style="direction: rtl; text-align:right">

    <h3 class="mb-4 text-primary">🏢 قائمة {{ $type }}ـي المؤسسة</h3>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table id="personsTable" class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>اللقب</th>
                <th>العمر</th>
                <th>الجنس</th>
                <th>التصنيف</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($persons as $p)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $p->firstname }}</td>
                <td>{{ $p->lastname }}</td>
                <td>{{ \Carbon\Carbon::parse($p->birth_date)->age }} سنة</td>
                <td>{{ $p->gender }}</td>
                <td>{{ $p->education }}</td>
                <td>
                    <a href="{{ route('entreprise.persons.edit', $p->id) }}" class="btn btn-info btn-sm">تعديل</a>

                    <form action="{{ route('entreprise.persons.delete', $p->id) }}" method="POST"
                        style="display:inline-block;">
                        @csrf @method('DELETE')
                        <button onclick="return confirm('هل أنت متأكد من الحذف؟')"
                                class="btn btn-danger btn-sm">
                            حذف
                        </button>
                    </form>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#personsTable').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json" },
        dom: 'Bfrtip',
        buttons: ['excel', 'print', 'pageLength']
    });
});
</script>
@endpush
