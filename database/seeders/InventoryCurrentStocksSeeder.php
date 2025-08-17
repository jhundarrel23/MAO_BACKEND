<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryCurrentStocksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentStocks = [
            [
                'inventory_id' => 1, // Hybrid Rice Seeds
                'current_quantity' => 500.00,
                'reserved_quantity' => 0.00,
                'available_quantity' => 500.00,
                'average_unit_cost' => 500.00,
                'total_stock_value' => 250000.00,
                'minimum_stock_level' => 50.00,
                'maximum_stock_level' => 1000.00,
                'is_low_stock' => false,
                'is_out_of_stock' => false,
                'last_stock_in' => now()->subDays(20),
                'last_stock_out' => null,
                'last_updated' => now(),
                'primary_location' => 'Main Warehouse - Section A',
                'batch_details' => json_encode([
                    ['batch' => 'RICE-2025-001', 'quantity' => 500, 'expiry' => '2026-01-10']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'inventory_id' => 2, // Hybrid Corn Seeds
                'current_quantity' => 200.00,
                'reserved_quantity' => 0.00,
                'available_quantity' => 200.00,
                'average_unit_cost' => 3500.00,
                'total_stock_value' => 700000.00,
                'minimum_stock_level' => 20.00,
                'maximum_stock_level' => 500.00,
                'is_low_stock' => false,
                'is_out_of_stock' => false,
                'last_stock_in' => now()->subDays(15),
                'last_stock_out' => null,
                'last_updated' => now(),
                'primary_location' => 'Main Warehouse - Section B',
                'batch_details' => json_encode([
                    ['batch' => 'CORN-2025-001', 'quantity' => 200, 'expiry' => '2026-01-15']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'inventory_id' => 3, // Vegetable Seeds Mix
                'current_quantity' => 1000.00,
                'reserved_quantity' => 0.00,
                'available_quantity' => 1000.00,
                'average_unit_cost' => 50.00,
                'total_stock_value' => 50000.00,
                'minimum_stock_level' => 100.00,
                'maximum_stock_level' => 2000.00,
                'is_low_stock' => false,
                'is_out_of_stock' => false,
                'last_stock_in' => now()->subDays(10),
                'last_stock_out' => null,
                'last_updated' => now(),
                'primary_location' => 'Main Warehouse - Section C',
                'batch_details' => json_encode([
                    ['batch' => 'VEG-2025-001', 'quantity' => 1000, 'expiry' => '2025-12-31']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'inventory_id' => 4, // 14-14-14 Complete Fertilizer
                'current_quantity' => 800.00,
                'reserved_quantity' => 0.00,
                'available_quantity' => 800.00,
                'average_unit_cost' => 1200.00,
                'total_stock_value' => 960000.00,
                'minimum_stock_level' => 100.00,
                'maximum_stock_level' => 1500.00,
                'is_low_stock' => false,
                'is_out_of_stock' => false,
                'last_stock_in' => now()->subDays(18),
                'last_stock_out' => null,
                'last_updated' => now(),
                'primary_location' => 'Fertilizer Warehouse - Section A',
                'batch_details' => json_encode([
                    ['batch' => 'FERT-2025-001', 'quantity' => 800, 'expiry' => '2027-01-12']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'inventory_id' => 5, // Urea Fertilizer
                'current_quantity' => 600.00,
                'reserved_quantity' => 0.00,
                'available_quantity' => 600.00,
                'average_unit_cost' => 1400.00,
                'total_stock_value' => 840000.00,
                'minimum_stock_level' => 80.00,
                'maximum_stock_level' => 1200.00,
                'is_low_stock' => false,
                'is_out_of_stock' => false,
                'last_stock_in' => now()->subDays(12),
                'last_stock_out' => null,
                'last_updated' => now(),
                'primary_location' => 'Fertilizer Warehouse - Section B',
                'batch_details' => json_encode([
                    ['batch' => 'UREA-2025-001', 'quantity' => 600, 'expiry' => '2027-01-18']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'inventory_id' => 6, // Organic Fertilizer
                'current_quantity' => 400.00,
                'reserved_quantity' => 0.00,
                'available_quantity' => 400.00,
                'average_unit_cost' => 300.00,
                'total_stock_value' => 120000.00,
                'minimum_stock_level' => 50.00,
                'maximum_stock_level' => 800.00,
                'is_low_stock' => false,
                'is_out_of_stock' => false,
                'last_stock_in' => now()->subDays(5),
                'last_stock_out' => null,
                'last_updated' => now(),
                'primary_location' => 'Organic Storage Area',
                'batch_details' => json_encode([
                    ['batch' => 'ORG-2025-001', 'quantity' => 400, 'expiry' => '2026-01-25']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'inventory_id' => 7, // Hand Tractor
                'current_quantity' => 5.00,
                'reserved_quantity' => 0.00,
                'available_quantity' => 5.00,
                'average_unit_cost' => 120000.00,
                'total_stock_value' => 600000.00,
                'minimum_stock_level' => 1.00,
                'maximum_stock_level' => 10.00,
                'is_low_stock' => false,
                'is_out_of_stock' => false,
                'last_stock_in' => now()->subDays(22),
                'last_stock_out' => null,
                'last_updated' => now(),
                'primary_location' => 'Equipment Storage Yard',
                'batch_details' => json_encode([
                    ['batch' => 'HT-2025-001', 'quantity' => 5, 'expiry' => null]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'inventory_id' => 8, // Water Pump
                'current_quantity' => 10.00,
                'reserved_quantity' => 0.00,
                'available_quantity' => 10.00,
                'average_unit_cost' => 15000.00,
                'total_stock_value' => 150000.00,
                'minimum_stock_level' => 2.00,
                'maximum_stock_level' => 20.00,
                'is_low_stock' => false,
                'is_out_of_stock' => false,
                'last_stock_in' => now()->subDays(8),
                'last_stock_out' => null,
                'last_updated' => now(),
                'primary_location' => 'Equipment Storage Yard',
                'batch_details' => json_encode([
                    ['batch' => 'WP-2025-001', 'quantity' => 10, 'expiry' => null]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('inventory_current_stocks')->insert($currentStocks);
    }
}