<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('migrate handles existing counter sessions table without migration record', function () {
    $migrationName = DB::table('migrations')
        ->where('migration', 'like', '%create_counter_sessions_table')
        ->value('migration');

    expect($migrationName)->not->toBeNull();

    DB::table('migrations')
        ->where('migration', $migrationName)
        ->delete();

    Schema::dropIfExists('counter_sessions');

    Schema::create('counter_sessions', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('counter_id');
        $table->foreignId('user_id');
        $table->timestamp('opened_at');
        $table->timestamp('closed_at')->nullable();
        $table->string('status', 20)->default('open');
        $table->timestamps();
    });

    $exitCode = Artisan::call('migrate', ['--no-interaction' => true]);

    expect($exitCode)->toBe(0)
        ->and(DB::table('migrations')->where('migration', $migrationName)->exists())->toBeTrue()
        ->and(Schema::hasIndex('counter_sessions', ['counter_id', 'status']))->toBeTrue()
        ->and(Schema::hasIndex('counter_sessions', ['user_id', 'status']))->toBeTrue();
});
