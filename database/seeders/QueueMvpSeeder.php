<?php

namespace Database\Seeders;

use App\Models\Counter;
use App\Models\QueuePool;
use App\Models\Service;
use Illuminate\Database\Seeder;

class QueueMvpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $umumPool = QueuePool::query()->firstOrCreate(
            ['code' => 'UMUM'],
            [
                'name' => 'Pool Umum',
                'description' => 'Pool untuk layanan umum PTSP.',
                'is_active' => true,
            ]
        );

        $bayarPool = QueuePool::query()->firstOrCreate(
            ['code' => 'BAYAR'],
            [
                'name' => 'Pool Pembayaran',
                'description' => 'Pool khusus layanan pembayaran.',
                'is_active' => true,
            ]
        );

        $posbakumPool = QueuePool::query()->firstOrCreate(
            ['code' => 'POSBAKUM'],
            [
                'name' => 'Pool Posbakum',
                'description' => 'Pool khusus layanan pos bantuan hukum.',
                'is_active' => true,
            ]
        );

        Service::query()->firstOrCreate(
            ['code' => 'A'],
            [
                'queue_pool_id' => $umumPool->id,
                'name' => 'Pendaftaran',
                'slug' => 'pendaftaran',
                'description' => 'Layanan pendaftaran perkara.',
                'requirements' => 'Dokumen identitas dan berkas permohonan.',
                'is_active' => true,
                'booking_enabled' => true,
                'walk_in_enabled' => true,
                'daily_quota' => 100,
                'sort_order' => 1,
            ]
        );

        Service::query()->firstOrCreate(
            ['code' => 'C'],
            [
                'queue_pool_id' => $umumPool->id,
                'name' => 'Informasi/Pengaduan',
                'slug' => 'informasi-pengaduan',
                'description' => 'Layanan informasi dan pengaduan.',
                'requirements' => 'Sampaikan kebutuhan atau pengaduan secara jelas.',
                'is_active' => true,
                'booking_enabled' => true,
                'walk_in_enabled' => true,
                'daily_quota' => 100,
                'sort_order' => 2,
            ]
        );

        Service::query()->firstOrCreate(
            ['code' => 'D'],
            [
                'queue_pool_id' => $umumPool->id,
                'name' => 'Pengambilan Produk Hukum',
                'slug' => 'pengambilan-produk-hukum',
                'description' => 'Layanan pengambilan produk hukum.',
                'requirements' => 'Bawa identitas dan bukti pengambilan.',
                'is_active' => true,
                'booking_enabled' => true,
                'walk_in_enabled' => true,
                'daily_quota' => 100,
                'sort_order' => 3,
            ]
        );

        Service::query()->firstOrCreate(
            ['code' => 'B'],
            [
                'queue_pool_id' => $bayarPool->id,
                'name' => 'Pembayaran',
                'slug' => 'pembayaran',
                'description' => 'Layanan pembayaran biaya perkara.',
                'requirements' => 'Bawa rincian tagihan atau informasi perkara.',
                'is_active' => true,
                'booking_enabled' => true,
                'walk_in_enabled' => true,
                'daily_quota' => 80,
                'sort_order' => 4,
            ]
        );

        Service::query()->firstOrCreate(
            ['code' => 'E'],
            [
                'queue_pool_id' => $posbakumPool->id,
                'name' => 'Posbakum',
                'slug' => 'posbakum',
                'description' => 'Layanan pos bantuan hukum.',
                'requirements' => 'Sampaikan kebutuhan konsultasi bantuan hukum.',
                'is_active' => true,
                'booking_enabled' => true,
                'walk_in_enabled' => true,
                'daily_quota' => 60,
                'sort_order' => 5,
            ]
        );

        Counter::query()->firstOrCreate(
            ['code' => 'U1'],
            ['queue_pool_id' => $umumPool->id, 'name' => 'Loket Umum 1', 'is_active' => true, 'sort_order' => 1]
        );

        Counter::query()->firstOrCreate(
            ['code' => 'U2'],
            ['queue_pool_id' => $umumPool->id, 'name' => 'Loket Umum 2', 'is_active' => true, 'sort_order' => 2]
        );

        Counter::query()->firstOrCreate(
            ['code' => 'U3'],
            ['queue_pool_id' => $umumPool->id, 'name' => 'Loket Umum 3', 'is_active' => true, 'sort_order' => 3]
        );

        Counter::query()->firstOrCreate(
            ['code' => 'BYR'],
            ['queue_pool_id' => $bayarPool->id, 'name' => 'Loket Pembayaran', 'is_active' => true, 'sort_order' => 4]
        );

        Counter::query()->firstOrCreate(
            ['code' => 'PBK'],
            ['queue_pool_id' => $posbakumPool->id, 'name' => 'Loket Posbakum', 'is_active' => true, 'sort_order' => 5]
        );
    }
}
