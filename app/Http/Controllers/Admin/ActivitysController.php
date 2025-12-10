<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivitysController extends Controller
{
    /**
     * 📌 عرض قائمة الأنشطة
     */
    public function index()
    {
        $activities = Activity::orderBy('id', 'DESC')->get();
        return view('admin.activities.index', compact('activities'));
    }

    /**
     * 📌 عرض صفحة إضافة نشاط جديد
     */
    public function create()
    {
        return view('admin.activities.create');
    }

    /**
     * 📌 حفظ نشاط جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        Activity::create($request->all());

        return redirect()->route('admin.activities.index')
            ->with('success', '✔ تم إضافة النشاط بنجاح');
    }

    /**
     * 📌 عرض صفحة التعديل
     */
    public function edit($id)
    {
        $activity = Activity::findOrFail($id);
        return view('admin.activities.edit', compact('activity'));
    }

    /**
     * 📌 تحديث بيانات النشاط
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'color' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $activity = Activity::findOrFail($id);
        $activity->update($request->all());

        return redirect()->route('admin.activities.index')
            ->with('success', '✔ تم تحديث بيانات النشاط بنجاح');
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
