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
}
