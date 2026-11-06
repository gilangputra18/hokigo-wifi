<?php

namespace App\Http\Controllers;

use App\Models\Village;
use Illuminate\Http\Request;

class VillageController extends Controller
{
    public function index()
    {
        $villages = Village::withCount('customers')->orderBy('name')->get();
        return view('villages.index', compact('villages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:villages,name'],
            'code' => ['nullable', 'string', 'max:20'],
            'district' => ['nullable', 'string', 'max:255'],
            'system_type' => ['required', 'in:pppoe,voucher,hybrid'],
            'notes' => ['nullable', 'string'],
        ]);

        $village = Village::create($validated);

        try {
            (new PppoeController())->autoPopulateVillageAccounts($village->id);
        } catch (\Throwable $e) {}

        return back()->with('success', "Desa {$validated['name']} berhasil ditambahkan!");
    }

    public function update(Request $request, Village $village)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:villages,name,' . $village->id],
            'code' => ['nullable', 'string', 'max:20'],
            'district' => ['nullable', 'string', 'max:255'],
            'system_type' => ['required', 'in:pppoe,voucher,hybrid'],
            'notes' => ['nullable', 'string'],
        ]);

        $village->update($validated);

        return back()->with('success', "Data desa {$village->name} berhasil diperbarui.");
    }

    public function destroy(Village $village)
    {
        if ($village->customers()->count() > 0) {
            return back()->withErrors(['error' => "Desa {$village->name} tidak dapat dihapus karena masih memiliki {$village->customers()->count()} pelanggan terdaftar."]);
        }

        $name = $village->name;
        $village->delete();

        return back()->with('success', "Desa {$name} berhasil dihapus.");
    }
}
