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
        Schema::create('finished_goods_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('finished_good_id')->constrained('finished_goods')->onDelete('cascade');
            $table->enum('adjustment_type', ['add', 'deduct'])->default('add');
            $table->integer('quantity');
            $table->text('reason')->nullable();
            $table->string('export_reference')->nullable();
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
        Schema::dropIfExists('finished_goods_adjustments');
    }
};
