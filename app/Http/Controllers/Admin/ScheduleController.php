<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\AgeCategory;
use App\Models\Complex;
use App\Models\Activity;
use App\Models\ComplexActivity;
use App\Models\User;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * قائمة الجداول الزمنية
     */
    public function index()
    {
        $schedules = Schedule::with([
            'complexActivity.complex',
            'complexActivity.activity',
            'ageCategory',
            'user'
        ])->get();

        $complexes = Complex::all();
        $activities = Activity::all();

        return view('admin.schedules.index', compact('schedules', 'complexes', 'activities'));
    }


    /**
     * صفحة إنشاء جدول جديد
     */
    public function create()
    {
        $ageCategories = AgeCategory::all();
        $complexes = Complex::all();
        $activities = Activity::all();
        $users = User::whereIn('type', ['club', 'company'])->get(); // user_id اختياري

        return view('admin.schedules.create', compact(
            'ageCategories',
            'complexes',
            'activities',
            'users'
        ));
    }


    /**
     * حفظ جدول جديد
     */
    public function store(Request $request)
{ //dd($request->all());

    $request->validate([
        'complex_id' => 'required',
        'activity_id' => 'required',
       // 'complex_activity_id' => 'required',
        'age_category_id' => 'required',
        'groupe' => 'required',
        'sex' => 'required|in:H,F,X',
        'nbr' => 'nullable|integer',
        'time_slots' => 'required'
    ]);

   // dd($request->all());

// 🟦 1) استخراج complex_activity_id تلقائياً
$complexActivity = \App\Models\ComplexActivity::where('complex_id', $request->complex_id)
                    ->where('activity_id', $request->activity_id)
                    ->first();

if (!$complexActivity) {
    return back()->with('error', '⚠ هذا النشاط غير مضاف داخل هذا المركب! يجب إضافته أولاً في Complex Activities.');
}

Schedule::create([
    'complex_id'        => $request->complex_id,
    'activity_id'       => $request->activity_id,
    'complex_activity_id' => $complexActivity->id,  // 🎯 حل المشكلة هنا
    'age_category_id'   => $request->age_category_id,
    'groupe'            => $request->groupe,
    'sex'               => $request->sex,
    'nbr'               => $request->nbr,
    'time_slots'        => $request->time_slots, // JSON محفوظ كما هو
]);


    return redirect()->route('admin.schedules.index')
                     ->with('success', '✔ تم إنشاء الجدول بنجاح');
}


    /**
     * صفحة التعديل
     */
    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);

        $ageCategories = AgeCategory::all();
        $complexes = Complex::all();
        $activities = Activity::all();
        $users = User::whereIn('type', ['club', 'company'])->get();

        // استخراج بيانات complex_id + activity_id الحالية
        $ca = ComplexActivity::find($schedule->complex_activity_id);

        $selected_complex = $ca ? $ca->complex_id : null;
        $selected_activity = $ca ? $ca->activity_id : null;

        return view('admin.schedules.edit', compact(
            'schedule',
            'ageCategories',
            'complexes',
            'activities',
            'users',
            'selected_complex',
            'selected_activity'
        ));
    }


    /**
     * تعديل الجدول
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'complex_id'       => 'required|integer',
            'activity_id'      => 'required|integer',
          //  'age_category_id'  => 'required|integer',
            'groupe'           => 'required|string',
            'sex'              => 'required|in:H,F,X',
            'nbr'              => 'nullable|integer',
           // 'type_prix'        => 'required|in:pricing_plan,fix',
           // 'price'            => 'nullable|numeric',
            'time_slots'       => 'required|json',
          //  'user_id'          => 'nullable|integer',
        ]);

        $schedule = Schedule::findOrFail($id);

        // البحث عن complex_activity_id الجديد
        $complexActivity = ComplexActivity::where('complex_id', $request->complex_id)
                                          ->where('activity_id', $request->activity_id)
                                          ->first();

        if (!$complexActivity) {
            return back()->withErrors([
                'msg' => '❌ النشاط غير مرتبط بالمركب.'
            ]);
        }

        $schedule->update([
            'complex_activity_id' => $complexActivity->id,
            'age_category_id'     => $request->age_category_id,
            'groupe'              => $request->groupe,
            'sex'                 => $request->sex,
            'nbr'                 => $request->nbr,
            'type_prix'           => $request->type_prix,
            'price'               => $request->price,
            'time_slots'          => $request->time_slots,
            'user_id'             => $request->user_id,
        ]);

        return redirect()->route('admin.schedules.index')
                         ->with('success', '✔ تم تعديل الجدول بنجاح');
    }


    /**
     * حذف جدول
     */
    public function destroy($id)
    {
        Schedule::destroy($id);

        return redirect()->route('admin.schedules.index')
                         ->with('success', '🗑 تم الحذف بنجاح');
    }
}
