<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class WilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('wilayah')->exists()) {
            return;
        }

        $sqlPath = database_path('wilayah.sql');
        if (! File::exists($sqlPath)) {
            return;
        }

        $sql = File::get($sqlPath);
        preg_match_all('/INSERT INTO wilayah .*?;/s', $sql, $insertStatements);

        foreach ($insertStatements[0] as $statement) {
            DB::unprepared($statement);
        }
    }
}
