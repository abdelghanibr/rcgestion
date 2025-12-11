<form action="{{ route('profile.step.save', 4) }}" method="POST" enctype="multipart/form-data">
    @csrf

    <h4 class="mb-4 fw-bold">الوثائق المطلوبة</h4>

    <div class="row">

       <div class="col-md-6 mb-3">

    <label>شهادة الميلاد (PDF)</label>

    {{-- إذا كانت الشهادة موجودة --}}
    @if(isset($person) && $person->birth_certificate)
        <div class="mb-2">
            <a href="{{ asset('storage/' . $person->birth_certificate) }}"
               class="btn btn-success btn-sm" target="_blank">
                📄 عرض شهادة الميلاد الحالية
            </a>
        </div>
    @endif

    <input type="file" name="birth_certificate"
           class="form-control @error('birth_certificate') is-invalid @enderror">

    @error('birth_certificate')
        <div class="form-error text-danger small">{{ $message }}</div>
    @enderror
</div>


     <div class="col-md-6 mb-3">

    <label>صورة شخصية</label>

    {{-- إذا كانت الصورة موجودة --}}
    @if(isset($person) && $person->photo)
        <div class="mb-2 text-center">
            <img src="{{ asset('storage/' . $person->photo) }}"
                 alt="الصورة الحالية"
                 style="width: 120px; height: 120px; object-fit: cover;
                        border-radius: 8px; border: 2px solid #ddd;">
        </div>
    @endif

    <input type="file" name="photo"
           class="form-control @error('photo') is-invalid @enderror">

    @error('photo')
        <div class="form-error text-danger small">{{ $message }}</div>
    @enderror
</div>


    </div>

    <div class="d-flex justify-content-between mt-3">
        <a href="{{ route('profile.step', 3) }}" class="btn btn-secondary px-4">السابق</a>
        <button class="btn btn-success px-4">إنهاء</button>
    </div>

</form>
