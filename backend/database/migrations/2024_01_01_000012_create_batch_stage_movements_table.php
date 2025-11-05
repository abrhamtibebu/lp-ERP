<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_stage_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_stage_id')->nullable()->constrained('production_stages')->onDelete('set null');
            $table->foreignId('to_stage_id')->constrained('production_stages')->onDelete('restrict');
            $table->integer('quantity');
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['batch_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_stage_movements');
    }
};

