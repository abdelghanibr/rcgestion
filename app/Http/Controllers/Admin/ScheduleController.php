<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\AgeCategory;
use App\Models\Complex;
use App\Models\Activity;
use App\Models\ComplexActivity;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
   public function index()
{
    $schedules = Schedule::with([
        'complexActivity.complex',
        'complexActivity.activity',
        'ageCategory'
    ])->get();

    // هذه هي البيانات المطلوبة للفلاتر في الـ Blade
    $complexes = \App\Models\Complex::all();
    $activities = \App\Models\Activity::all();

    return view('admin.schedules.index', compact('schedules', 'complexes', 'activities'));
}


    public function create()
    {
        $ageCategories = AgeCategory::all();
        $complexes = Complex::all();
        $activities = Activity::all();

        return view('admin.schedules.create', compact(
            'ageCategories',
            'complexes',
            'activities'
        ));
    }

    public function store(Request $request)
    {//dd($request);
        $request->validate([
            'complex_id' => 'required|integer',
            'activity_id' => 'required|integer',
            'age_category_id' => 'required|integer',
            'groupe' => 'required|string',
            'day_of_week' => 'required|string',
            'heure_debut' => 'required',
            'heure_fin' => 'required',
            'nbr' => 'nullable|integer',
            'sex' => 'required|in:H,F,X',
        ]);

        // استخراج complex_activity_id
        $complexActivity = ComplexActivity::where('complex_id', $request->complex_id)
                                          ->where('activity_id', $request->activity_id)
                                          ->first();
//dd($complexActivity);

        if (!$complexActivity) {
            return back()->withErrors([
                'msg' => '❌ هذا النشاط غير مسجل داخل هذا المركب. قم بإضافته في complex_activities أولاً.'
            ]);
        }

        Schedule::create([
            'complex_activity_id' => $complexActivity->id,
            'age_category_id' => $request->age_category_id,
            'groupe' => $request->groupe,
            'day_of_week' => $request->day_of_week,
            'heure_debut' => $request->heure_debut,
            'heure_fin' => $request->heure_fin,
            'nbr' => $request->nbr,
            'sex' => $request->sex,
        ]);

        return redirect()->route('admin.schedules.index')
                         ->with('success', '✔ تم إضافة الجدول بنجاح');
    }

    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);

        $ageCategories = AgeCategory::all();
        $complexes = Complex::all();
        $activities = Activity::all();

        // استخراج complex_id و activity_id الحاليين
        $ca = ComplexActivity::find($schedule->complex_activity_id);

        $selected_complex = $ca ? $ca->complex_id : null;
        $selected_activity = $ca ? $ca->activity_id : null;

        return view('admin.schedules.edit', compact(
            'schedule',
            'ageCategories',
            'complexes',
            'activities',
            'selected_complex',
            'selected_activity'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'complex_id' => 'required|integer',
            'activity_id' => 'required|integer',
            'age_category_id' => 'required|integer',
            'groupe' => 'required|string',
            'day_of_week' => 'required|string',
            'heure_debut' => 'required',
            'heure_fin' => 'required',
            'nbr' => 'nullable|integer',
            'sex' => 'required|in:H,F,X',
        ]);

        $schedule = Schedule::findOrFail($id);

        // استخراج complex_activity_id الجديد
        $complexActivity = ComplexActivity::where('complex_id', $request->complex_id)
                                          ->where('activity_id', $request->activity_id)
                                          ->first();

        if (!$complexActivity) {
            return back()->withErrors([
                'msg' => '❌ هذا النشاط غير مرتبط بهذا المركب. قم بإضافته أولاً في complex_activities.'
            ]);
        }

        $schedule->update([
            'complex_activity_id' => $complexActivity->id,
            'age_category_id' => $request->age_category_id,
            'groupe' => $request->groupe,
            'day_of_week' => $request->day_of_week,
            'heure_debut' => $request->heure_debut,
            'heure_fin' => $request->heure_fin,
            'nbr' => $request->nbr,
            'sex' => $request->sex,
        ]);

        return redirect()->route('admin.schedules.index')
                         ->with('success', '✔ تم تعديل الجدول بنجاح');
    }

    public function destroy($id)
    {
        Schedule::destroy($id);

        return redirect()->route('admin.schedules.index')
                         ->with('success', '🗑 تم الحذف بنجاح');
    }
}
