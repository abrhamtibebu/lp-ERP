<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('product_name');
            $table->string('color')->nullable();
            $table->string('sku')->unique();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->text('consumption_formula')->nullable(); // Formula for auto-calculation
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

