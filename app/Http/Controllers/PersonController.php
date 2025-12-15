<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Person;
use Carbon\Carbon;

class PersonController extends Controller
{


public function index()
{
    $user = Auth::user();

    // 👇 اختيار مصدر البيانات حسب نوع المستخدم
    if ($user->type === 'club') {

        $persons = Person::where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        $view = 'club.persons.index';
    }
    elseif ($user->type === 'company') {

        $persons = Person::where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        $view = 'entreprise.persons.index';
    }
    elseif ($user->type === 'person') {

        $persons = Person::where('user_id', $user->id)->get();

        $view = 'person.profile';
    }
    else {
        abort(403, 'Unauthorized');
    }

    return view($view, compact('persons'));
}


    public function edit($id)
    {
        $user = Auth::user();
        $person = Person::findOrFail($id);

        // حماية المستخدم للوصول لغير بياناته
        if ($person->user_id != $user->id) {
            abort(403);
        }

        if ($user->type === 'club') {
            return view('club.persons.edit', compact('person'));
        } elseif ($user->type === 'company') {
            return view('entreprise.persons.edit', compact('person'));
        }

        abort(403);
    }


    /* ------------------------------------------------
    | 3️⃣ تحديث البيانات
    --------------------------------------------------*/
   public function update(Request $request, $id)
{
    $user = Auth::user();
    $person = Person::findOrFail($id);

    // 🔐 منع وصول مستخدم آخر لبياناته
    if ($person->user_id != $user->id) {
        abort(403);
    }

    // 🔍 التحقق من صحة الإدخال
    $request->validate([
        'firstname' => 'required|string|max:50',
        'lastname' => 'required|string|max:50',
        'birth_date' => 'required|date',
        'gender' => 'required',
        'education' => 'required'
    ]);

    // 💾 تحديث الحقول المطلوبة فقط
    $person->update([
        'firstname' => $request->firstname,
        'lastname' => $request->lastname,
        'birth_date' => $request->birth_date,
        'gender' => $request->gender,
        'education' => $request->education
    ]);

    // 👈 تحديد مسار العودة حسب نوع المستخدم
    if ($user->type === 'club') {
        $route = 'club.persons.index';
    } elseif ($user->type === 'company') {
        $route = 'entreprise.persons.index';
    } else {
        $route = 'dashboard';
    }

    // 🔁 الرجوع إلى صفحة القائمة مع رسالة نجاح
    return redirect()->route($route, $person->education)
                     ->with('success', '✔ تم تحديث البيانات بنجاح');
}

public function create()
{
    return view('club.persons.create');
}
    /* ------------------------------------------------
    | 4️⃣ حذف شخص
    --------------------------------------------------*/
    public function destroy($id)
    {
        $user = Auth::user();
        $person = Person::findOrFail($id);

        if ($person->user_id != $user->id) {
            abort(403);
        }

        $person->delete();

        return redirect()->back()->with('success', '❌ تم حذف المستخدم بنجاح');
    }


    public function store(Request $request)
{
    $validated = $request->validate([
        'firstname'  => 'required|string|max:100',
        'lastname'   => 'required|string|max:100',
        'birth_date' => 'required|date',
        'gender'     => 'required|in:ذكر,أنثى',
        'education'  => 'required|string|max:50',
        'photo'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ], [
        'photo.image' => 'يجب أن يكون الملف صورة',
        'photo.mimes' => 'الصيغ المسموحة: JPG, PNG',
        'photo.max'   => 'حجم الصورة لا يتجاوز 2MB',
    ]);

    // حساب العمر
    $age = Carbon::parse($validated['birth_date'])->age;

    // رفع الصورة
    $photoPath = null;
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('photos/persons', 'public');
    }



    $person = new Person();
$person->user_id = auth()->id();
$person->firstname = $request->firstname;
$person->lastname = $request->lastname;
$person->birth_date = $request->birth_date;
$person->gender = $request->gender;
$person->education = $request->education;
$person->photo = $photoPath;
$person->save();

//dd('SAVED', $person->id);
    // إنشاء الشخص
 /*   Person::create([
        'firstname'  => $validated['firstname'],
        'lastname'   => $validated['lastname'],
        'birth_date' => $validated['birth_date'],
        'gender'     => $validated['gender'],
        'education'  => $validated['education'],
        //'photo'      => $photoPath,
        'club_id'    => Auth::user()->club->id, // ربطه بالنادي
    ]);*/

    return redirect()
        ->route('club.persons.index')
        ->with('success', '✅ تمت إضافة العضو بنجاح');
}
}
