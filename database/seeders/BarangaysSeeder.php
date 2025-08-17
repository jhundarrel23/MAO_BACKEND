<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barangays = [
            [
                'barangay_name' => 'Poblacion',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'barangay_name' => 'Awang',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'barangay_name' => 'Bonbon',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'barangay_name' => 'Bugo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'barangay_name' => 'Taboc',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'barangay_name' => 'Patag',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'barangay_name' => 'Barra',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'barangay_name' => 'Tingalan',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('barangays')->insert($barangays);
    }
}