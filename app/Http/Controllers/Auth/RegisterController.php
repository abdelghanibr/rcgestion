<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }
public function edit()
{
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login');
    }

    switch ($user->type) {

        case 'admin':
            return view('admin.profile.edit', compact('user'));

        case 'club':
            return view('club.profile.edit', compact('user'));

        case 'person':
            return view('person.profile.edit', compact('user'));

        
        case 'company':
            return view('entreprise.profile.edit', compact('user'));

        default:
            abort(403, 'Unauthorized access');
    }
}
public function update(Request $request)
{
    $user = Auth::user();

    // 🔹 التحقق العام لجميع المستخدمين
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'phone' => 'nullable|string|max:20',
        'photo' => 'nullable|image|max:2048',
    ]);

    // 🔹 تحديث الحقول العامة
    $user->name = $request->name;
    $user->email = $request->email;
    $user->phone = $request->phone;

    // 🔹 حفظ الصورة إن وُجدت
    if ($request->hasFile('photo')) {
        // حذف الصورة القديمة إن وجدت
        if($user->photo && \Storage::disk('public')->exists($user->photo)){
            \Storage::disk('public')->delete($user->photo);
        }

        $path = $request->photo->store('users', 'public');
        $user->photo = $path;
    }

    // ========================
    // 🔥 حسب نوع المستخدم
    // ========================

    switch ($user->type) {

        case 'person':
            // لا يوجد حقول إضافية حالياً
            $user->save();
            return redirect()->route('person.profile.edit')
                ->with('success', 'تم تحديث معلوماتك بنجاح 🎯');

        case 'club':
            // يمكن مستقبلاً إضافة حقول تخص النادي
            $user->save();
            return redirect()->route('club.profile.edit')
                ->with('success', 'تم تحديث بيانات النادي 👍');

        case 'entreprise':
        case 'company':
            // مثال: مستقبلًا يمكن إضافة (NRC, NIF, adresse…)
            $user->save();
            return redirect()->route('entreprise.profile.edit')
                ->with('success', '✔ تم تحديث حساب الشركة!');

        default:
            abort(403, 'غير مصرح لك بتنفيذ هذا الإجراء');
    }
}


    public function register(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname'  => ['required', 'string', 'max:255'],
            'username'  => ['required', 'string', 'max:255', 'unique:users'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],

            'password'  => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',      // حرف كبير
                'regex:/[a-z]/',      // حرف صغير
                'regex:/[0-9]/',      // رقم
                'regex:/[@$!%*#?&]/', // رمز خاص
            ],
        ],
        [
            'email.unique' => 'هذا البريد الإلكتروني مسجّل مسبقاً.',
            'username.unique' => 'اسم المستخدم مسجّل مسبقاً.',
            'password.confirmed' => 'كلمتا المرور غير متطابقتين.',
            'password.regex' => 'كلمة المرور يجب أن تحتوي على حرف كبير وصغير ورقم ورمز خاص.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user
        $user = User::create([
            'firstname' => $request->firstname,
            'lastname'  => $request->lastname,
            'username'  => $request->username,
            'email'     => $request->email,
            'name'      => $request->firstname . ' ' . $request->lastname,
            'password'  => Hash::make($request->password),
        ]);

        // Redirect to success page
        return redirect()->route('register.success')
                         ->with('name', $user->name);
    }
}
