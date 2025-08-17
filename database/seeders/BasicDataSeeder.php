<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BasicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Seed Barangays in Opol, Misamis Oriental
        $barangays = [
            'Awang',
            'Barra',
            'Bonbon',
            'Bulak',
            'Bunga',
            'Igpit',
            'Lower Loboc',
            'Lumanglas',
            'Malanang',
            'Nasipit',
            'Patag',
            'Poblacion',
            'Pook',
            'Puntod',
            'Sangay Daku',
            'Sangay Gamay',
            'Simunul',
            'Sinaman',
            'Taboc',
            'Tingalan',
            'Tomi',
            'Tupang Bato',
            'Upper Loboc',
            'Villaflor'
        ];

        foreach ($barangays as $barangay) {
            DB::table('barangays')->insertOrIgnore([
                'barangay_name' => $barangay,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Seed Agricultural Sectors
        $sectors = [
            'Rice Production',
            'Corn Production',
            'High Value Crops',
            'Livestock',
            'Poultry',
            'Fisheries',
            'Agricultural Extension',
            'Agricultural Mechanization',
            'Organic Agriculture',
            'Agricultural Processing'
        ];

        foreach ($sectors as $sector) {
            DB::table('sector')->insertOrIgnore([
                'sector_name' => $sector,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Seed Commodity Categories
        $commodityCategories = [
            'Cereals',
            'Root Crops',
            'Legumes',
            'Vegetables',
            'Fruits',
            'Spices and Condiments',
            'Industrial Crops',
            'Livestock',
            'Poultry',
            'Aquaculture'
        ];

        foreach ($commodityCategories as $category) {
            DB::table('commodity_categories')->insertOrIgnore([
                'category_name' => $category,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Seed Commodities
        $commodities = [
            // Cereals
            ['commodity_name' => 'Rice', 'category_name' => 'Cereals', 'sector_name' => 'Rice Production'],
            ['commodity_name' => 'Corn', 'category_name' => 'Cereals', 'sector_name' => 'Corn Production'],
            
            // Root Crops
            ['commodity_name' => 'Sweet Potato', 'category_name' => 'Root Crops', 'sector_name' => 'High Value Crops'],
            ['commodity_name' => 'Cassava', 'category_name' => 'Root Crops', 'sector_name' => 'High Value Crops'],
            ['commodity_name' => 'Taro', 'category_name' => 'Root Crops', 'sector_name' => 'High Value Crops'],
            
            // Vegetables
            ['commodity_name' => 'Tomato', 'category_name' => 'Vegetables', 'sector_name' => 'High Value Crops'],
            ['commodity_name' => 'Eggplant', 'category_name' => 'Vegetables', 'sector_name' => 'High Value Crops'],
            ['commodity_name' => 'Okra', 'category_name' => 'Vegetables', 'sector_name' => 'High Value Crops'],
            ['commodity_name' => 'Cabbage', 'category_name' => 'Vegetables', 'sector_name' => 'High Value Crops'],
            ['commodity_name' => 'Lettuce', 'category_name' => 'Vegetables', 'sector_name' => 'High Value Crops'],
            
            // Fruits
            ['commodity_name' => 'Banana', 'category_name' => 'Fruits', 'sector_name' => 'High Value Crops'],
            ['commodity_name' => 'Mango', 'category_name' => 'Fruits', 'sector_name' => 'High Value Crops'],
            ['commodity_name' => 'Coconut', 'category_name' => 'Fruits', 'sector_name' => 'High Value Crops'],
            ['commodity_name' => 'Papaya', 'category_name' => 'Fruits', 'sector_name' => 'High Value Crops'],
            
            // Livestock
            ['commodity_name' => 'Cattle', 'category_name' => 'Livestock', 'sector_name' => 'Livestock'],
            ['commodity_name' => 'Goat', 'category_name' => 'Livestock', 'sector_name' => 'Livestock'],
            ['commodity_name' => 'Swine', 'category_name' => 'Livestock', 'sector_name' => 'Livestock'],
            ['commodity_name' => 'Carabao', 'category_name' => 'Livestock', 'sector_name' => 'Livestock'],
            
            // Poultry
            ['commodity_name' => 'Native Chicken', 'category_name' => 'Poultry', 'sector_name' => 'Poultry'],
            ['commodity_name' => 'Broiler', 'category_name' => 'Poultry', 'sector_name' => 'Poultry'],
            ['commodity_name' => 'Layer', 'category_name' => 'Poultry', 'sector_name' => 'Poultry'],
            ['commodity_name' => 'Duck', 'category_name' => 'Poultry', 'sector_name' => 'Poultry'],
            
            // Aquaculture
            ['commodity_name' => 'Tilapia', 'category_name' => 'Aquaculture', 'sector_name' => 'Fisheries'],
            ['commodity_name' => 'Milkfish', 'category_name' => 'Aquaculture', 'sector_name' => 'Fisheries'],
            ['commodity_name' => 'Catfish', 'category_name' => 'Aquaculture', 'sector_name' => 'Fisheries'],
        ];

        foreach ($commodities as $commodity) {
            $categoryId = DB::table('commodity_categories')
                ->where('category_name', $commodity['category_name'])
                ->value('id');
            
            $sectorId = DB::table('sector')
                ->where('sector_name', $commodity['sector_name'])
                ->value('id');

            if ($categoryId && $sectorId) {
                DB::table('commodities')->insertOrIgnore([
                    'commodity_name' => $commodity['commodity_name'],
                    'category_id' => $categoryId,
                    'sector_id' => $sectorId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Seed Livelihood Categories
        $livelihoodCategories = [
            'Farmer',
            'Farm Worker',
            'Fisherfolk',
            'Agri-Youth'
        ];

        foreach ($livelihoodCategories as $category) {
            DB::table('livelihood_categories')->insertOrIgnore([
                'livelihood_category_name' => $category,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Seed Subsidy Categories
        $subsidyCategories = [
            'Seeds',
            'Fertilizers',
            'Pesticides',
            'Farm Tools',
            'Agricultural Machinery',
            'Fuel Subsidy',
            'Training and Capacity Building',
            'Marketing Support',
            'Financial Assistance',
            'Farm Infrastructure'
        ];

        foreach ($subsidyCategories as $category) {
            DB::table('subsidy_categories')->insertOrIgnore([
                'subsidy_name' => $category,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Seed Basic Inventory Items
        $inventoryItems = [
            // Seeds
            ['item_name' => 'Hybrid Rice Seed', 'unit' => 'bags', 'item_type' => 'seeds'],
            ['item_name' => 'Hybrid Corn Seed', 'unit' => 'bags', 'item_type' => 'seeds'],
            ['item_name' => 'Vegetable Seeds Assorted', 'unit' => 'packets', 'item_type' => 'seeds'],
            
            // Fertilizers
            ['item_name' => 'Complete Fertilizer 14-14-14', 'unit' => 'bags', 'item_type' => 'fertilizer'],
            ['item_name' => 'Urea 46-0-0', 'unit' => 'bags', 'item_type' => 'fertilizer'],
            ['item_name' => 'Organic Fertilizer', 'unit' => 'bags', 'item_type' => 'fertilizer'],
            
            // Farm Tools
            ['item_name' => 'Bolo', 'unit' => 'pieces', 'item_type' => 'tools'],
            ['item_name' => 'Hoe', 'unit' => 'pieces', 'item_type' => 'tools'],
            ['item_name' => 'Shovel', 'unit' => 'pieces', 'item_type' => 'tools'],
            ['item_name' => 'Rake', 'unit' => 'pieces', 'item_type' => 'tools'],
            
            // Livestock
            ['item_name' => 'Goat (Native)', 'unit' => 'heads', 'item_type' => 'livestock'],
            ['item_name' => 'Piglets', 'unit' => 'heads', 'item_type' => 'livestock'],
            ['item_name' => 'Native Chicken', 'unit' => 'heads', 'item_type' => 'livestock'],
        ];

        foreach ($inventoryItems as $item) {
            DB::table('inventories')->insertOrIgnore([
                'item_name' => $item['item_name'],
                'unit' => $item['unit'],
                'item_type' => $item['item_type'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Basic agricultural data seeded successfully!');
    }
}