<form action="{{ route('profile.step.save', 1) }}" method="POST">
    @csrf

    <h4 class="mb-4 fw-bold">المعلومات الأساسية</h4>

    <div class="row">

        <!-- الاسم -->
        <div class="col-md-6 mb-3">
            <label class="form-label">الاسم</label>
            <input type="text" name="firstname"
                   class="form-control @error('firstname') is-invalid @enderror"
                   value="{{ old('firstname', $person->firstname ?? '') }}">
            @error('firstname')
                <div class="form-error text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <!-- اللقب -->
        <div class="col-md-6 mb-3">
            <label class="form-label">اللقب</label>
            <input type="text" name="lastname"
                   class="form-control @error('lastname') is-invalid @enderror"
                   value="{{ old('lastname', $person->lastname ?? '') }}">
            @error('lastname')
                <div class="form-error text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <!-- تاريخ الميلاد -->
        <div class="col-md-6 mb-3">
            <label class="form-label">تاريخ الميلاد</label>
            <input type="date" name="birth_date"
                   class="form-control @error('birth_date') is-invalid @enderror"
                   value="{{ old('birth_date', $person->birth_date ?? '') }}">
            @error('birth_date')
                <div class="form-error text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <!-- الجنس -->
      <div class="col-md-6 mb-3">
    <label class="form-label d-block">الجنس</label>

    <label class="ms-3">
        <input type="radio" name="gender" value="H"
               {{ old('gender', $person->gender ?? '') == 'H' ? 'checked' : '' }}>
        ذكر
    </label>

    <label class="ms-3">
        <input type="radio" name="gender" value="F"
               {{ old('gender', $person->gender ?? '') == 'F' ? 'checked' : '' }}>
        أنثى
    </label>

    @error('gender')
        <div class="form-error text-danger small">{{ $message }}</div>
    @enderror
</div>


        <!-- احتياجات خاصة -->
        <div class="col-md-6 mb-3">
            <label class="form-label d-block">هل لديك احتياجات خاصة؟</label>

            <label class="ms-3">
                <input type="radio" name="handicap" value="1"
                {{ old('handicap', $person->handicap ?? '') == 1 ? 'checked' : '' }}>
                نعم
            </label>

            <label class="ms-3">
                <input type="radio" name="handicap" value="0"
                {{ old('handicap', $person->handicap ?? '') == 0 ? 'checked' : '' }}>
                لا
            </label>

            @error('handicap')
                <div class="form-error text-danger small">{{ $message }}</div>
            @enderror
        </div>

    </div>

    <div class="mt-4 text-center">
        <button class="btn btn-success px-5">التالي</button>
    </div>

</form>
