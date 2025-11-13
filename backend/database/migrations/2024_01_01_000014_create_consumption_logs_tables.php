<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leather_consumption_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('leather_inventory_id')->constrained('leather_inventory')->onDelete('restrict');
            $table->decimal('quantity_consumed', 10, 2);
            $table->enum('consumption_type', ['formula', 'manual', 'hybrid']);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['batch_id', 'created_at']);
        });

        Schema::create('accessories_consumption_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('accessory_inventory_id')->constrained('accessories_inventory')->onDelete('restrict');
            $table->decimal('quantity_consumed', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['batch_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessories_consumption_logs');
        Schema::dropIfExists('leather_consumption_logs');
    }
};

