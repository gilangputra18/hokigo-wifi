<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\VillageController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Peta Jaringan & Penitikan Rumah Pelanggan (Bisa diakses Owner, Admin, & Teknisi)
    Route::get('/maps', [MapController::class, 'index'])->name('maps.index');
    Route::post('/maps/customers/{customer}/location', [MapController::class, 'updateLocation'])->name('maps.update-location');
    Route::post('/maps/nodes', [MapController::class, 'storeNode'])->name('maps.nodes.store');
    Route::delete('/maps/nodes/{node}', [MapController::class, 'destroyNode'])->name('maps.nodes.destroy');
    Route::post('/maps/cables', [MapController::class, 'storeCable'])->name('maps.cables.store');
    Route::delete('/maps/cables/{cable}', [MapController::class, 'destroyCable'])->name('maps.cables.destroy');
    Route::post('/maps/villages/{village}/location', [MapController::class, 'updateVillageLocation'])->name('maps.villages.location');
    Route::post('/maps/reset', [MapController::class, 'resetMapData'])->name('maps.reset');

    // Pelanggan (Bisa diakses Owner, Admin, & Teknisi)
    Route::get('/customers/{customer}/photo', [CustomerController::class, 'photo'])->name('customers.photo');
    Route::resource('customers', CustomerController::class);
    Route::post('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');

    // Kelompok Desa - Tampilan Read-Only untuk Teknisi
    Route::get('/villages', [VillageController::class, 'index'])->name('villages.index');

    // Routes Khusus Owner & Admin (Finansial, Tagihan, Settings, Paket, Master Data)
    Route::middleware('role:owner,admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Tagihan & WhatsApp Invoicing
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::post('/invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
        Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
        Route::post('/invoices/{invoice}/mark-unpaid', [InvoiceController::class, 'markUnpaid'])->name('invoices.mark-unpaid');
        Route::post('/invoices/{invoice}/mark-wa-sent', [InvoiceController::class, 'markWaSent'])->name('invoices.mark-wa-sent');
        Route::get('/invoices/reminders', [InvoiceController::class, 'reminders'])->name('invoices.reminders');
        Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

        // Manajemen Voucheran
        Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
        Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
        Route::post('/vouchers/{voucher}/mark-deposited', [VoucherController::class, 'markDeposited'])->name('vouchers.mark-deposited');
        Route::delete('/vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');

        // Keuangan (Finance: Omzet, Pengeluaran, Laba Bersih)
        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::post('/finance/expense', [FinanceController::class, 'storeExpense'])->name('finance.expense.store');
        Route::post('/finance/income', [FinanceController::class, 'storeIncome'])->name('finance.income.store');
        Route::get('/finance/export-csv', [FinanceController::class, 'exportCsv'])->name('finance.export-csv');
        Route::delete('/finance/{transaction}', [FinanceController::class, 'destroy'])->name('finance.destroy');

        // Master Desa (Write Operations)
        Route::post('/villages', [VillageController::class, 'store'])->name('villages.store');
        Route::put('/villages/{village}', [VillageController::class, 'update'])->name('villages.update');
        Route::delete('/villages/{village}', [VillageController::class, 'destroy'])->name('villages.destroy');

        // Master Paket Internet
        Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
        Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
        Route::put('/packages/{package}', [PackageController::class, 'update'])->name('packages.update');
        Route::delete('/packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/general', [SettingController::class, 'updateGeneral'])->name('settings.general');
        Route::post('/settings/users', [SettingController::class, 'storeUser'])->name('settings.users.store');
        Route::put('/settings/users/{user}', [SettingController::class, 'updateUser'])->name('settings.users.update');
        Route::delete('/settings/users/{user}', [SettingController::class, 'destroyUser'])->name('settings.users.destroy');
    });

    // Route Khusus Owner (Reset Keuangan ke 0)
    Route::middleware('role:owner')->group(function () {
        Route::post('/finance/reset', [FinanceController::class, 'resetData'])->name('finance.reset');
    });
});
