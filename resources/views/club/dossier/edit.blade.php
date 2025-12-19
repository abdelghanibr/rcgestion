@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction:rtl;text-align:right;max-width:1000px">

<h3 class="fw-bold mb-4">✏️ تعديل ملف النادي</h3>

{{-- ================= FORM ================= --}}
<form id="dossierForm"
      action="{{ route('club.dossier.update') }}"
      method="POST"
      enctype="multipart/form-data">
@csrf
@method('PUT')

@php
$files = [
    'agrement' => 'اعتماد النادي',
    'statut' => 'القانون الأساسي',
    'bureau_members' => 'قائمة أعضاء المكتب',
    'coaches_certificates' => 'شهادات المدربين',
    'federation_affiliation' => 'شهادة الانخراط في الرابطة',
    'insurance_certificate' => 'شهادة التأمين',
    'rules_book' => 'دفتر الشروط',
    'minutes_meeting' => 'محضر الجمعية',
    'exploitation_request' => 'طلب الاستغلال'
];

$attachments = json_decode($club->attachments ?? '{}', true);
@endphp

<div class="row g-3">
@foreach($files as $key => $label)
    <div class="col-md-6">
        <label class="fw-bold mb-1 d-block">{{ $label }}</label>

        <input type="file"
               name="{{ $key }}"
               class="form-control">

        {{-- عرض الملف الحالي إن وُجد --}}
        @if(isset($attachments[$key]))
            <a href="{{ asset($attachments[$key]) }}"
               target="_blank"
               class="btn btn-sm btn-outline-success mt-1">
               👁 عرض الملف الحالي
            </a>
        @endif
    </div>
@endforeach
</div>

{{-- ================= PROGRESS BAR ================= --}}
<div class="mt-4 d-none" id="uploadProgressWrapper">
    <div class="progress" style="height:26px;border-radius:30px;">
        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
             role="progressbar"
             style="width:0%"
             id="uploadProgressBar">
            0%
        </div>
    </div>
    <small class="text-muted d-block mt-1">
        ⏳ جارٍ حفظ الملفات…
    </small>
</div>

{{-- ================= ACTIONS ================= --}}
<div class="mt-5 d-flex justify-content-between">
    <a href="{{ route('club.dossier.index') }}"
       class="btn btn-secondary px-4">
       ⬅ رجوع
    </a>

    <button type="submit"
            class="btn btn-success px-5 fw-bold">
        💾 حفظ وإرسال
    </button>
</div>

</form>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
document.getElementById('dossierForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    const wrapper = document.getElementById('uploadProgressWrapper');
    const bar = document.getElementById('uploadProgressBar');

    // إظهار Progress
    wrapper.classList.remove('d-none');
    bar.style.width = '0%';
    bar.textContent = '0%';
    bar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);

    xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            bar.style.width = percent + '%';
            bar.textContent = percent + '%';
        }
    };

    xhr.onload = function () {
        if (xhr.status === 200) {
            bar.classList.remove('progress-bar-animated');
            bar.classList.replace('bg-primary', 'bg-success');
            bar.textContent = '✅ تم الحفظ بنجاح';

            setTimeout(() => {
                window.location.href = "{{ route('club.dossier.index') }}";
            }, 900);
        } else {
            bar.classList.remove('progress-bar-animated');
            bar.classList.replace('bg-primary', 'bg-danger');
            bar.textContent = '❌ خطأ أثناء الحفظ';
        }
    };

    xhr.onerror = function () {
        bar.classList.remove('progress-bar-animated');
        bar.classList.replace('bg-primary', 'bg-danger');
        bar.textContent = '❌ فشل الاتصال';
    };

    xhr.send(formData);
});
</script>
@endsection
