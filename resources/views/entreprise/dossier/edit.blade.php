@extends('layouts.app')

@section('content')
<div class="container py-4" style="direction:rtl;text-align:right;max-width:1000px">

    <h3 class="fw-bold mb-4">✏️ تعديل ملف المؤسسة</h3>

    <form action="{{ route('entreprise.dossier.update') }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @php
        $files = [
            'registre_commerce'      => 'السجل التجاري',
            'statut'                 => 'القانون الأساسي للمؤسسة',
            'tax_number'             => 'البطاقة الجبائية',
            'bank_rib'               => 'شهادة RIB بنكي',
            'insurance_certificate'  => 'شهادة التأمين',
            'rules_book'             => 'دفتر الشروط',
            'minutes_meeting'        => 'محضر الجمعية (إن وجد)',
            'exploitation_request'   => 'طلب الاستغلال'
        ];

        $attachments = json_decode($enterprise->attachments ?? '{}', true);
        @endphp

        <div class="row g-3">
        @foreach($files as $key => $label)
            <div class="col-md-6">
                <label class="fw-bold">{{ $label }}</label>

                <input type="file"
                       name="{{ $key }}"
                       class="form-control">

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

        <div class="mt-5 d-flex justify-content-between">
            <a href="{{ route('entreprise.dossier.index') }}"
               class="btn btn-secondary">
               ⬅ رجوع
            </a>

            <button class="btn btn-success px-4 fw-bold">
                💾 حفظ وإرسال
            </button>
        </div>

    </form>

</div>
@endsection
