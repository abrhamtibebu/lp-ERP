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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('business_number')->nullable()->after('tin_number');
            $table->text('address')->nullable()->after('business_number');
            $table->string('woreda')->nullable()->after('address');
            $table->string('house_number')->nullable()->after('woreda');
            $table->string('phone_number')->nullable()->after('house_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['business_number', 'address', 'woreda', 'house_number', 'phone_number']);
        });
    }
};
