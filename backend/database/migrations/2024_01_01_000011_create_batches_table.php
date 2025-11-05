<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('batch_id')->unique(); // Auto-generated
            $table->foreignId('current_stage_id')->nullable()->constrained('production_stages')->onDelete('set null');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'rework'])->default('pending');
            $table->integer('total_quantity');
            $table->integer('current_quantity'); // Current quantity in WIP
            $table->timestamps();
            
            $table->index(['tenant_id', 'batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};

