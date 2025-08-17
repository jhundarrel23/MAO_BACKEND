<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommodityCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'category_name' => 'Cereals',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Vegetables',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Fruits',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Root Crops',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Legumes',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Livestock',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Poultry',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Fisheries',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('commodity_categories')->insert($categories);
    }
}