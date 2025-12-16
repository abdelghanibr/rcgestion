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
{
    try {

        // 🟦 1) Validation complète
        $validated = $request->validate([
            'complex_id'         => 'required|integer',
            'activity_id'        => 'required|integer',
          //  'age_category_id'    => 'required|integer',
            'groupe'             => 'required|string|max:50',
            'sex'                => 'required|in:H,F,X',
            'nbr'                => 'nullable|integer|min:0',
            'type_prix'          => 'required|in:pricing_plan,fix',
            'price'              => 'nullable|numeric|min:0',
            'user_id'            => 'nullable|integer|exists:users,id',
            'time_slots'         => 'required|json',
        ]);

        // 🟦 2) extraire complex_activity_id
        $complexActivity = ComplexActivity::where('complex_id', $request->complex_id)
                                          ->where('activity_id', $request->activity_id)
                                          ->first();

        if (!$complexActivity) {
            return back()->withErrors([
                'complex_id' => '❌ هذا النشاط غير مرتبط بهذا المركب. يجب إضافته أولاً في complex_activities'
            ])->withInput();
        }

        // 🟦 3) créer schedule
        $schedule = new Schedule();
        $schedule->complex_activity_id = $complexActivity->id;
        $schedule->age_category_id     = $request->age_category_id;
        $schedule->groupe              = $request->groupe;
        $schedule->sex                 = $request->sex;
        $schedule->nbr                 = $request->nbr;
        $schedule->type_prix           = $request->type_prix;
        $schedule->price               = $request->type_prix == "fix" ? $request->price : null;
        $schedule->user_id             = $request->user_id;
        $schedule->time_slots          = $request->time_slots; // JSON

        $schedule->save();

        return redirect()->route('admin.schedules.index')
                         ->with('success', '✔ تم حفظ الجدول بنجاح');

    } catch (\Exception $e) {

        return back()->with('error', '❌ خطأ أثناء حفظ البيانات: ' . $e->getMessage())
                     ->withInput();
    }
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



public function occupiedSlots(Request $request)
{
    $request->validate([
        'complex_id'  => 'required|integer',
        'activity_id' => 'required|integer',
    ]);

    // 🔗 إيجاد complex_activity_id
    $complexActivity = ComplexActivity::where('complex_id', $request->complex_id)
        ->where('activity_id', $request->activity_id)
        ->first();

    if (!$complexActivity) {
        return response()->json([]);
    }

    // 📦 جلب الجداول المرتبطة
    $schedules = Schedule::where('complex_activity_id', $complexActivity->id)
        ->whereNotNull('time_slots')
        ->get();

    $events = [];

    foreach ($schedules as $schedule) {
        $slots = json_decode($schedule->time_slots, true);

        if (!is_array($slots)) continue;

        foreach ($slots as $slot) {

            // day_number: 0=الأحد ... 6=السبت
            $events[] = [
                'daysOfWeek' => [(int)$slot['day_number']],
                'startTime'  => $slot['start'],
                'endTime'    => $slot['end'],
                'display'    => 'background',
                'backgroundColor' => '#dc3545',
                'borderColor'     => '#dc3545',
                'extendedProps' => [
                    'groupe' => $schedule->groupe, // 👈 اسم المجموعة
                ],
            ];
        }
    }

    return response()->json($events);
}


    /**
     * تعديل الجدول
     */
    public function update(Request $request, $id)
{
    try {

        // 🟦 1) Validation complète
        $validated = $request->validate([
            'complex_id'         => 'required|integer',
            'activity_id'        => 'required|integer',
           // 'age_category_id'    => 'required|integer',
            'groupe'             => 'required|string|max:50',
            'sex'                => 'required|in:H,F,X',
            'nbr'                => 'nullable|integer|min:0',
            'type_prix'          => 'required|in:pricing_plan,fix',
            'price'              => 'nullable|numeric|min:0',
            'user_id'            => 'nullable|integer|exists:users,id',
            'time_slots'         => 'required|json',
        ]);

        $schedule = Schedule::findOrFail($id);

        // 🟦 2) Extraire complex_activity_id
        $complexActivity = ComplexActivity::where('complex_id', $request->complex_id)
                                          ->where('activity_id', $request->activity_id)
                                          ->first();

        if (!$complexActivity) {
            return back()->withErrors([
                'complex_id' => '❌ هذا النشاط غير مرتبط بهذا المركب. يجب إضافته أولاً في complex_activities'
            ])->withInput();
        }

        // 🟦 3) Mise à jour des champs
        $schedule->complex_activity_id = $complexActivity->id;
        $schedule->age_category_id     = $request->age_category_id;
        $schedule->groupe              = $request->groupe;
        $schedule->sex                 = $request->sex;
        $schedule->nbr                 = $request->nbr;
        $schedule->type_prix           = $request->type_prix;
        $schedule->price               = $request->type_prix == "fix" ? $request->price : null;
        $schedule->user_id             = $request->user_id;
        $schedule->time_slots          = $request->time_slots;

        $schedule->save();

        return redirect()->route('admin.schedules.index')
                         ->with('success', '✔ تم تعديل الجدول بنجاح');

    } catch (\Exception $e) {

        return back()->with('error', '❌ فشل تحديث الجدول: ' . $e->getMessage())
                     ->withInput();
    }
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
