<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wip_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->constrained()->onDelete('cascade');
            $table->foreignId('stage_id')->constrained('production_stages')->onDelete('restrict');
            $table->integer('quantity');
            $table->timestamps();
            
            $table->unique(['batch_id', 'stage_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wip_inventory');
    }
};

