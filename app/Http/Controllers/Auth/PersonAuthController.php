<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Person;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PersonAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.person.login');
    }

    public function showRegister()
    {
        return view('auth.person.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'type'     => 'person',
        ]);

        Person::create(['user_id' => $user->id]);

        Auth::login($user);
        return redirect()->route('person.dashboard');
    }

    public function login(Request $request)
    {
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'type' => 'person'
        ])) {
            return redirect()->route('person.dashboard');
        }

        return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('person.login');
    }
}
