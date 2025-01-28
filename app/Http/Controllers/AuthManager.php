<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthManager extends Controller
{
    function login(){
        return view('auth.login');
    }

    function loginPost(Request $request)
    {
        $request->validate([
            'email'=>'required',
            'password'=>'required'
        ]);

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->intended(route('home'));
        }
        return redirect('login')
            ->with('error', 'Invalid Credentials');
    }
    function Logout()
    {
        Auth::logout();
        return redirect('login');
    }

    function register(){
        return view('auth.register');
    }

    function registerPost(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'email'=>'required',
            'password'=>'required'
        ]);
        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->password);
        if ($user->save()) {
            return redirect()->intended(route('login'))
                ->with('success', 'Registration Successful');
        }
        return redirect(route("register"))->with('error', 'Registration Failed');
    }
}
