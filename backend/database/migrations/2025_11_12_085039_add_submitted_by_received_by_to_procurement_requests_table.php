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
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->foreignId('submitted_by')->nullable()->after('requested_by')->constrained('users')->onDelete('set null');
            $table->foreignId('received_by')->nullable()->after('submitted_by')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['received_by']);
            $table->dropColumn(['submitted_by', 'received_by']);
        });
    }
};
