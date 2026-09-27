<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel master tipe keluar stok (rusak, retur, adjustment, dll)
        Schema::create('stock_out_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();           // DAMAGED, RETURN, ADJUSTMENT, OTHER
            $table->string('name');                     // Barang Rusak, Retur Supplier, Penyesuaian
            $table->string('color')->default('secondary'); // badge color: danger, warning, info, secondary
            $table->string('icon')->nullable();          // FA icon
            $table->boolean('affects_stock')->default(true); // apakah mengurangi stok?
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Tambah FK ke stock_outs agar bisa pakai master tipe
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->foreignId('stock_out_type_id')->nullable()->after('type')
                  ->constrained('stock_out_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\StockOutType::class);
            $table->dropColumn('stock_out_type_id');
        });
        Schema::dropIfExists('stock_out_types');
    }
};
