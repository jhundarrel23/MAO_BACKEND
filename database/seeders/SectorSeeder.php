<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sectors = [
            [
                'sector_name' => 'Farmer',
                'description' => 'Rice and corn farmers',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'sector_name' => 'Farmworker',
                'description' => 'Agricultural workers and laborers',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'sector_name' => 'Fisherfolk',
                'description' => 'Fishermen and aquaculture practitioners',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'sector_name' => 'Agri-Youth',
                'description' => 'Young farmers and agricultural entrepreneurs',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('sector')->insert($sectors);
    }
}