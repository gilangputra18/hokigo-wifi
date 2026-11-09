<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    /**
     * Display the Backup & Restore management page.
     */
    public function index()
    {
        $stats = [
            'customers' => DB::table('customers')->count(),
            'villages' => DB::table('villages')->count(),
            'packages' => DB::table('packages')->count(),
            'pppoe_accounts' => DB::table('pppoe_accounts')->count(),
            'invoices' => DB::table('invoices')->count(),
            'financial_transactions' => DB::table('financial_transactions')->count(),
            'voucher_sales' => DB::table('voucher_sales')->count(),
            'network_nodes' => DB::table('network_nodes')->count(),
            'cable_routes' => DB::table('cable_routes')->count(),
            'settings' => DB::table('settings')->count(),
        ];

        $totalRecords = array_sum($stats);

        return view('backup.index', compact('stats', 'totalRecords'));
    }

    /**
     * Download the entire database snapshot as a structured JSON file.
     */
    public function download()
    {
        $backupData = [
            'app' => 'Hokigo WiFi Management',
            'version' => '2.0',
            'exported_at' => now()->toIso8601String(),
            'exported_by' => auth()->user()?->name ?? 'Admin',
            'exported_by_email' => auth()->user()?->email,
            'summary' => [
                'villages' => DB::table('villages')->count(),
                'packages' => DB::table('packages')->count(),
                'customers' => DB::table('customers')->count(),
                'pppoe_accounts' => DB::table('pppoe_accounts')->count(),
                'invoices' => DB::table('invoices')->count(),
                'financial_transactions' => DB::table('financial_transactions')->count(),
                'voucher_sales' => DB::table('voucher_sales')->count(),
                'network_nodes' => DB::table('network_nodes')->count(),
                'cable_routes' => DB::table('cable_routes')->count(),
                'settings' => DB::table('settings')->count(),
            ],
            'data' => [
                'villages' => DB::table('villages')->get()->map(fn($r) => (array) $r)->toArray(),
                'packages' => DB::table('packages')->get()->map(fn($r) => (array) $r)->toArray(),
                'settings' => DB::table('settings')->get()->map(fn($r) => (array) $r)->toArray(),
                'network_nodes' => DB::table('network_nodes')->get()->map(fn($r) => (array) $r)->toArray(),
                'cable_routes' => DB::table('cable_routes')->get()->map(fn($r) => (array) $r)->toArray(),
                'customers' => DB::table('customers')->get()->map(fn($r) => (array) $r)->toArray(),
                'pppoe_accounts' => DB::table('pppoe_accounts')->get()->map(fn($r) => (array) $r)->toArray(),
                'invoices' => DB::table('invoices')->get()->map(fn($r) => (array) $r)->toArray(),
                'financial_transactions' => DB::table('financial_transactions')->get()->map(fn($r) => (array) $r)->toArray(),
                'voucher_sales' => DB::table('voucher_sales')->get()->map(fn($r) => (array) $r)->toArray(),
            ],
        ];

        $jsonString = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fileName = 'hokigo_backup_' . date('Y-m-d_His') . '.json';

        return response($jsonString, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Export customer list as CSV for Excel / Google Sheets viewing.
     */
    public function exportCustomersCsv()
    {
        $customers = DB::table('customers')
            ->leftJoin('villages', 'customers.village_id', '=', 'villages.id')
            ->leftJoin('packages', 'customers.package_id', '=', 'packages.id')
            ->select([
                'customers.id',
                'customers.customer_code',
                'customers.name',
                'customers.phone',
                'villages.name as village_name',
                'packages.name as package_name',
                'customers.monthly_fee',
                'customers.due_date_day',
                'customers.pppoe_user',
                'customers.pppoe_password',
                'customers.status',
                'customers.address',
                'customers.house_number',
                'customers.redaman',
                'customers.installation_date',
                'customers.notes',
            ])
            ->orderBy('customers.name')
            ->get();

        $csvFileName = 'hokigo_pelanggan_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$csvFileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($customers) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'No',
                'Kode Pelanggan',
                'Nama Pelanggan',
                'No WhatsApp',
                'Kelompok Desa',
                'Paket Internet',
                'Biaya Bulanan (Rp)',
                'Tanggal Jatuh Tempo',
                'PPPoE User',
                'PPPoE Password',
                'Status',
                'Nomor Rumah',
                'Redaman (dBm)',
                'Tgl Pasang',
                'Alamat Lengkap',
                'Catatan',
            ]);

            $no = 1;
            foreach ($customers as $c) {
                fputcsv($handle, [
                    $no++,
                    $c->customer_code ?? '-',
                    $c->name,
                    $c->phone ? "'" . $c->phone : '-',
                    $c->village_name ?? '-',
                    $c->package_name ?? '-',
                    number_format((float) ($c->monthly_fee ?? 0), 0, ',', '.'),
                    $c->due_date_day ? 'Tgl ' . $c->due_date_day : '-',
                    $c->pppoe_user ?? '-',
                    $c->pppoe_password ?? '-',
                    $c->status === 'active' ? 'Aktif' : 'Nonaktif',
                    $c->house_number ?? '-',
                    $c->redaman ?? '-',
                    $c->installation_date ?? '-',
                    $c->address ?? '-',
                    $c->notes ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Restore database from an uploaded JSON backup file.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'max:51200'], // max 50MB
            'restore_mode' => ['required', 'string', 'in:merge,fresh'],
        ], [
            'backup_file.required' => 'Silakan pilih file cadangan (.json) yang ingin dipulihkan.',
            'backup_file.file' => 'File yang diunggah tidak valid.',
            'backup_file.max' => 'Ukuran file cadangan maksimal 50MB.',
            'restore_mode.required' => 'Pilih mode pemulihan (Gabungkan atau Timpa Bersih).',
        ]);

        $uploadedFile = $request->file('backup_file');
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        if ($extension !== 'json') {
            return back()->with('error', 'Format file harus berupa file cadangan JSON (.json).');
        }

        $rawContent = file_get_contents($uploadedFile->getRealPath());
        $payload = json_decode($rawContent, true);

        if (!$payload || !isset($payload['data']) || !is_array($payload['data'])) {
            return back()->with('error', 'Struktur file cadangan tidak valid atau file rusak.');
        }

        $tablesInOrder = [
            'villages',
            'packages',
            'settings',
            'network_nodes',
            'cable_routes',
            'customers',
            'pppoe_accounts',
            'invoices',
            'financial_transactions',
            'voucher_sales',
        ];

        $reverseTables = array_reverse($tablesInOrder);

        $counts = [];

        DB::beginTransaction();
        try {
            // If Fresh Restore mode, clear existing records in reverse dependency order
            if ($request->input('restore_mode') === 'fresh') {
                foreach ($reverseTables as $tbl) {
                    DB::table($tbl)->delete();
                }
            }

            // Restore in foreign key dependency order
            foreach ($tablesInOrder as $tbl) {
                $rows = $payload['data'][$tbl] ?? [];
                $restoredCount = 0;

                foreach ($rows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }

                    if ($tbl === 'settings') {
                        $key = $row['key'] ?? null;
                        if ($key) {
                            DB::table($tbl)->updateOrInsert(['key' => $key], $row);
                            $restoredCount++;
                        }
                    } else {
                        $id = $row['id'] ?? null;
                        if ($id) {
                            DB::table($tbl)->updateOrInsert(['id' => $id], $row);
                            $restoredCount++;
                        }
                    }
                }

                $counts[$tbl] = $restoredCount;
            }

            // In PostgreSQL Supabase, reset sequence counters after inserting explicit IDs
            if (DB::getDriverName() === 'pgsql') {
                foreach ($tablesInOrder as $tbl) {
                    try {
                        DB::statement("SELECT setval(pg_get_serial_sequence('{$tbl}', 'id'), coalesce(max(id), 1)) FROM {$tbl}");
                    } catch (\Throwable $seqEx) {
                        Log::warning("Could not reset sequence for {$tbl}: " . $seqEx->getMessage());
                    }
                }
            }

            DB::commit();

            $totalRestored = array_sum($counts);
            $cCount = $counts['customers'] ?? 0;
            $vCount = $counts['villages'] ?? 0;
            $iCount = $counts['invoices'] ?? 0;

            return back()->with('success', "Alhamdulillah, pemulihan data berhasil! Total {$totalRestored} data dipulihkan ({$cCount} Pelanggan, {$vCount} Desa, {$iCount} Tagihan). Seluruh data kini telah kembali sinkron.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Restore database failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Gagal memulihkan data: ' . $e->getMessage() . '. Database telah dikembalikan ke kondisi sebelumnya (Rollback).');
        }
    }
}
