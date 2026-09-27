<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Header transaksi POS
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();      // INV-20240101-0001
            $table->foreignId('shift_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // kasir

            // Pelanggan (opsional)
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();

            // Total
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            // Pembayaran
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->decimal('amount_paid', 15, 2)->default(0);  // uang dibayar
            $table->decimal('change_amount', 15, 2)->default(0); // kembalian
            $table->string('payment_reference')->nullable();     // no. ref QRIS/transfer

            $table->enum('status', ['pending', 'paid', 'voided'])->default('paid');
            $table->text('notes')->nullable();
            $table->timestamp('transaction_at');
            $table->timestamps();
            $table->softDeletes();
        });

        // Detail item per transaksi
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            $table->string('product_name');                     // snapshot nama saat transaksi
            $table->string('product_code');                     // snapshot kode
            $table->string('product_barcode')->nullable();      // snapshot barcode

            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);               // harga jual saat transaksi
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);                 // qty * unit_price - discount

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
    }
};
