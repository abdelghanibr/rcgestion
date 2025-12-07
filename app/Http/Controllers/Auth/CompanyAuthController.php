<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Club;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CompanyAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.company.login');
    }

    public function showRegister()
    {
        return view('auth.company.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'         => 'required',
            'email'        => 'required|email|unique:users',
            'password'     => 'required|min:6|confirmed',
            'attachments'  => 'required',
            'attachments.*'=> 'required|mimes:pdf,jpg,jpeg,png|max:4096'
        ]);

        DB::beginTransaction();
        $savedFiles = [];

        try {

            // إنشاء المستخدم
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'type'     => 'entreprise'
            ]);

            // مسارات التخزين
            if (app()->environment('local')) {
                $storagePath = storage_path('app/public/clubs');
                $storageUrl  = '/storage/clubs';
            } else {
                $storagePath = rtrim(env('PUBLIC_STORAGE_PATH'), '/') . '/clubs';
                $storageUrl  = rtrim(env('PUBLIC_STORAGE_URL'), '/') . '/clubs';
            }

            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0777, true);
            }

            // رفع الملفات
            foreach ($request->file('attachments') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move($storagePath, $fileName);
                $savedFiles[] = $storageUrl . '/' . $fileName;
            }

            if (count($savedFiles) === 0) {
                throw new \Exception("Upload failed");
            }

            // إنشاء السجل داخل جدول clubs
            Club::create([
                'user_id'         => $user->id,
                'nom'             => $request->name,
                'entity_type'     => 'company', // 👈 أهم تغيير
                'attachments'     => json_encode($savedFiles, JSON_UNESCAPED_UNICODE),
            ]);

            DB::commit();

            Auth::login($user);
            return redirect()->route('entreprise.dashboard')->with('success', 'تم تسجيل الشركة بنجاح 🎉');

        } catch (\Exception $e) {

            DB::rollBack();

            foreach ($savedFiles as $file) {
                $localFile = str_replace('/storage', 'storage/app/public', $file);
                if (file_exists($localFile)) {
                    @unlink($localFile);
                }
            }

            return back()
                ->withErrors(['error' => '⚠️ حدث خطأ أثناء التسجيل. يرجى المحاولة لاحقاً.'])
                ->withInput();
        }
    }

    public function login(Request $request)
    {
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'type' => 'entreprise'
        ])) {
            return redirect()->route('entreprise.dashboard');
        }

        return back()->withErrors(['email' => 'المعلومات غير صحيحة']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('entreprise.login');
    }
}
