<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('counter_sessions')) {
            $this->syncExistingTable();

            return;
        }

        Schema::create('counter_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counter_id')->constrained()->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamps();

            $table->index(['counter_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    private function syncExistingTable(): void
    {
        if (! Schema::hasIndex('counter_sessions', ['counter_id', 'status'])) {
            Schema::table('counter_sessions', function (Blueprint $table): void {
                $table->index(['counter_id', 'status']);
            });
        }

        if (! Schema::hasIndex('counter_sessions', ['user_id', 'status'])) {
            Schema::table('counter_sessions', function (Blueprint $table): void {
                $table->index(['user_id', 'status']);
            });
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (! $this->hasForeignKey('counter_sessions', 'counter_sessions_counter_id_foreign')) {
            Schema::table('counter_sessions', function (Blueprint $table): void {
                $table->foreign('counter_id')
                    ->references('id')
                    ->on('counters')
                    ->cascadeOnUpdate();
            });
        }

        if (! $this->hasForeignKey('counter_sessions', 'counter_sessions_user_id_foreign')) {
            Schema::table('counter_sessions', function (Blueprint $table): void {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnUpdate();
            });
        }
    }

    private function hasForeignKey(string $table, string $constraintName): bool
    {
        $result = DB::select(
            'SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ? LIMIT 1',
            [$table, $constraintName, 'FOREIGN KEY']
        );

        return ! empty($result);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counter_sessions');
    }
};
