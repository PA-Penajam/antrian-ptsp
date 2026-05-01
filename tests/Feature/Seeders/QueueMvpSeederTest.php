<?php

use Database\Seeders\QueueMvpSeeder;

test('queue mvp seeder creates default pools services and counters', function () {
    $this->seed(QueueMvpSeeder::class);

    $this->assertDatabaseCount('queue_pools', 3);
    $this->assertDatabaseCount('services', 3);
    $this->assertDatabaseCount('counters', 6);

    $this->assertDatabaseHas('queue_pools', ['code' => 'UMUM']);
    $this->assertDatabaseHas('queue_pools', ['code' => 'BAYAR']);
    $this->assertDatabaseHas('queue_pools', ['code' => 'POSBAKUM']);

    $this->assertDatabaseHas('services', ['name' => 'Layanan Umum']);
    $this->assertDatabaseHas('services', ['name' => 'Pembayaran']);
    $this->assertDatabaseHas('services', ['name' => 'Posbakum']);
});
