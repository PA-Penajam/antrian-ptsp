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
        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->string('visit_purpose', 50)->nullable()->after('visitor_wilayah_kode');
        });

        Schema::table('counters', function (Blueprint $table) {
            $table->boolean('is_fixed')->default(false)->after('is_active');
        });

        Schema::table('counter_sessions', function (Blueprint $table) {
            $table->foreignId('assigned_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counter_sessions', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            $table->dropColumn('assigned_by');
        });

        Schema::table('counters', function (Blueprint $table) {
            $table->dropColumn('is_fixed');
        });

        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->dropColumn('visit_purpose');
        });
    }
};
