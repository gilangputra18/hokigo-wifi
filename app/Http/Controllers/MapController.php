<?php

namespace App\Http\Controllers;

use App\Models\CableRoute;
use App\Models\Customer;
use App\Models\NetworkNode;
use App\Models\Village;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index(Request $request)
    {
        $villages = Village::orderBy('name')->get();

        $query = Customer::with([
            'village:id,name,system_type',
            'package:id,name,speed,price'
        ])->select([
            'id',
            'customer_code',
            'name',
            'phone',
            'village_id',
            'package_id',
            'monthly_fee',
            'router_model',
            'pppoe_user',
            'pppoe_password',
            'status',
            'notes',
            'latitude',
            'longitude',
            'house_number',
            'redaman',
        ])->selectRaw("CASE WHEN house_photo IS NOT NULL AND house_photo != '' THEN 1 ELSE 0 END AS has_photo");

        if ($request->filled('village_id')) {
            $query->where('village_id', $request->village_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $allCustomers = $query->orderBy('name')->get();

        // Attach lightweight photo URL without loading massive base64 string into map payload
        $allCustomers->each(function ($c) {
            $c->photo_url = $c->has_photo ? route('customers.photo', $c->id) : null;
        });

        // Compute village coordinates if not manually set (use centroid of its pinned customers)
        $villages->each(function ($v) use ($allCustomers) {
            if (is_null($v->latitude) || is_null($v->longitude)) {
                $villageCustomers = $allCustomers->where('village_id', $v->id)
                    ->filter(fn($c) => !is_null($c->latitude) && !is_null($c->longitude));
                if ($villageCustomers->isNotEmpty()) {
                    $v->latitude = (float) $villageCustomers->avg('latitude');
                    $v->longitude = (float) $villageCustomers->avg('longitude');
                    $v->zoom_level = 16;
                }
            }
        });

        // Pinned vs Unpinned customers
        $pinnedCustomers = $allCustomers->filter(function ($c) {
            return !is_null($c->latitude) && !is_null($c->longitude);
        })->values();

        $unpinnedCustomers = $allCustomers->filter(function ($c) {
            return is_null($c->latitude) || is_null($c->longitude);
        })->values();

        // Network Infrastructure Nodes (Server, Starlink Dish, ODP, Tiang)
        $networkNodes = NetworkNode::with('village:id,name')->get();

        // Cable Routes (Dropcore, Distribution, Backbone)
        $cableRoutes = CableRoute::with('village:id,name')->get();

        return view('maps.index', compact(
            'villages',
            'pinnedCustomers',
            'unpinnedCustomers',
            'allCustomers',
            'networkNodes',
            'cableRoutes'
        ));
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
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'customer_code' => $customer->customer_code,
                    'latitude' => $customer->latitude,
                    'longitude' => $customer->longitude,
                    'house_number' => $customer->house_number,
                    'redaman' => $customer->redaman,
                    'notes' => $customer->notes,
                    'status' => $customer->status,
                    'clean_phone' => $customer->clean_phone,
                    'has_photo' => !empty($customer->house_photo),
                    'photo_url' => !empty($customer->house_photo) ? route('customers.photo', $customer->id) : null,
                ],
            ]);
        }

        return back()->with('success', "Titik koordinat {$customer->name} berhasil disimpan!");
    }

    public function storeNode(Request $request)
    {
        $validated = $request->validate([
            'village_id' => ['nullable', 'exists:villages,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:server,starlink,odp,switch,pole'],
            'capacity' => ['nullable', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'string', 'in:active,maintenance'],
        ]);

        $node = NetworkNode::create($validated);
        $node->load('village:id,name');

        return response()->json([
            'success' => true,
            'message' => "Titik {$node->name} ({$node->type_label}) berhasil disimpan!",
            'node' => $node,
        ]);
    }

    public function destroyNode(NetworkNode $node)
    {
        $name = $node->name;
        $node->delete();

        return response()->json([
            'success' => true,
            'message' => "Titik {$name} berhasil dihapus dari peta.",
        ]);
    }

    public function storeCable(Request $request)
    {
        $validated = $request->validate([
            'village_id' => ['nullable', 'exists:villages,id'],
            'name' => ['required', 'string', 'max:255'],
            'cable_type' => ['required', 'string', 'in:dropcore,distribution,backbone'],
            'color' => ['required', 'string', 'max:20'],
            'core_count' => ['nullable', 'string', 'max:50'],
            'coordinates' => ['required', 'array', 'min:2'],
            'coordinates.*.0' => ['required', 'numeric', 'between:-90,90'],
            'coordinates.*.1' => ['required', 'numeric', 'between:-180,180'],
            'length_meters' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cable = CableRoute::create($validated);
        $cable->load('village:id,name');

        return response()->json([
            'success' => true,
            'message' => "Jalur kabel {$cable->name} berhasil disimpan!",
            'cable' => $cable,
        ]);
    }

    public function destroyCable(CableRoute $cable)
    {
        $name = $cable->name;
        $cable->delete();

        return response()->json([
            'success' => true,
            'message' => "Jalur kabel {$name} berhasil dihapus dari peta.",
        ]);
    }

    public function updateVillageLocation(Request $request, Village $village)
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'zoom_level' => ['nullable', 'integer', 'between:10,20'],
        ]);

        $village->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Titik pusat wilayah {$village->name} berhasil diperbarui!",
            'village' => $village,
        ]);
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
