<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
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
