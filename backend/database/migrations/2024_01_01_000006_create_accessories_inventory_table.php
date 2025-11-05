<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accessories_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('pcs'); // pieces, kg, etc.
            $table->string('import_invoice_number')->nullable();
            $table->foreignId('submitted_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('received_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessories_inventory');
    }
};

