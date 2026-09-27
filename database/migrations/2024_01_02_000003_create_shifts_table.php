<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('shift_number')->unique();        // SHF-20240101-001
            $table->foreignId('user_id')->constrained()->restrictOnDelete(); // kasir
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();

            // Kas awal & akhir
            $table->decimal('opening_cash', 15, 2)->default(0);  // modal awal
            $table->decimal('closing_cash', 15, 2)->nullable();   // uang di laci saat tutup
            $table->decimal('expected_cash', 15, 2)->nullable();  // total seharusnya
            $table->decimal('cash_difference', 15, 2)->nullable(); // selisih

            // Ringkasan shift
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('total_discount', 15, 2)->default(0);
            $table->decimal('total_cash', 15, 2)->default(0);
            $table->decimal('total_non_cash', 15, 2)->default(0);

            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
