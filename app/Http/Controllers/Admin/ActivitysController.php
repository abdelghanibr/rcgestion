<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
 use App\Models\ActivityCategory;

class ActivitysController extends Controller
{
    /**
     * 📌 عرض قائمة الأنشطة
     */
   
public function index()
{
    $activities = Activity::with('activityCategory')->get();
    return view('admin.activities.index', compact('activities'));
}


    /**
     * 📌 عرض صفحة إضافة نشاط جديد
     */
    public function create()
{
    $activityCategories = ActivityCategory::orderBy('name')->get();

    return view('admin.activities.create', compact(
        'activityCategories'
    ));
}

    /**
     * 📌 حفظ نشاط جديد
     */
   public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'icon'  => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        'description' => 'nullable|string',
        'is_active' => 'nullable|boolean',
        'color' => 'nullable|string|max:20',
        'activity_category_id' => 'required|exists:activity_categories,id',
    ]);


        if (app()->environment('local')) {
        $storagePath = storage_path('app/public');
        $storageUrl  = '/storage';
    } else {
        $storagePath = rtrim(env('PUBLIC_STORAGE_PATH'), '/');
        $storageUrl  = rtrim(env('PUBLIC_STORAGE_URL'), '/');
    }
 

    $iconUrl = null;

    if ($request->hasFile('icon')) {
        $file = $request->file('icon');
        $iconName = time() . '_' . $file->getClientOriginalName();
        $file->move($storagePath . '/photos', $iconName);
        $iconUrl = $storageUrl . '/photos/' . $iconName;
    }

    Activity::create([
        'title' => $request->title,
   
        'description' => $request->description,
        'color' => $request->color,
        'icon' => $iconUrl,
        'activity_category_id' => $request->activity_category_id,
        'is_active' => $request->has('is_active') ? $request->is_active : 0,
    ]);

    return redirect()
        ->route('admin.activities.index')
        ->with('success', 'تمت إضافة النشاط بنجاح 🎉');
}


    /**
     * 📌 عرض صفحة التعديل
     */
 public function edit($id)
{
    $activity = Activity::findOrFail($id);

    $activityCategories = ActivityCategory::orderBy('name')->get();

    return view('admin.activities.edit', compact(
        'activity',
        'activityCategories'
    ));
}
    /**
     * 📌 تحديث بيانات النشاط
     */
public function update(Request $request, $id)
{
    $activity = Activity::findOrFail($id);

    $request->validate([
        'title' => 'required|string|max:255',
        'icon'  => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
        'description' => 'nullable|string',
        'is_active' => 'nullable|boolean',
        'color' => 'nullable|string',
        'activity_category_id' => 'nullable|exists:activity_categories,id',
    ]);

    // === تحديد مسار التخزين حسب البيئة ===
    if (app()->environment('local')) {
        $storagePath = storage_path('app/public');
        $storageUrl  = '/storage';
    } else {
        $storagePath = rtrim(env('PUBLIC_STORAGE_PATH'), '/');
        $storageUrl  = rtrim(env('PUBLIC_STORAGE_URL'), '/');
    }

    // الاحتفاظ بالأيقونة الحالية افتراضيًا
    $iconUrl = $activity->icon;

    // إذا تم رفع صورة جديدة
    if ($request->hasFile('icon')) {

        // 🗑️ حذف الصورة القديمة إن وُجدت
        if ($activity->icon) {
            $oldPath = public_path($activity->icon);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // 📥 حفظ الصورة الجديدة
        $file = $request->file('icon');
        $iconName = time() . '_' . $file->getClientOriginalName();
        $file->move($storagePath . '/photos', $iconName);

        $iconUrl = $storageUrl . '/photos/' . $iconName;
    }

    // تحديث البيانات
    $activity->update([
        'title' => $request->title,
        'description' => $request->description,
        'color' => $request->color,
        'icon' => $iconUrl, // إما القديمة أو الجديدة
        'activity_category_id' => $request->activity_category_id,
        'is_active' => $request->has('is_active') ? 1 : 0,
    ]);

    return redirect()
        ->route('admin.activities.index')
        ->with('success', '✏️ تم تحديث النشاط بنجاح');
}



    /**
     * 📌 حذف النشاط
     */
    public function destroy($id)
    {
        Activity::findOrFail($id)->delete();

        return redirect()->route('admin.activities.index')
            ->with('success', '🗑 تم حذف النشاط بنجاح');
    }
}
