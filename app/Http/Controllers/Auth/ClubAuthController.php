<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Club;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClubAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.club.login');
    }

    public function showRegister()
    {
        return view('auth.club.register');
    }

   public function register(Request $request)
{
    $request->validate([
        'name'             => 'required',
        'email'            => 'required|email|unique:users',
        'password'         => 'required|min:6|confirmed',
        'date_expiration'  => 'required|date',
        'numero_agrement'  => 'required',
        'attachments'      => 'required',
        'attachments.*'    => 'required|mimes:pdf,jpg,jpeg,png|max:2048'
    ]);

    DB::beginTransaction();
    $savedFiles = [];

    try {

        // 🔹 إنشاء المستخدم أولاً
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'type'     => 'club',
        ]);

        // 🔹 إعداد مسار المرفقات
        if (app()->environment('local')) {
            $storagePath = storage_path('app/public/clubs');
            $storageUrl = '/storage/clubs';
        } else {
            $storagePath = rtrim(env('PUBLIC_STORAGE_PATH'), '/') . '/clubs';
            $storageUrl = rtrim(env('PUBLIC_STORAGE_URL'), '/') . '/clubs';
        }

        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        // 🔹 معالجة الملفات
        foreach ($request->file('attachments') as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move($storagePath, $fileName);
            $savedFiles[] = $storageUrl . '/' . $fileName;
        }

        // 🔹 إنشاء النادي
        Club::create([
            'user_id'         => $user->id,
            'nom'             => $request->name,
            'numero_agrement' => $request->numero_agrement,
            'date_expiration' => $request->date_expiration,
            'attachments'     => json_encode($savedFiles, JSON_UNESCAPED_UNICODE),
            'entity_type' => 'club'
        ]);

        DB::commit();

        Auth::login($user);
        return redirect()->route('club.dashboard')->with('success', 'تم التسجيل بنجاح 🎉');

    } catch (\Exception $e) {

        // 🔥 حذف الملفات التي تم رفعها
        foreach ($savedFiles as $file) {
            $fullPath = str_replace('/storage', 'storage/app/public', $file);
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        // 🔥 التراجع عن كل العمليات
        DB::rollBack();

        return back()
            ->withErrors(['error' => 'حدث خطأ أثناء إنشاء الحساب. يرجى المحاولة لاحقًا.'])
            ->withInput();
    }
}

public function login(Request $request)
    {
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'type' => 'club'
        ])) {
            return redirect()->route('club.dashboard');
        }

        return back()->withErrors(['email' => 'المعلومات غير صحيحة']);
    }


    public function logout()
    {
        Auth::logout();
        return redirect()->route('club.login');
    }
}
