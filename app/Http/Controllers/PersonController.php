<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Person;

class PersonController extends Controller
{
    public function index($type)
    {
        $user = Auth::user();

        if (!in_array($type, ['لاعب','مدرب','مسير','آخر'])) {
            abort(404);
        }

        // 👇 اختيار مصدر البيانات حسب نوع المستخدم
        if ($user->type === 'club') {
            $persons = Person::where('user_id', $user->id)
                                ->where('education', $type)
                                ->orderByDesc('id')
                                ->get();

            $view = 'club.persons.index';
        }
        elseif ($user->type === 'company') {
            $persons = Person::where('user_id', $user->id)
                                ->where('education', $type)
                                ->orderByDesc('id')
                                ->get();

            $view = 'entreprise.persons.index';
        }
        elseif ($user->type === 'person') {
            $persons = Person::where('user_id', $user->id)->get();

            $view = 'person.profile'; // 👈 يمكنك تغيير هذا لاحقاً
        }
        else {
            abort(403, "Unauthorized");
        }

        return view($view, compact('persons', 'type'));
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
}
