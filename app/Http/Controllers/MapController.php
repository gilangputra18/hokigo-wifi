<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Village;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index(Request $request)
    {
        $villages = Village::orderBy('name')->get();

        $query = Customer::with(['village', 'package']);

        if ($request->filled('village_id')) {
            $query->where('village_id', $request->village_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $allCustomers = $query->orderBy('name')->get();

        // Pinned vs Unpinned customers
        $pinnedCustomers = $allCustomers->filter(function ($c) {
            return !is_null($c->latitude) && !is_null($c->longitude);
        })->values();

        $unpinnedCustomers = $allCustomers->filter(function ($c) {
            return is_null($c->latitude) || is_null($c->longitude);
        })->values();

        return view('maps.index', compact('villages', 'pinnedCustomers', 'unpinnedCustomers', 'allCustomers'));
    }

    public function updateLocation(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $customer->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Titik koordinat {$customer->name} berhasil disimpan!",
                'customer' => $customer,
            ]);
        }

        return back()->with('success', "Titik koordinat {$customer->name} berhasil disimpan!");
    }

    public function resetMapData(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya Owner atau Admin yang memiliki wewenang untuk mereset data peta.');
        }

        $request->validate([
            'confirmation' => ['required', 'string', 'in:RESET,reset,RESET PETA'],
            'action_type' => ['required', 'in:coordinates_only,delete_all_customers'],
        ], [
            'confirmation.in' => 'Ketik kata "RESET" untuk mengonfirmasi tindakan reset peta.',
        ]);

        if ($request->action_type === 'delete_all_customers') {
            Customer::query()->delete();
            return redirect()->route('maps.index')->with('success', 'Seluruh data pelanggan berhasil dihapus (Total: 0 Pelanggan). Anda kini dapat mendaftarkan pelanggan baru dari awal.');
        } else {
            Customer::query()->update([
                'latitude' => null,
                'longitude' => null,
            ]);
            return redirect()->route('maps.index')->with('success', 'Seluruh titik koordinat rumah di peta berhasil di-0 kan! Semua pelanggan kini berstatus "Belum Dititik" dan siap dititik ulang oleh teknisi/admin.');
        }
    }
}
