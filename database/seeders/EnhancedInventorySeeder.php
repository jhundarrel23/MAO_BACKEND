<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\InventoryDisbursementService;

class EnhancedInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Enhanced inventory items with proper codes and categories
        $inventoryItems = [
            // SEEDS CATEGORY
            [
                'item_code' => 'SEED-RICE-001',
                'item_name' => 'Hybrid Rice Seed PSB Rc18',
                'description' => 'High-yielding hybrid rice variety suitable for wet season',
                'unit' => 'bags',
                'category' => 'Seeds',
                'item_type' => 'seeds',
                'unit_cost' => 1200.00,
                'minimum_stock_level' => 50,
                'maximum_stock_level' => 500,
                'reorder_point' => 100,
                'is_subsidizable' => true,
                'storage_location' => 'Warehouse A - Section 1',
                'initial_stock' => 200
            ],
            [
                'item_code' => 'SEED-CORN-001',
                'item_name' => 'Hybrid Corn Seed Pioneer 30G12',
                'description' => 'High-yielding yellow corn variety for both wet and dry season',
                'unit' => 'bags',
                'category' => 'Seeds',
                'item_type' => 'seeds',
                'unit_cost' => 2500.00,
                'minimum_stock_level' => 30,
                'maximum_stock_level' => 300,
                'reorder_point' => 60,
                'is_subsidizable' => true,
                'storage_location' => 'Warehouse A - Section 1',
                'initial_stock' => 150
            ],
            [
                'item_code' => 'SEED-VEG-001',
                'item_name' => 'Vegetable Seeds Assorted Pack',
                'description' => 'Mixed vegetable seeds: tomato, eggplant, okra, cabbage',
                'unit' => 'packets',
                'category' => 'Seeds',
                'item_type' => 'seeds',
                'unit_cost' => 25.00,
                'minimum_stock_level' => 200,
                'maximum_stock_level' => 1000,
                'reorder_point' => 400,
                'is_subsidizable' => true,
                'storage_location' => 'Warehouse A - Section 2',
                'initial_stock' => 500
            ],

            // FERTILIZERS CATEGORY
            [
                'item_code' => 'FERT-COMP-001',
                'item_name' => 'Complete Fertilizer 14-14-14',
                'description' => 'Balanced NPK fertilizer suitable for all crops',
                'unit' => 'bags',
                'category' => 'Fertilizers',
                'item_type' => 'fertilizer',
                'unit_cost' => 1850.00,
                'minimum_stock_level' => 100,
                'maximum_stock_level' => 1000,
                'reorder_point' => 200,
                'is_subsidizable' => true,
                'storage_location' => 'Warehouse B - Section 1',
                'initial_stock' => 300
            ],
            [
                'item_code' => 'FERT-UREA-001',
                'item_name' => 'Urea 46-0-0',
                'description' => 'High nitrogen fertilizer for vegetative growth',
                'unit' => 'bags',
                'category' => 'Fertilizers',
                'item_type' => 'fertilizer',
                'unit_cost' => 1650.00,
                'minimum_stock_level' => 80,
                'maximum_stock_level' => 800,
                'reorder_point' => 160,
                'is_subsidizable' => true,
                'storage_location' => 'Warehouse B - Section 1',
                'initial_stock' => 250
            ],
            [
                'item_code' => 'FERT-ORG-001',
                'item_name' => 'Organic Fertilizer',
                'description' => 'Composted organic matter for soil improvement',
                'unit' => 'bags',
                'category' => 'Fertilizers',
                'item_type' => 'fertilizer',
                'unit_cost' => 450.00,
                'minimum_stock_level' => 50,
                'maximum_stock_level' => 500,
                'reorder_point' => 100,
                'is_subsidizable' => true,
                'storage_location' => 'Warehouse B - Section 2',
                'initial_stock' => 180
            ],

            // FARM TOOLS CATEGORY
            [
                'item_code' => 'TOOL-BOLO-001',
                'item_name' => 'Bolo/Machete',
                'description' => 'Sharp cutting tool for farm work',
                'unit' => 'pieces',
                'category' => 'Farm Tools',
                'item_type' => 'tools',
                'unit_cost' => 350.00,
                'minimum_stock_level' => 20,
                'maximum_stock_level' => 200,
                'reorder_point' => 40,
                'is_subsidizable' => true,
                'storage_location' => 'Warehouse C - Tools Section',
                'initial_stock' => 100
            ],
            [
                'item_code' => 'TOOL-HOE-001',
                'item_name' => 'Garden Hoe',
                'description' => 'Cultivating tool for soil preparation',
                'unit' => 'pieces',
                'category' => 'Farm Tools',
                'item_type' => 'tools',
                'unit_cost' => 450.00,
                'minimum_stock_level' => 15,
                'maximum_stock_level' => 150,
                'reorder_point' => 30,
                'is_subsidizable' => true,
                'storage_location' => 'Warehouse C - Tools Section',
                'initial_stock' => 75
            ],
            [
                'item_code' => 'TOOL-SHOVEL-001',
                'item_name' => 'Long Handle Shovel',
                'description' => 'Digging and soil moving tool',
                'unit' => 'pieces',
                'category' => 'Farm Tools',
                'item_type' => 'tools',
                'unit_cost' => 650.00,
                'minimum_stock_level' => 10,
                'maximum_stock_level' => 100,
                'reorder_point' => 20,
                'is_subsidizable' => true,
                'storage_location' => 'Warehouse C - Tools Section',
                'initial_stock' => 50
            ],

            // LIVESTOCK CATEGORY
            [
                'item_code' => 'LIVE-GOAT-001',
                'item_name' => 'Goat (Native Breed)',
                'description' => 'Native goat for breeding and meat production',
                'unit' => 'heads',
                'category' => 'Livestock',
                'item_type' => 'livestock',
                'unit_cost' => 8500.00,
                'minimum_stock_level' => 5,
                'maximum_stock_level' => 50,
                'reorder_point' => 10,
                'is_subsidizable' => true,
                'storage_location' => 'Livestock Area',
                'initial_stock' => 25
            ],
            [
                'item_code' => 'LIVE-PIGLET-001',
                'item_name' => 'Piglets (Improved Breed)',
                'description' => 'Young pigs for fattening program',
                'unit' => 'heads',
                'category' => 'Livestock',
                'item_type' => 'livestock',
                'unit_cost' => 3500.00,
                'minimum_stock_level' => 10,
                'maximum_stock_level' => 100,
                'reorder_point' => 20,
                'is_subsidizable' => true,
                'storage_location' => 'Livestock Area',
                'initial_stock' => 40
            ],
            [
                'item_code' => 'LIVE-CHICK-001',
                'item_name' => 'Native Chicken',
                'description' => 'Native chicken for backyard raising',
                'unit' => 'heads',
                'category' => 'Livestock',
                'item_type' => 'livestock',
                'unit_cost' => 150.00,
                'minimum_stock_level' => 50,
                'maximum_stock_level' => 500,
                'reorder_point' => 100,
                'is_subsidizable' => true,
                'storage_location' => 'Poultry Area',
                'initial_stock' => 200
            ],

            // EQUIPMENT CATEGORY (Not subsidizable - for office use)
            [
                'item_code' => 'EQUIP-PUMP-001',
                'item_name' => 'Water Pump 2HP',
                'description' => 'Water pump for irrigation system',
                'unit' => 'units',
                'category' => 'Equipment',
                'item_type' => 'equipment',
                'unit_cost' => 15000.00,
                'minimum_stock_level' => 2,
                'maximum_stock_level' => 10,
                'reorder_point' => 3,
                'is_subsidizable' => false, // Not for subsidy
                'storage_location' => 'Equipment Storage',
                'initial_stock' => 5
            ]
        ];

        // Insert inventory items and create initial stock records
        foreach ($inventoryItems as $item) {
            $initialStock = $item['initial_stock'];
            unset($item['initial_stock']);

            // Insert inventory item
            $inventoryId = DB::table('inventories')->insertGetId(array_merge($item, [
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]));

            // Create current stock record
            DB::table('inventory_current_stocks')->insert([
                'inventory_id' => $inventoryId,
                'current_stock' => $initialStock,
                'reserved_stock' => 0,
                'available_stock' => $initialStock,
                'total_value' => $initialStock * $item['unit_cost'],
                'last_movement_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Create initial stock movement record
            DB::table('inventory_movements')->insert([
                'inventory_id' => $inventoryId,
                'movement_type' => 'stock_in',
                'quantity' => $initialStock,
                'balance_after' => $initialStock,
                'unit_cost' => $item['unit_cost'],
                'total_cost' => $initialStock * $item['unit_cost'],
                'reference_type' => 'initial_stock',
                'reference_number' => 'INIT-' . date('Y'),
                'remarks' => 'Initial stock setup',
                'processed_by' => 1, // Assume admin user ID = 1
                'movement_date' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Enhanced inventory system seeded successfully!');
        $this->command->info('Created ' . count($inventoryItems) . ' inventory items with initial stock levels.');
    }
}