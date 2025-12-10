<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Person;
use App\Models\Dossier;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function index()
    {
        return redirect()->route('profile.step', 1);
    }

public function showStep($step)
{
    $user = Auth::user();
    
    // 🔹 إذا كا مستخدم club أو entreprise
    if ($user->type === 'club' || $user->type === 'company') {

        if(session()->has('edit_person_id')) {
            $person = Person::find(session('edit_person_id'));
        } else {
            // 👈 إنشاء شخص جديد (حقول فارغة)
            $person = new Person();
        }

    } 
    // 🔹 إذا كان المستخدم فردي person
    else {
        $person = Person::where('user_id', $user->id)
                        ->orderByDesc('id')
                        ->first();
    }

    $wilayas = [
        "أدرار","الشلف","الأغواط","أم البواقي","باتنة","بجاية","بسكرة","بشار",
        "البليدة","البويرة","تمنراست","تبسة","تلمسان","تيارت","تيزي وزو","الجزائر",
        "الجلفة","جيجل","سطيف","سعيدة","سكيكدة","سيدي بلعباس","عنابة","قالمة",
        "قسنطينة","المدية","مستغانم","المسيلة","معسكر","ورقلة","وهران","البيض",
        "إليزي","برج بوعريريج","بومرداس","الطارف","تندوف","تيسمسيلت","الوادي",
        "خنشلة","سوق أهراس","تيبازة","ميلة","عين الدفلى","النعامة","عين تموشنت",
        "غرداية","غليزان"
    ];

    return view('profile.steps', compact('step','user','person','wilayas'));
}



    public function saveStep(Request $request, $step)
    {
        $user = Auth::user();
        $type = $user->type;

        switch ($step) {

            case 1:
                $validated = $request->validate([
                    'firstname' => 'required|string|max:50',
                    'lastname' => 'required|string|max:50',
                    'birth_date' => 'required|date',
                    'gender' => 'required',
                    'handicap' => 'required'
                ]);

                $age = Carbon::parse($request->birth_date)->age;
                $ageCat = $age <= 12 ? 1 : ($age <= 17 ? 2 : ($age <= 59 ? 3 : 4));

                if ($type === 'club' || $type === 'company') {
                    Person::create(array_merge($validated, [
                        'user_id' => $user->id,
                        'age_category_id' => $ageCat
                    ]));
                } else {
                    Person::updateOrCreate(
                        ['user_id' => $user->id],
                        array_merge($validated, ['age_category_id' => $ageCat])
                    );
                }

                return redirect()->route('profile.step', ($age < 18 ? 2 : 3));



            case 2:
                if ($type !== 'person') return redirect()->route('profile.step', 3);

                $validated = $request->validate([
                    'parent_firstname' => 'required|string|max:50',
                    'parent_lastname' => 'required|string|max:50',
                    'parent_phone' => 'required|string|max:20',
                ]);

                $person = Person::where('user_id', $user->id)->orderByDesc('id')->first();
                $person->update($validated);

                return redirect()->route('profile.step', 3);



            case 3:
                $rules = [
                    'phone' => 'required|string|max:20',
                    'address' => 'required|string|max:255',
                ];

                if ($type !== 'person') {
                    $rules['education'] = 'required|in:مسير,مدرب,لاعب,آخر';
                }

                $validated = $request->validate($rules);

                $person = Person::where('user_id', $user->id)->orderByDesc('id')->first();
                $person->update($validated);

                return redirect()->route('profile.step', 4);



            case 4:

                $request->validate([
                    'photo' => 'required|image|max:2048',
                    'birth_certificate' => 'required|mimes:pdf,jpg,png|max:4096'
                ]);

                if (app()->environment('local')) {
                    $storagePath = storage_path('app/public');
                    $storageUrl = '/storage';
                } else {
                    $storagePath = rtrim(env('PUBLIC_STORAGE_PATH'), '/');
                    $storageUrl = rtrim(env('PUBLIC_STORAGE_URL'), '/');
                }

                $person = Person::where('user_id', $user->id)->orderByDesc('id')->first();
                $attachments = [];

                if ($request->hasFile('photo')) {
                    $photoName = time().'_'.$request->file('photo')->getClientOriginalName();
                    $request->file('photo')->move($storagePath.'/photos', $photoName);
                    $attachments['photo'] = $storageUrl.'/photos/'.$photoName;
                    $person->photo = $attachments['photo'];
                }

                if ($request->hasFile('birth_certificate')) {
                    $fileName = time().'_'.$request->file('birth_certificate')->getClientOriginalName();
                    $request->file('birth_certificate')->move($storagePath.'/documents', $fileName);
                    $attachments['birth_certificate'] = $storageUrl.'/documents/'.$fileName;
                    $person->birth_certificate = $attachments['birth_certificate'];
                }

                $person->save();

                if (!empty($attachments)) {
                    Dossier::updateOrCreate(
                        ['person_id' => $person->id],
                        [
                            'etat' =>'pending',
                            'attachments' => json_encode($attachments),
                            'owner_type' => $type,
                            'note_admin' => '📌 تم رفع الوثائق وجاري التحقق منها'
                        ]
                    );
                }

                $route = match ($user->type) {
                    'admin' => 'admin.dashboard',
                    'club' => 'club.dashboard',
                    'company' => 'entreprise.dashboard',
                    default => 'person.dashboard'
                };

                return redirect()->route($route)->with('success','✔ تم استكمال البيانات بنجاح 🎉');
        }
    }
public function newPerson()
{
    // حذف وضع التعديل
    session()->forget('edit_person_id');

    return redirect()->route('profile.step', 1);
}


    /* =====================================================
       🛠️ دوال التعديل الجديدة (Club & Entreprise only)
    ===================================================== */

   public function editStep($personId, $step)
{
    $user = Auth::user();

    // التحقق من أن الشخص تابع فعلاً للمستخدم (نادي/مؤسسة)
    $person = Person::where('id', $personId)
                    ->where('user_id', $user->id)
                    ->firstOrFail();

    // 🧠 تخزين الـ ID في Session للعمل في وضع تعديل
    session(['edit_person_id' => $person->id]);

    // جلب بيانات الخطوة
    return redirect()->route('profile.step', $step)
                     ->with('info', '📝 وضع التعديل مفعل!');
}



    public function saveEditStep(Request $request, $personId, $step)
    {
        $person = Person::findOrFail($personId);

        switch ($step) {
            case 1:
                $person->update($request->validate([
                    'firstname' => 'required',
                    'lastname' => 'required',
                    'birth_date' => 'required|date',
                    'gender' => 'required'
                ]));
                break;

            case 2:
                $person->update($request->validate([
                    'parent_firstname' => 'nullable|string',
                    'parent_lastname' => 'nullable|string',
                    'parent_phone' => 'nullable'
                ]));
                break;

            case 3:
                $person->update($request->validate([
                    'phone' => 'required|string',
                    'address' => 'required|string',
                    'education' => 'nullable'
                ]));
                break;
        }

        return redirect()
            ->route('profile.editStep', ['person' => $personId, 'step' => $step + 1])
            ->with('success','✔ تم تحديث البيانات بنجاح');
    }
}
