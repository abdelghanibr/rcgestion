@php
    // تاريخ الميلاد (إن وجد)
    $birthDate = old('birth_date', $person->birth_date ?? null);

    // الوثائق الموجودة (إن وُجد dossier)
    $attachments = [];

    if (isset($dossier) && $dossier && $dossier->attachments) {
        $attachments = is_array($dossier->attachments)
            ? $dossier->attachments
            : json_decode($dossier->attachments, true);
    }
@endphp


<form action="{{ route('profile.step.save', 4) }}"
      method="POST"
      enctype="multipart/form-data">
    @csrf

    <h4 class="mb-4 fw-bold">الوثائق المطلوبة</h4>

    <div class="row">

        {{-- ================= شهادة طبية (للجميع) ================= --}}
        <div class="col-md-6 mb-3">
            <label>
                🩺 شهادة طبية / صدرية
                @if(isset($attachments['medical_certificate']))
                    <a href="{{ $attachments['medical_certificate'] }}"
                       target="_blank"
                       class="btn btn-outline-success btn-sm ms-2">
                        👁 عرض
                    </a>
                @endif
            </label>

            <input type="file"
                   name="medical_certificate"
                   class="form-control @error('medical_certificate') is-invalid @enderror">

            @error('medical_certificate')
                <div class="form-error text-danger small">{{ $message }}</div>
            @enderror
        </div>

            {{-- تعهد --}}
            <div class="col-md-6 mb-3">
                <label>
                    ✍️ تعهد
                    @if(isset($attachments['engagement']))
                        <a href="{{ $attachments['engagement'] }}"
                           target="_blank"
                           class="btn btn-outline-success btn-sm ms-2">
                            👁 عرض
                        </a>
                    @endif
                </label>

                <input type="file" name="engagement"
                       class="form-control @error('engagement') is-invalid @enderror">
                @error('engagement')
                    <div class="form-error text-danger small">{{ $message }}</div>
                @enderror
            </div>
             {{-- صورة شمسية --}}
   {{-- صورة شمسية --}}
<div class="col-md-6 mb-3">
    <label class="fw-bold">📷 صورة شمسية</label>

{{-- معاينة الصورة (حالية أو مختارة) --}}
<div class="mb-2 text-center">

    <img id="photoPreview"
         src="{{ isset($attachments['photo']) ? asset($attachments['photo']) : '' }}"
         style="
            width:120px;
            height:120px;
            object-fit:cover;
            border-radius:10px;
            border:2px solid #0d6efd;
            background:#f8f9fa;
            display: {{ isset($attachments['photo']) ? 'inline-block' : 'none' }};
         ">

    {{-- زر عرض الصورة الحالية --}}
    @if(isset($attachments['photo']))
        <div class="mt-1">
            <a href="{{ asset($attachments['photo']) }}"
               target="_blank"
               class="btn btn-outline-success btn-sm">
                👁 عرض الصورة الحالية
            </a>
        </div>
    @endif

</div>


    {{-- متطلبات الصورة --}}
    <div class="alert alert-info py-2 small">
        📌 <strong>شروط الصورة:</strong>
        <ul class="mb-0 ps-3">
            <li>خلفية <strong>بيضاء</strong> وواضحة</li>
            <li>الوجه ظاهر بوضوح (بدون قبعة أو نظارات شمسية)</li>
            <li>الصيغة: JPG أو PNG</li>
            <li>القياس المقترح: مربعة (مثلاً 300×300 أو أكثر)</li>
            <li>الحجم الأقصى: <strong>2 ميغابايت</strong></li>
        </ul>
    </div>

    {{-- حقل الرفع --}}
  <input type="file"
       name="photo"
       id="photoInput"
       accept="image/jpeg,image/png"
       class="form-control @error('photo') is-invalid @enderror">


    @error('photo')
        <div class="form-error text-danger small">{{ $message }}</div>
    @enderror
