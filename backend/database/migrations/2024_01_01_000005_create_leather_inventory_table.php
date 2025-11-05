<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leather_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('leather_name');
            $table->string('brand_make')->nullable();
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            $table->date('purchase_date');
            $table->decimal('quantity_sqft', 10, 2); // Quantity in square feet
            $table->decimal('consumption_reduction', 10, 2)->default(0); // Auto or manual
            $table->foreignId('submitted_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('received_by')->constrained('users')->onDelete('restrict');
            $table->string('delivered_to')->nullable(); // Department or Batch
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leather_inventory');
    }
};

