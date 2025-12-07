<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dossier;
use App\Models\Club;
use App\Models\Person;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        // السماح بدخول المسؤول فقط
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->type !== 'admin') {
                abort(403, 'غير مصرح لك بالدخول');
            }
            return $next($request);
        });
    }

    /**
     * 🔹 لوحة التحكم
     */
    public function dashboard()
    {
        // إحصائيات عامة
        $personsCount = person::count();
        $clubsCount   = User::where('type', 'club')->count();
        $adminsCount  = User::where('type', 'admin')->count();
        $dossiersCount = \App\Models\Dossier::count();

        return view('admin.dashboard', compact(
            'personsCount',
            'clubsCount',
            'adminsCount',
            'dossiersCount'
        ));
    }

    /**
     * 📂 عرض جميع الملفات للفلترة والإدارة
     */
    public function dossiersIndex()
    {
        $dossiers = Dossier::with('person.user')->latest()->get();

        return view('admin.dossiers.index', compact('dossiers'));
    }

    /**
     * ✔ قبول ملف
     */
    public function approveDossier($id)
    {
        $d = Dossier::findOrFail($id);
        $d->etat = 'approved';
        $d->note_admin = 'تم القبول من قبل الإدارة';
        $d->save();

        return redirect()->back()->with('success', 'تم قبول الملف بنجاح ✔');
    }

    /**
     * ❌ رفض ملف
     */
    public function rejectDossier($id)
    {
        $d = Dossier::findOrFail($id);
        $d->etat = 'rejected';
        $d->note_admin = 'تم الرفض من قبل الإدارة';
        $d->save();

        return redirect()->back()->with('error', 'تم رفض الملف ❌');
    }

    /**
     * 🏊‍♂️ عرض قائمة النوادي
     */
    public function clubsIndex()
    {
        $clubs = Club::with('user')->latest()->get();
        return view('admin.clubs.index', compact('clubs'));
    }

    /**
     * 👥 عرض جميع الأفراد (لاحقاً يمكنك تخصيصه أكثر)
     */
    public function personsIndex()
    {
        $persons = Person::with('user')->latest()->get();
        return view('admin.persons.index', compact('persons'));
    }

    public function adminsIndex()
{
    $admins = User::where('type', 'admin')->get();
    return view('admin.admins.index', compact('admins'));
}

// 📌 صفحة إنشاء مسؤول جديد
public function adminsCreate()
{
    return view('admin.admins.create');
}

// 📌 حفظ مسؤول جديد
public function adminsStore(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6'
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'type' => 'admin'
    ]);

    return redirect()->route('admins.index')->with('success', 'تم إضافة المسؤول بنجاح');
}

// 📌 صفحة تعديل
public function adminsEdit($id)
{
    $admin = User::findOrFail($id);
    return view('admin.admins.edit', compact('admin'));
}

// 📌 تحديث بيانات المسؤول
public function adminsUpdate(Request $request, $id)
{
    $admin = User::findOrFail($id);

    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users,email,'.$admin->id,
    ]);

    $admin->name = $request->name;
    $admin->email = $request->email;

    if($request->password){
        $admin->password = Hash::make($request->password);
    }

    $admin->save();

    return redirect()->route('admins.index')->with('success', 'تم تحديث بيانات المسؤول');
}

// 📌 حذف مسؤول
public function adminsDelete($id)
{
    $admin = User::findOrFail($id);
    $admin->delete();
    return redirect()->back()->with('success', 'تم حذف المسؤول');
}
}
