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
            'wa_gateway_provider' => Setting::get('wa_gateway_provider', 'fonnte'),
            'wa_gateway_token' => Setting::get('wa_gateway_token', ''),
            'wa_auto_reminder' => Setting::get('wa_auto_reminder', '1'),
            'wa_reminder_time' => Setting::get('wa_reminder_time', '08:00'),
            'wa_cron_secret' => Setting::get('wa_cron_secret', 'hokigo_cron_secret'),
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

    public function updateWhatsApp(Request $request)
    {
        $validated = $request->validate([
            'wa_gateway_provider' => ['required', 'in:fonnte,wablas'],
            'wa_gateway_token' => ['nullable', 'string', 'max:255'],
            'wa_auto_reminder' => ['nullable'],
            'wa_reminder_time' => ['required', 'string', 'max:10'],
        ]);

        Setting::set('wa_gateway_provider', $validated['wa_gateway_provider']);
        Setting::set('wa_gateway_token', $validated['wa_gateway_token'] ?? '');
        Setting::set('wa_auto_reminder', $request->has('wa_auto_reminder') ? '1' : '0');
        Setting::set('wa_reminder_time', $validated['wa_reminder_time']);

        return back()->with('success', 'Pengaturan WhatsApp Gateway berhasil diperbarui!');
    }

    public function testWhatsApp(Request $request)
    {
        $request->validate([
            'test_phone' => ['required', 'string', 'max:25'],
        ]);

        $companyName = Setting::get('company_name', 'Hokigo Starlink');
        $msg = "Halo! Ini adalah pesan uji coba dari sistem WiFi {$companyName}.\nKoneksi WhatsApp Gateway Anda berhasil aktif dan siap digunakan untuk penagihan otomatis! ✅";

        $res = \App\Services\WhatsAppGatewayService::sendMessage($request->test_phone, $msg);

        if ($res['success']) {
            return back()->with('success', "Uji coba berhasil! Pesan WhatsApp terkirim ke {$request->test_phone}.");
        } else {
            return back()->withErrors(['test_phone' => "Gagal mengirim: {$res['message']}"]);
        }
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:owner,admin,teknisi'],
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
            'role' => ['required', 'in:owner,admin,teknisi'],
            'phone' => ['nullable', 'string', 'max:25'],
            'is_active' => ['nullable'],
            'avatar' => ['nullable', 'string'],
            'avatar_file' => ['nullable', 'file', 'image', 'max:5120'],
            'remove_avatar' => ['nullable'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->phone = $validated['phone'] ?? null;
        $user->is_active = $request->boolean('is_active');

        // Handle Avatar
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

        $passwordChanged = false;
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $passwordChanged = true;
        }

        $user->save();

        if (\Illuminate\Support\Facades\Auth::id() === $user->id) {
            \Illuminate\Support\Facades\Auth::login($user);
        }

        $msg = "Akun {$user->name} berhasil diperbarui. Email login: {$user->email}";
        if ($passwordChanged) {
            $msg .= " & kata sandi baru berhasil disimpan!";
        }

        return back()->with('success', $msg);
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
