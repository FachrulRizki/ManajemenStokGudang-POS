<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_outs', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->date('transaction_date');
            $table->string('customer_name')->nullable();
            $table->enum('type', ['sale', 'return', 'damaged', 'other'])->default('sale');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_outs');
    }
};
