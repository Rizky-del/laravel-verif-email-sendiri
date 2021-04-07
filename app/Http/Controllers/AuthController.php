<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('login');
    }

    public function Login(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'email'     => 'required|email',
                'password'  => 'required|string'
            ],
            [
                'email.required'        => 'Email harus di isi',
                'email.email'           => 'Email anda tidak valid',
                'password.required'     => 'Password harus di isi',
                'password.string'       => 'Password harus berupa string'
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            return redirect()->route('home');
        }
        $request->session()->flash('error', 'Email atau password anda salah atau data anda belum ada');
        return redirect('login');
    }

    public function showRegister()
    {
        return view('register');
    }

    public function register(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'name'      => 'required',
                'email'     => 'required|email|unique:users,email',
                'password'  => 'required|confirmed'
            ],
            [
                'name.required'         => 'Nama harus di isi',
                'email.required'        => 'Email harus di isi',
                'email.email'           => 'Email anda tidak valid',
                'email.unique'          => 'Email tidak boleh sama',
                'password.required'     => 'Password harus di isi',
                'password.confirmed'    => 'Password tidak sama dengan konfirmasi password'
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput($request->all);
        }

        $user = new User;
        $user->name                 =   ucwords(strtolower($request->name));
        $user->email                =   strtolower($request->email);
        $user->password             =   bcrypt($request->password);
        // $user->email_verified_at    =   \Carbon\Carbon::now();
        $simpan = $user->save();

        // mengirimkan email 
        event(new Registered($user));
        if ($simpan) {
            $request->session()->flash('success', 'Register berhasil ditambahkan silahkan login untuk mengetes data');
            return redirect()->route('login');
        } else {
            $request->session()->flash('errors', ['' => 'Register gagal silahkan dicoba ulang']);
            return redirect()->route('register');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('login');
    }
}
