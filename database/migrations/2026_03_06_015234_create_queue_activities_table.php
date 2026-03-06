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
        Schema::create('queue_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_ticket_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('counter_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->string('action', 40);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['queue_ticket_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_activities');
    }
};
