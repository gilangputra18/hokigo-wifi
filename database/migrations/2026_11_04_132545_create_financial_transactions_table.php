<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code', 40)->unique();
            $table->enum('type', ['income', 'expense']); // income = pemasukan/omzet, expense = pengeluaran
            $table->string('category', 60);
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payment_method', 30)->nullable();
            $table->text('description')->nullable();
            $table->string('attachment')->nullable(); // proof receipt image
            $table->timestamps();

            $table->index(['type', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
