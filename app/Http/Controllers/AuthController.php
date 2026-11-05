<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isTeknisi() 
                ? redirect()->route('maps.index') 
                : redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda dinonaktifkan. Hubungi Owner Hokigo.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            if ($user->isTeknisi()) {
                $request->session()->forget('url.intended');
                return redirect()->route('maps.index')
                    ->with('success', 'Selamat datang Teknisi, ' . $user->name . '! Silakan akses Peta & Data Pelanggan.');
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:25'],
            'password' => ['nullable', 'string', 'min:6'],
            'avatar' => ['nullable', 'string'],
            'avatar_file' => ['nullable', 'file', 'image', 'max:5120'],
            'remove_avatar' => ['nullable'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;

        // Handle Avatar Upload / Removal
        if ($request->input('remove_avatar') == '1') {
            $user->avatar = null;
        } elseif ($request->hasFile('avatar_file')) {
            $file = $request->file('avatar_file');
            if ($file->isValid()) {
                $mime = $file->getMimeType();
                $base64 = base64_encode(file_get_contents($file->getRealPath()));
                $user->avatar = "data:{$mime};base64,{$base64}";
            }
        } elseif (!empty($request->avatar)) {
            $user->avatar = $request->avatar;
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        Auth::login($user);

        return back()->with('success', 'Foto profil dan data akun Anda (' . $user->name . ') berhasil diperbarui.' . (!empty($validated['password']) ? ' Kata sandi baru Anda berhasil disimpan.' : ''));
    }
}
