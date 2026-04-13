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
        Schema::table('queue_pools', function (Blueprint $table) {
            $table->string('letter_code', 5)->nullable()->after('code');
        });

        // Set letter_code berdasarkan data pool yang ada agar konsisten dengan Service lama
        DB::table('queue_pools')->update([
            'letter_code' => DB::raw("CASE
                WHEN code = 'UMUM' THEN 'A'
                WHEN code = 'BAYAR' THEN 'D'
                WHEN code = 'POSBAKUM' THEN 'E'
                WHEN code = 'PCQ60' THEN 'F'
                ELSE 'X'
            END"),
        ]);

        if (Schema::hasColumn('services', 'letter_code')) {
            // Cek apakah unique index ada sebelum drop (kompatibel MariaDB)
            $indexExists = DB::table('information_schema.statistics')
                ->where('table_schema', DB::connection()->getDatabaseName())
                ->where('table_name', 'services')
                ->where('index_name', 'services_letter_code_unique')
                ->exists();

            Schema::table('services', function (Blueprint $table) use ($indexExists) {
                if ($indexExists) {
                    $table->dropUnique('services_letter_code_unique');
                }
                $table->dropColumn('letter_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_pools', function (Blueprint $table) {
            $table->dropColumn('letter_code');
        });

        if (! Schema::hasColumn('services', 'letter_code')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('letter_code', 5)->nullable()->after('code');
            });

            DB::table('services')->update([
                'letter_code' => DB::raw("CASE
                    WHEN queue_pool_id = 1 THEN 'A'
                    WHEN queue_pool_id = 2 THEN 'D'
                    WHEN queue_pool_id = 3 THEN 'E'
                    WHEN queue_pool_id = 4 THEN 'F'
                    ELSE 'X'
                END"),
            ]);
        }
    }
};
