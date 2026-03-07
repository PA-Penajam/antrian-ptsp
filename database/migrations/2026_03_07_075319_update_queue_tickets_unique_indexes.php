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
            $table->dropUnique('queue_tickets_pool_date_number_unique');
            $table->unique(['service_id', 'service_date', 'ticket_number'], 'queue_tickets_service_date_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_tickets', function (Blueprint $table) {
            $table->dropUnique('queue_tickets_service_date_number_unique');
            $table->unique(['queue_pool_id', 'service_date', 'ticket_number'], 'queue_tickets_pool_date_number_unique');
        });
    }
};
