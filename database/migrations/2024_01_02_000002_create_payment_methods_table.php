<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();         // CASH, QRIS, TRANSFER, EDC
            $table->string('name');                   // Tunai, QRIS, Transfer Bank, Kartu Debit
            $table->string('type')->default('cash');  // cash | digital | card
            $table->text('description')->nullable();
            $table->string('icon')->nullable();       // FA icon class
            $table->boolean('requires_reference')->default(false); // butuh no. referensi?
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
