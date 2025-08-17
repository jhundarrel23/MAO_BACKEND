<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommoditiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $commodities = [
            // Cereals
            [
                'commodity_name' => 'Rice',
                'commodity_category_id' => 1,
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'commodity_name' => 'Corn',
                'commodity_category_id' => 1,
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Vegetables
            [
                'commodity_name' => 'Tomato',
                'commodity_category_id' => 2,
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'commodity_name' => 'Eggplant',
                'commodity_category_id' => 2,
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'commodity_name' => 'Cabbage',
                'commodity_category_id' => 2,
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Fruits
            [
                'commodity_name' => 'Banana',
                'commodity_category_id' => 3,
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'commodity_name' => 'Mango',
                'commodity_category_id' => 3,
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Root Crops
            [
                'commodity_name' => 'Sweet Potato',
                'commodity_category_id' => 4,
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'commodity_name' => 'Cassava',
                'commodity_category_id' => 4,
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Livestock
            [
                'commodity_name' => 'Swine',
                'commodity_category_id' => 6,
                'unit' => 'head',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'commodity_name' => 'Cattle',
                'commodity_category_id' => 6,
                'unit' => 'head',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Poultry
            [
                'commodity_name' => 'Chicken',
                'commodity_category_id' => 7,
                'unit' => 'head',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Fisheries
            [
                'commodity_name' => 'Tilapia',
                'commodity_category_id' => 8,
                'unit' => 'kg',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('commodities')->insert($commodities);
    }
}