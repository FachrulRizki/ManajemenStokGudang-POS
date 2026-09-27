<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->string('location')->nullable();   // alamat / lokasi fisik
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->string('code')->nullable();       // mis: A, B, C
            $table->string('name');                   // mis: Rak A
            $table->string('row')->nullable();         // baris
            $table->string('column')->nullable();      // kolom
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Relasikan produk ke rack
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('rack_id')->nullable()->after('rack_location')
                  ->constrained('racks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Rack::class);
            $table->dropColumn('rack_id');
        });
        Schema::dropIfExists('racks');
        Schema::dropIfExists('warehouses');
    }
};
