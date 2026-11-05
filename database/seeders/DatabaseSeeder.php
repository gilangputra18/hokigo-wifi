<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Setting;
use App\Models\User;
use App\Models\Village;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users (Owner & Admin)
        $owner = User::firstOrCreate(
            ['email' => 'owner@hokigo.net'],
            [
                'name' => 'Owner Hokigo',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@hokigo.net'],
            [
                'name' => 'Admin Hokigo',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081298765432',
                'is_active' => true,
            ]
        );

        $teknisi = User::firstOrCreate(
            ['email' => 'teknisi@hokigo.net'],
            [
                'name' => 'Teknisi Lapangan',
                'password' => Hash::make('password'),
                'role' => 'teknisi',
                'phone' => '081227391262',
                'is_active' => true,
            ]
        );

        // 2. Settings
        $settings = [
            'company_name' => 'Hokigo Starlink',
            'company_tagline' => 'Provider Internet Cepat, Stabil & Hemat untuk Warga Desa',
            'company_phone' => '081227391262',
            'company_address' => 'Jl. Poros Desa Utama No. 88, Jawa Timur',
            'bank_accounts' => "MUHAMMAD IRZAQ IRAWAN\n7233297792 BSI",
            'currency_symbol' => 'Rp',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // 3. Villages
        $villagesData = [
            ['name' => 'Desa Sukamaju', 'code' => 'SKM', 'district' => 'Kecamatan Makmur', 'system_type' => 'hybrid', 'notes' => 'Server OLT utama berada di balai desa, 4 ODP terpasang'],
            ['name' => 'Desa Karanganyar', 'code' => 'KRA', 'district' => 'Kecamatan Makmur', 'system_type' => 'pppoe', 'notes' => 'Jalur fiber optic tiang PLN, 3 ODP terpasang'],
            ['name' => 'Desa Sumbersekar', 'code' => 'SBS', 'district' => 'Kecamatan Makmur', 'system_type' => 'voucher', 'notes' => 'Wilayah pegunungan/lereng, coverage radio AP + voucher hotspot'],
            ['name' => 'Desa Wonokerto', 'code' => 'WNK', 'district' => 'Kecamatan Makmur', 'system_type' => 'hybrid', 'notes' => 'Wilayah timur, 2 ODP sub-distribution'],
        ];

        $villages = [];
        foreach ($villagesData as $v) {
            $villages[] = Village::firstOrCreate(['name' => $v['name']], $v);
        }

        // 4. Internet Packages
        $packagesData = [
            ['name' => 'Paket Hemat 5 Mbps', 'speed' => '5 Mbps', 'price' => 100000, 'description' => 'Cocok untuk chat WhatsApp, browsing santai 1-2 HP', 'is_active' => true],
            ['name' => 'Hoki Home 10 Mbps', 'speed' => '10 Mbps', 'price' => 150000, 'description' => 'Paket terfavorit keluarga, nonton YouTube HD & Zoom lancar', 'is_active' => true],
            ['name' => 'Hoki Family 20 Mbps', 'speed' => '20 Mbps', 'price' => 220000, 'description' => 'Streaming 4K, Smart TV, game online anti lag banyak device', 'is_active' => true],
            ['name' => 'Hoki Bisnis 50 Mbps', 'speed' => '50 Mbps', 'price' => 450000, 'description' => 'Khusus kantor desa, toko, warnet/cafe & usaha UMKM', 'is_active' => true],
        ];

        $packages = [];
        foreach ($packagesData as $p) {
            $packages[] = Package::firstOrCreate(['name' => $p['name']], $p);
        }

        // 5. Customers
        $customersData = [
            [
                'customer_code' => 'HKG-001',
                'name' => 'Bapak Slamet Raharjo',
                'phone' => '081234567801',
                'village_id' => $villages[0]->id,
                'package_id' => $packages[1]->id,
                'address' => 'RT 02 RW 01, Dusun Krajan',
                'monthly_fee' => 150000,
                'due_date_day' => 10,
                'installation_date' => Carbon::now()->subMonths(8)->format('Y-m-d'),
                'router_model' => 'ZTE F609',
                'pppoe_user' => 'slamet_skm',
                'pppoe_password' => 'slamet123',
                'ip_address' => '192.168.10.11',
                'latitude' => -7.535800,
                'longitude' => 112.237900,
                'status' => 'active',
                'notes' => 'Kabel dropcore hitam 75m dari ODP-01 port 2. Rumah cat putih pagar besi hitam.',
            ],
            [
                'customer_code' => 'HKG-002',
                'name' => 'Ibu Siti Aminah',
                'phone' => '081398765402',
                'village_id' => $villages[0]->id,
                'package_id' => $packages[0]->id,
                'address' => 'RT 04 RW 02, Dekat Masjid Nurul Huda',
                'monthly_fee' => 100000,
                'due_date_day' => 5,
                'installation_date' => Carbon::now()->subMonths(6)->format('Y-m-d'),
                'router_model' => 'Fiberhome HG6243C',
                'pppoe_user' => 'aminah_skm',
                'pppoe_password' => 'aminah456',
                'ip_address' => '192.168.10.12',
                'latitude' => -7.536300,
                'longitude' => 112.238400,
                'status' => 'active',
                'notes' => 'Tiang bambu sendiri di halaman. Bayar selalu tepat tanggal 5.',
            ],
            [
                'customer_code' => 'HKG-003',
                'name' => 'Warung Kopi Mas Budi',
                'phone' => '082155667703',
                'village_id' => $villages[1]->id,
                'package_id' => $packages[2]->id,
                'address' => 'Jl. Poros Karanganyar No. 12',
                'monthly_fee' => 220000,
                'due_date_day' => 10,
                'installation_date' => Carbon::now()->subMonths(5)->format('Y-m-d'),
                'router_model' => 'Huawei HG8245H5',
                'pppoe_user' => 'warkop_budi',
                'pppoe_password' => 'kopi789',
                'ip_address' => '192.168.20.15',
                'latitude' => -7.537100,
                'longitude' => 112.239100,
                'status' => 'active',
                'notes' => 'Pasang access point tambahan di gazebo outdoor. Sering minta cek kuota/speed.',
            ],
            [
                'customer_code' => 'HKG-004',
                'name' => 'Kantor Balai Desa Karanganyar',
                'phone' => '085744332204',
                'village_id' => $villages[1]->id,
                'package_id' => $packages[3]->id,
                'address' => 'Kompleks Balai Desa Karanganyar',
                'monthly_fee' => 450000,
                'due_date_day' => 15,
                'installation_date' => Carbon::now()->subMonths(10)->format('Y-m-d'),
                'router_model' => 'Mikrotik RB750Gr3 + Huawei ONT',
                'pppoe_user' => 'pemdes_karanganyar',
                'pppoe_password' => 'balai2026',
                'ip_address' => '192.168.20.2',
                'latitude' => -7.535200,
                'longitude' => 112.236800,
                'status' => 'active',
                'notes' => 'Ruang Pelayanan & Ruang Kades. Surat tagihan resmi diminta kirim WA ke bendahara desa.',
            ],
            [
                'customer_code' => 'HKG-005',
                'name' => 'Pak Joko Widodo (Bengkel Las)',
                'phone' => '087811223305',
                'village_id' => $villages[2]->id,
                'package_id' => $packages[1]->id,
                'address' => 'RT 01 RW 03, Sumbersekar Timur',
                'monthly_fee' => 150000,
                'due_date_day' => 10,
                'installation_date' => Carbon::now()->subMonths(4)->format('Y-m-d'),
                'router_model' => 'ZTE F609',
                'pppoe_user' => 'joko_sbs',
                'pppoe_password' => 'las321',
                'ip_address' => '192.168.30.22',
                'latitude' => -7.537800,
                'longitude' => 112.240200,
                'status' => 'active',
                'notes' => 'Kabel lewat pohon mangga tetangga (sudah izin). Router di ruang tamu.',
            ],
            [
                'customer_code' => 'HKG-006',
                'name' => 'Ibu Endang Sri Rejeki',
                'phone' => '081299887706',
                'village_id' => $villages[2]->id,
                'package_id' => $packages[1]->id,
                'address' => 'RT 03 RW 01, Sumbersekar Barat',
                'monthly_fee' => 150000,
                'due_date_day' => 5,
                'installation_date' => Carbon::now()->subMonths(3)->format('Y-m-d'),
                'router_model' => 'Fiberhome HG6243C',
                'pppoe_user' => 'endang_sbs',
                'pppoe_password' => 'endang99',
                'ip_address' => '192.168.30.25',
                'latitude' => -7.536700,
                'longitude' => 112.237200,
                'status' => 'isolated',
                'notes' => 'Pernah minta ganti password wifi. Status sementara diisolir karena telat bayar 2 bulan.',
            ],
            [
                'customer_code' => 'HKG-007',
                'name' => 'Hendra Prasetyo',
                'phone' => '089633221107',
                'village_id' => $villages[3]->id,
                'package_id' => $packages[2]->id,
                'address' => 'Dusun Krajan RT 05 RW 02, Wonokerto',
                'monthly_fee' => 220000,
                'due_date_day' => 20,
                'installation_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'router_model' => 'Huawei HG8245H5',
                'pppoe_user' => 'hendra_wnk',
                'pppoe_password' => 'hendra20',
                'ip_address' => '192.168.40.10',
                'latitude' => -7.538300,
                'longitude' => 112.241500,
                'status' => 'active',
                'notes' => 'Gamer & streamer. Permintaan ping stabil ke game Mobile Legends & Valorant.',
            ],
            [
                'customer_code' => 'HKG-008',
                'name' => 'Toko Kelontong Bu Anik',
                'phone' => '081288990008',
                'village_id' => $villages[3]->id,
                'package_id' => $packages[1]->id,
                'address' => 'Pasar Wonokerto Kios No. 4',
                'monthly_fee' => 150000,
                'due_date_day' => 10,
                'installation_date' => Carbon::now()->subMonths(7)->format('Y-m-d'),
                'router_model' => 'ZTE F609',
                'pppoe_user' => 'kios_anik',
                'pppoe_password' => 'anik88',
                'ip_address' => '192.168.40.18',
                'latitude' => -7.538900,
                'longitude' => 112.239800,
                'status' => 'active',
                'notes' => 'Dipakai untuk EDC mesin gesek dan CCTV toko.',
            ],
        ];

        $customers = [];
        foreach ($customersData as $c) {
            $customers[] = Customer::firstOrCreate(['customer_code' => $c['customer_code']], $c);
        }

        // 6. Invoices for Current Month and Previous Month
        $currentPeriod = Carbon::now()->format('Y-m');
        $prevPeriod = Carbon::now()->subMonth()->format('Y-m');

        // Past month invoices (all paid)
        foreach ($customers as $index => $customer) {
            $invNum = 'INV-' . str_replace('-', '', $prevPeriod) . '-' . str_pad($customer->id, 4, '0', STR_PAD_LEFT);
            $inv = Invoice::firstOrCreate(
                ['invoice_number' => $invNum],
                [
                    'customer_id' => $customer->id,
                    'period' => $prevPeriod,
                    'amount' => $customer->monthly_fee,
                    'due_date' => Carbon::createFromFormat('Y-m', $prevPeriod)->setDay($customer->due_date_day)->format('Y-m-d'),
                    'status' => 'paid',
                    'paid_at' => Carbon::createFromFormat('Y-m', $prevPeriod)->setDay($customer->due_date_day)->subDays(1)->setTime(10, 30),
                    'payment_method' => $index % 2 === 0 ? 'transfer' : 'cash',
                    'notes' => 'Pembayaran lunas bulan lalu',
                    'created_by' => $admin->id,
                ]
            );

            // Record income transaction for past month
            FinancialTransaction::firstOrCreate(
                ['transaction_code' => 'TRX-' . $inv->invoice_number],
                [
                    'type' => 'income',
                    'category' => 'Tagihan WiFi',
                    'amount' => $inv->amount,
                    'transaction_date' => $inv->paid_at->format('Y-m-d'),
                    'invoice_id' => $inv->id,
                    'user_id' => $admin->id,
                    'payment_method' => $inv->payment_method,
                    'description' => "Pembayaran tagihan {$customer->name} ({$customer->customer_code}) periode {$prevPeriod}",
                ]
            );
        }

        // Current month invoices (mixed status: some paid, some unpaid, some overdue)
        foreach ($customers as $index => $customer) {
            $invNum = 'INV-' . str_replace('-', '', $currentPeriod) . '-' . str_pad($customer->id, 4, '0', STR_PAD_LEFT);
            $dueDate = Carbon::createFromFormat('Y-m', $currentPeriod)->setDay($customer->due_date_day);
            $isPastDue = Carbon::now()->gt($dueDate);

            // Let first 4 be paid, 5 & 7 unpaid, 6 isolated/overdue, 8 unpaid
            if ($index < 4) {
                $status = 'paid';
                $paidAt = Carbon::now()->subDays(2)->setTime(14, 15);
                $method = $index % 2 === 0 ? 'transfer' : 'cash';
            } elseif ($index == 5 || $isPastDue) {
                $status = 'overdue';
                $paidAt = null;
                $method = null;
            } else {
                $status = 'unpaid';
                $paidAt = null;
                $method = null;
            }

            $inv = Invoice::firstOrCreate(
                ['invoice_number' => $invNum],
                [
                    'customer_id' => $customer->id,
                    'period' => $currentPeriod,
                    'amount' => $customer->monthly_fee,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'status' => $status,
                    'paid_at' => $paidAt,
                    'payment_method' => $method,
                    'notes' => $status === 'paid' ? 'Lunas via ' . strtoupper($method) : 'Menunggu pembayaran',
                    'created_by' => $admin->id,
                ]
            );

            if ($status === 'paid') {
                FinancialTransaction::firstOrCreate(
                    ['transaction_code' => 'TRX-' . $inv->invoice_number],
                    [
                        'type' => 'income',
                        'category' => 'Tagihan WiFi',
                        'amount' => $inv->amount,
                        'transaction_date' => $inv->paid_at->format('Y-m-d'),
                        'invoice_id' => $inv->id,
                        'user_id' => $admin->id,
                        'payment_method' => $inv->payment_method,
                        'description' => "Pembayaran tagihan {$customer->name} ({$customer->customer_code}) periode {$currentPeriod}",
                    ]
                );
            }
        }

        // 7. Operational Expenses for current month & previous month
        $expenses = [
            // Current month
            [
                'transaction_code' => 'EXP-' . Carbon::now()->format('Ym') . '-001',
                'type' => 'expense',
                'category' => 'Bandwidth / ISP Utama',
                'amount' => 1500000,
                'transaction_date' => Carbon::now()->startOfMonth()->addDays(1)->format('Y-m-d'),
                'payment_method' => 'transfer',
                'description' => 'Langganan Dedicated Bandwidth Upstream 100 Mbps ke ISP Induk',
                'user_id' => $owner->id,
            ],
            [
                'transaction_code' => 'EXP-' . Carbon::now()->format('Ym') . '-002',
                'type' => 'expense',
                'category' => 'Listrik & Biaya ODP',
                'amount' => 200000,
                'transaction_date' => Carbon::now()->startOfMonth()->addDays(2)->format('Y-m-d'),
                'payment_method' => 'transfer',
                'description' => 'Token Listrik Server OLT & Router Sentral',
                'user_id' => $admin->id,
            ],
            [
                'transaction_code' => 'EXP-' . Carbon::now()->format('Ym') . '-003',
                'type' => 'expense',
                'category' => 'Beli Alat & Kabel',
                'amount' => 380000,
                'transaction_date' => Carbon::now()->startOfMonth()->addDays(3)->format('Y-m-d'),
                'payment_method' => 'cash',
                'description' => 'Pembelian 1 Roll Kabel Dropcore 1 Core 3 Seling 1000m + Fast Connector',
                'user_id' => $admin->id,
            ],
            [
                'transaction_code' => 'EXP-' . Carbon::now()->format('Ym') . '-004',
                'type' => 'expense',
                'category' => 'Konsumsi & Operasional',
                'amount' => 125000,
                'transaction_date' => Carbon::now()->startOfMonth()->addDays(3)->format('Y-m-d'),
                'payment_method' => 'cash',
                'description' => 'Bensin & konsumsi teknisi patroli perbaikan jalur ODP Desa Sumbersekar',
                'user_id' => $admin->id,
            ],
            // Previous month
            [
                'transaction_code' => 'EXP-' . Carbon::now()->subMonth()->format('Ym') . '-001',
                'type' => 'expense',
                'category' => 'Bandwidth / ISP Utama',
                'amount' => 1500000,
                'transaction_date' => Carbon::now()->subMonth()->startOfMonth()->addDays(1)->format('Y-m-d'),
                'payment_method' => 'transfer',
                'description' => 'Langganan Dedicated Bandwidth Upstream bulan lalu',
                'user_id' => $owner->id,
            ],
            [
                'transaction_code' => 'EXP-' . Carbon::now()->subMonth()->format('Ym') . '-002',
                'type' => 'expense',
                'category' => 'Gaji Teknisi / Karyawan',
                'amount' => 1000000,
                'transaction_date' => Carbon::now()->subMonth()->endOfMonth()->subDays(2)->format('Y-m-d'),
                'payment_method' => 'transfer',
                'description' => 'Gaji teknisi jaringan & admin lapangan',
                'user_id' => $owner->id,
            ],
        ];

        foreach ($expenses as $exp) {
            FinancialTransaction::firstOrCreate(['transaction_code' => $exp['transaction_code']], $exp);
        }

        // 8. Sample Voucher Sales
        $voucherSales = [
            [
                'village_id' => $villages[0]->id,
                'seller_name' => 'Warung Bu Siti (Depan Balai Desa)',
                'seller_phone' => '081227391262',
                'period' => Carbon::now()->format('Y-m'),
                'voucher_type' => 'Voucher 24 Jam',
                'price_per_unit' => 5000,
                'quantity_sold' => 120,
                'total_gross_amount' => 600000,
                'fee_per_unit' => 1000,
                'total_seller_fee' => 120000,
                'net_amount' => 480000,
                'status' => 'deposited',
                'sale_date' => Carbon::now()->toDateString(),
                'deposited_at' => Carbon::now(),
                'notes' => 'Stok voucher batch 1 lunas disetor',
                'created_by' => $owner->id,
            ],
            [
                'village_id' => $villages[2]->id,
                'seller_name' => 'Konter Mas Agus Sumbersekar',
                'seller_phone' => '085811223344',
                'period' => Carbon::now()->format('Y-m'),
                'voucher_type' => 'Voucher 7 Hari',
                'price_per_unit' => 20000,
                'quantity_sold' => 45,
                'total_gross_amount' => 900000,
                'fee_per_unit' => 3000,
                'total_seller_fee' => 135000,
                'net_amount' => 765000,
                'status' => 'deposited',
                'sale_date' => Carbon::now()->toDateString(),
                'deposited_at' => Carbon::now(),
                'notes' => 'Penjualan laris di area pasar dan balai dusun',
                'created_by' => $admin->id,
            ],
        ];

        foreach ($voucherSales as $vs) {
            \App\Models\VoucherSale::firstOrCreate([
                'seller_name' => $vs['seller_name'],
                'period' => $vs['period'],
                'voucher_type' => $vs['voucher_type'],
            ], $vs);
        }
    }
}
