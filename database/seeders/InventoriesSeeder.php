<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inventories = [
            // Seeds
            [
                'item_name' => 'Hybrid Rice Seeds - NSIC Rc222',
                'unit' => 'bags',
                'item_type' => 'seed',
                'assistance_category' => 'physical',
                'is_trackable_stock' => true,
                'unit_value' => 500.00,
                'description' => 'High-yielding hybrid rice variety suitable for irrigated lowland',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'item_name' => 'Hybrid Corn Seeds - Pioneer 30G12',
                'unit' => 'bags',
                'item_type' => 'seed',
                'assistance_category' => 'physical',
                'is_trackable_stock' => true,
                'unit_value' => 3500.00,
                'description' => 'White corn hybrid seeds for upland farming',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'item_name' => 'Vegetable Seeds Mix',
                'unit' => 'packets',
                'item_type' => 'seed',
                'assistance_category' => 'physical',
                'is_trackable_stock' => true,
                'unit_value' => 50.00,
                'description' => 'Mixed vegetable seeds for backyard gardening',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Fertilizers
            [
                'item_name' => '14-14-14 Complete Fertilizer',
                'unit' => 'bags',
                'item_type' => 'fertilizer',
                'assistance_category' => 'physical',
                'is_trackable_stock' => true,
                'unit_value' => 1200.00,
                'description' => 'Complete fertilizer with balanced NPK for general crop use',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'item_name' => 'Urea Fertilizer (46-0-0)',
                'unit' => 'bags',
                'item_type' => 'fertilizer',
                'assistance_category' => 'physical',
                'is_trackable_stock' => true,
                'unit_value' => 1400.00,
                'description' => 'High nitrogen fertilizer for vegetative growth',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'item_name' => 'Organic Fertilizer',
                'unit' => 'bags',
                'item_type' => 'fertilizer',
                'assistance_category' => 'physical',
                'is_trackable_stock' => true,
                'unit_value' => 300.00,
                'description' => 'Organic compost fertilizer for sustainable farming',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Equipment and Tools
            [
                'item_name' => 'Hand Tractor',
                'unit' => 'units',
                'item_type' => 'equipment',
                'assistance_category' => 'physical',
                'is_trackable_stock' => true,
                'unit_value' => 120000.00,
                'description' => '8HP hand tractor for land preparation',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'item_name' => 'Water Pump',
                'unit' => 'units',
                'item_type' => 'equipment',
                'assistance_category' => 'physical',
                'is_trackable_stock' => true,
                'unit_value' => 15000.00,
                'description' => '3-inch water pump for irrigation',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Fuel Assistance
            [
                'item_name' => 'Diesel Fuel Subsidy',
                'unit' => 'liters',
                'item_type' => 'fuel',
                'assistance_category' => 'monetary',
                'is_trackable_stock' => false,
                'unit_value' => 55.00,
                'description' => 'Diesel fuel subsidy for farm machinery operation',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'item_name' => 'Gasoline Fuel Subsidy',
                'unit' => 'liters',
                'item_type' => 'fuel',
                'assistance_category' => 'monetary',
                'is_trackable_stock' => false,
                'unit_value' => 65.00,
                'description' => 'Gasoline fuel subsidy for farm operations',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Cash Assistance
            [
                'item_name' => 'Cash Assistance for Farming',
                'unit' => 'PHP',
                'item_type' => 'cash',
                'assistance_category' => 'monetary',
                'is_trackable_stock' => false,
                'unit_value' => 1.00,
                'description' => 'Direct cash assistance for farming expenses',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'item_name' => 'Emergency Cash Assistance',
                'unit' => 'PHP',
                'item_type' => 'cash',
                'assistance_category' => 'monetary',
                'is_trackable_stock' => false,
                'unit_value' => 1.00,
                'description' => 'Emergency cash assistance for disaster-affected farmers',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('inventories')->insert($inventories);
    }
}