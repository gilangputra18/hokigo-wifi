<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\VillageController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes (Owner & Admin)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pelanggan (Customer Management)
    Route::resource('customers', CustomerController::class);
    Route::post('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');

    // Tagihan & WhatsApp Invoicing
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('/invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::post('/invoices/{invoice}/mark-unpaid', [InvoiceController::class, 'markUnpaid'])->name('invoices.mark-unpaid');
    Route::post('/invoices/{invoice}/mark-wa-sent', [InvoiceController::class, 'markWaSent'])->name('invoices.mark-wa-sent');
    Route::get('/invoices/reminders', [InvoiceController::class, 'reminders'])->name('invoices.reminders');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    // Keuangan (Finance: Omzet, Pengeluaran, Laba Bersih)
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::post('/finance/expense', [FinanceController::class, 'storeExpense'])->name('finance.expense.store');
    Route::post('/finance/income', [FinanceController::class, 'storeIncome'])->name('finance.income.store');
    Route::get('/finance/export-csv', [FinanceController::class, 'exportCsv'])->name('finance.export-csv');
    Route::delete('/finance/{transaction}', [FinanceController::class, 'destroy'])->name('finance.destroy');

    // Master Desa
    Route::get('/villages', [VillageController::class, 'index'])->name('villages.index');
    Route::post('/villages', [VillageController::class, 'store'])->name('villages.store');
    Route::put('/villages/{village}', [VillageController::class, 'update'])->name('villages.update');
    Route::delete('/villages/{village}', [VillageController::class, 'destroy'])->name('villages.destroy');

    // Master Paket Internet
    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
    Route::put('/packages/{package}', [PackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');

    // Settings (Owner & Admin)
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/general', [SettingController::class, 'updateGeneral'])->name('settings.general');
    Route::post('/settings/users', [SettingController::class, 'storeUser'])->name('settings.users.store');
    Route::put('/settings/users/{user}', [SettingController::class, 'updateUser'])->name('settings.users.update');
    Route::delete('/settings/users/{user}', [SettingController::class, 'destroyUser'])->name('settings.users.destroy');
});