</div>

        {{-- ================= تاريخ الميلاد (مخفي) ================= --}}
        <input type="hidden"
               name="birth_date"
               id="birth_date"
               value="{{ $birthDate }}">

        {{-- ================= وثائق القاصر ================= --}}
        <div id="minor-docs" style="display:none">

            {{-- شهادة الميلاد --}}
            <div class="col-md-6 mb-3">
                <label>
                    📄 شهادة الميلاد
                    @if(isset($attachments['birth_certificate']))
                        <a href="{{ $attachments['birth_certificate'] }}"
                           target="_blank"
                           class="btn btn-outline-success btn-sm ms-2">
                            👁 عرض
                        </a>
                    @endif
                </label>

                <input type="file" name="birth_certificate"
                       class="form-control @error('birth_certificate') is-invalid @enderror">
                @error('birth_certificate')
                    <div class="form-error text-danger small">{{ $message }}</div>
                @enderror
            </div>

           

            {{-- تصريح أبوي --}}
            <div class="col-md-6 mb-3">
                <label>
                    📝 تصريح أبوي
                    @if(isset($attachments['parental_authorization']))
                        <a href="{{ $attachments['parental_authorization'] }}"
                           target="_blank"
                           class="btn btn-outline-success btn-sm ms-2">
                            👁 عرض
                        </a>
                    @endif
                </label>

                <input type="file" name="parental_authorization"
                       class="form-control @error('parental_authorization') is-invalid @enderror">
                @error('parental_authorization')
                    <div class="form-error text-danger small">{{ $message }}</div>
                @enderror
            </div>

            {{-- بطاقة الولي --}}
            <div class="col-md-6 mb-3">
                <label>
                    🪪 بطاقة التعريف الوطنية للولي
                    @if(isset($attachments['guardian_id_card']))
                        <a href="{{ $attachments['guardian_id_card'] }}"
                           target="_blank"
                           class="btn btn-outline-success btn-sm ms-2">
                            👁 عرض
                        </a>
                    @endif
                </label>

                <input type="file" name="guardian_id_card"
                       class="form-control @error('guardian_id_card') is-invalid @enderror">
                @error('guardian_id_card')
                    <div class="form-error text-danger small">{{ $message }}</div>
                @enderror
            </div>

        </div>

        {{-- ================= وثائق البالغ ================= --}}
        <div id="adult-docs" style="display:none">

            {{-- بطاقة تعريف --}}
            <div class="col-md-6 mb-3">
                <label>
                    🪪 بطاقة التعريف الوطنية
                    @if(isset($attachments['national_id_card']))
                        <a href="{{ $attachments['national_id_card'] }}"
                           target="_blank"
                           class="btn btn-outline-success btn-sm ms-2">
                            👁 عرض
                        </a>
                    @endif
                </label>

                <input type="file" name="national_id_card"
                       class="form-control @error('national_id_card') is-invalid @enderror">
                @error('national_id_card')
                    <div class="form-error text-danger small">{{ $message }}</div>
                @enderror
            </div>


        </div>

    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('profile.step', 3) }}" class="btn btn-secondary px-4">
            السابق
        </a>
        <button class="btn btn-success px-4">
            إنهاء
        </button>
    </div>

</form>

{{-- ================= JavaScript ================= --}}
<script>

document.getElementById('photoInput')?.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();

    reader.onload = function (event) {
        const preview = document.getElementById('photoPreview');
        preview.src = event.target.result;
        preview.style.display = 'inline-block';
    };

    reader.readAsDataURL(file);
});



function calculateAge(birthDate) {
    const today = new Date();
    const birth = new Date(birthDate);

    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();

    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
}

function toggleDocuments() {
    const birthInput = document.getElementById('birth_date');
    if (!birthInput || !birthInput.value) return;

    const age = calculateAge(birthInput.value);

    const minorDocs = document.getElementById('minor-docs');
    const adultDocs = document.getElementById('adult-docs');

    if (age < 18) {
        minorDocs.style.display = 'block';
        adultDocs.style.display = 'none';
    } else {
        minorDocs.style.display = 'none';
        adultDocs.style.display = 'block';
    }
}







// عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', toggleDocuments);




</script>
