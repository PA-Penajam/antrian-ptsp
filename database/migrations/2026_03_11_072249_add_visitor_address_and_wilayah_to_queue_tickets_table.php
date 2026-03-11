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
            $table->string('visitor_address')->nullable()->after('visitor_phone');
            $table->string('visitor_wilayah_kode', 13)->nullable()->after('visitor_address');

            $table->index('visitor_wilayah_kode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->dropIndex(['visitor_wilayah_kode']);
            $table->dropColumn([
                'visitor_address',
                'visitor_wilayah_kode',
            ]);
        });
    }
};
