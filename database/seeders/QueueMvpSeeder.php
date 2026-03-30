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
                'name' => 'Pool Layanan Umum',
                'description' => 'Pool untuk layanan umum (gabungan pendaftaran, informasi, pengaduan, produk hukum, eCourt). Satu pool ini digunakan oleh semua officer layanan umum.',
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

        // --- Services (konsolidasi: 3 layanan) ---

        Service::query()->firstOrCreate(
            ['code' => 'UMUM'],
            [
                'queue_pool_id' => $umumPool->id,
                'name' => 'Layanan Umum',
                'slug' => 'layanan-umum',
                'description' => 'Layanan umum mencakup Pendaftaran, Informasi/Pengaduan, Pengambilan Produk Hukum, dan eCourt.',
                'requirements' => 'Dokumen identitas dan berkas permohonan.',
                'is_active' => true,
                'booking_enabled' => true,
                'walk_in_enabled' => true,
                'daily_quota' => 200,
                'sort_order' => 1,
                'letter_code' => 'A',
            ]
        );

        Service::query()->firstOrCreate(
            ['code' => 'BYR'],
            [
                'queue_pool_id' => $bayarPool->id,
                'name' => 'Pembayaran',
                'slug' => 'pembayaran',
                'description' => 'Layanan pembayaran biaya perkara.',
                'requirements' => 'Bawa rincian tagihan atau informasi perkara.',
                'is_active' => true,
                'booking_enabled' => true,
                'walk_in_enabled' => true,
                'daily_quota' => 100,
                'sort_order' => 2,
                'letter_code' => 'B',
            ]
        );

        Service::query()->firstOrCreate(
            ['code' => 'POSBAKUM'],
            [
                'queue_pool_id' => $posbakumPool->id,
                'name' => 'Posbakum',
                'slug' => 'posbakum',
                'description' => 'Layanan pos bantuan hukum.',
                'requirements' => 'Sampaikan kebutuhan konsultasi bantuan hukum.',
                'is_active' => true,
                'booking_enabled' => true,
                'walk_in_enabled' => true,
                'daily_quota' => 80,
                'sort_order' => 3,
                'letter_code' => 'C',
            ]
        );

        // --- Counters (6 loket fisik: Pendaftaran, Informasi & Pengaduan, Produk Hukum, eCourt = pool UMUM, Pembayaran & Posbakum = fixed) --

        Counter::query()->firstOrCreate(
            ['code' => 'PENDAFTARAN'],
            ['queue_pool_id' => $umumPool->id, 'name' => 'Loket Pendaftaran', 'is_active' => true, 'is_fixed' => false, 'sort_order' => 1]
        );

        Counter::query()->firstOrCreate(
            ['code' => 'INFORMASI'],
            ['queue_pool_id' => $umumPool->id, 'name' => 'Loket Informasi & Pengaduan', 'is_active' => true, 'is_fixed' => false, 'sort_order' => 2]
        );

        Counter::query()->firstOrCreate(
            ['code' => 'PRODUK_HUKUM'],
            ['queue_pool_id' => $umumPool->id, 'name' => 'Loket Pengambilan Produk Hukum', 'is_active' => true, 'is_fixed' => false, 'sort_order' => 3]
        );

        Counter::query()->firstOrCreate(
            ['code' => 'ECOURT'],
            ['queue_pool_id' => $umumPool->id, 'name' => 'Loket eCourt', 'is_active' => true, 'is_fixed' => false, 'sort_order' => 4]
        );

        Counter::query()->firstOrCreate(
            ['code' => 'BYR'],
            ['queue_pool_id' => $bayarPool->id, 'name' => 'Loket Pembayaran', 'is_active' => true, 'is_fixed' => true, 'sort_order' => 5]
        );

        Counter::query()->firstOrCreate(
            ['code' => 'PBK'],
            ['queue_pool_id' => $posbakumPool->id, 'name' => 'Loket Posbakum', 'is_active' => true, 'is_fixed' => true, 'sort_order' => 6]
        );
    }
}
