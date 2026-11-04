<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'company_name' => Setting::get('company_name', 'Hokigo Starlink'),
            'company_tagline' => Setting::get('company_tagline', 'Provider Internet Cepat & Hemat'),
            'company_phone' => Setting::get('company_phone', '081234567890'),
            'company_address' => Setting::get('company_address', 'Jl. Poros Utama No. 88'),
            'bank_accounts' => Setting::get('bank_accounts', "MUHAMMAD IRZAQ IRAWAN\n7233297792 BSI"),
        ];

        $users = User::orderBy('name')->get();

        return view('settings.index', compact('settings', 'users'));
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:100'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['required', 'string', 'max:30'],
            'company_address' => ['nullable', 'string'],
            'bank_accounts' => ['required', 'string'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Pengaturan usaha Hokigo berhasil disimpan!');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:owner,admin'],
            'phone' => ['nullable', 'string', 'max:25'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        User::create($validated);

        return back()->with('success', "Pengguna {$validated['name']} ({$validated['role']}) berhasil ditambahkan!");
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:owner,admin'],
            'phone' => ['nullable', 'string', 'max:25'],
            'is_active' => ['boolean'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $user->update($validated);

        return back()->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroyUser(User $user)
    {
        if (User::count() <= 1) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus satu-satunya akun pengguna yang tersisa.']);
        }

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.']);
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Akun {$name} berhasil dihapus.");
    }
}
