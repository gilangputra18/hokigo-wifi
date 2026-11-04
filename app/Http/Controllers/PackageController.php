<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::withCount('customers')->get();
        return view('packages.index', compact('packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'speed' => ['nullable', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        Package::create($validated);

        return back()->with('success', "Paket {$validated['name']} berhasil ditambahkan!");
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'speed' => ['nullable', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $package->update($validated);

        return back()->with('success', "Paket {$package->name} berhasil diperbarui.");
    }

    public function destroy(Package $package)
    {
        if ($package->customers()->count() > 0) {
            return back()->withErrors(['error' => "Paket {$package->name} tidak dapat dihapus karena masih digunakan oleh {$package->customers()->count()} pelanggan."]);
        }

        $name = $package->name;
        $package->delete();

        return back()->with('success', "Paket {$name} berhasil dihapus.");
    }
}
