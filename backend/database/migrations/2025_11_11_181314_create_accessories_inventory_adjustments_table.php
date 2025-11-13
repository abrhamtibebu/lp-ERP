<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accessories_inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('accessory_inventory_id')->constrained('accessories_inventory')->onDelete('cascade');
            $table->enum('adjustment_type', ['add', 'deduct'])->default('add');
            $table->decimal('quantity', 10, 2);
            $table->text('notes')->nullable();
            $table->foreignId('adjusted_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('adjusted_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessories_inventory_adjustments');
    }
};
