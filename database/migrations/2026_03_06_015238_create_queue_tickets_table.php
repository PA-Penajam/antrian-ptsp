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
        Schema::create('queue_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnUpdate();
            $table->foreignId('queue_pool_id')->constrained()->cascadeOnUpdate();
            $table->foreignId('counter_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('channel', 40);
            $table->string('ticket_number', 40);
            $table->unsignedInteger('sequence_number');
            $table->date('service_date');
            $table->string('visitor_name');
            $table->string('visitor_identifier', 64)->nullable();
            $table->string('visitor_phone', 30)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30);
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['queue_pool_id', 'service_date', 'sequence_number'], 'queue_tickets_pool_date_sequence_unique');
            $table->unique(['queue_pool_id', 'service_date', 'ticket_number'], 'queue_tickets_pool_date_number_unique');
            $table->index(['service_date', 'status']);
            $table->index(['queue_pool_id', 'service_date', 'status']);
            $table->index(['service_id', 'service_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_tickets');
    }
};
