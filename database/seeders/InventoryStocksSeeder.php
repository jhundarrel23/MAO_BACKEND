<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryStocksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stocks = [
            // Rice Seeds Stock In
            [
                'inventory_id' => 1, // Hybrid Rice Seeds
                'quantity' => 500,
                'movement_type' => 'stock_in',
                'transaction_type' => 'donation',
                'reference' => 'DR-2025-001',
                'source' => 'DA Region X',
                'date_received' => '2025-01-10',
                'transaction_date' => '2025-01-10',
                'unit_cost' => 500.00,
                'total_value' => 250000.00,
                'running_balance' => 500.00,
                'batch_number' => 'RICE-2025-001',
                'expiry_date' => '2026-01-10',
                'is_verified' => true,
                'verified_by' => 1,
                'status' => 'completed',
                'approved_at' => now()->subDays(20),
                'created_at' => now()->subDays(20),
                'updated_at' => now()->subDays(20)
            ],
            
            // Corn Seeds Stock In
            [
                'inventory_id' => 2, // Hybrid Corn Seeds
                'quantity' => 200,
                'movement_type' => 'stock_in',
                'transaction_type' => 'purchase',
                'reference' => 'PO-2025-001',
                'source' => 'Pioneer Seeds Philippines',
                'date_received' => '2025-01-15',
                'transaction_date' => '2025-01-15',
                'unit_cost' => 3500.00,
                'total_value' => 700000.00,
                'running_balance' => 200.00,
                'batch_number' => 'CORN-2025-001',
                'expiry_date' => '2026-01-15',
                'is_verified' => true,
                'verified_by' => 1,
                'status' => 'completed',
                'approved_at' => now()->subDays(15),
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(15)
            ],
            
            // Vegetable Seeds Stock In
            [
                'inventory_id' => 3, // Vegetable Seeds Mix
                'quantity' => 1000,
                'movement_type' => 'stock_in',
                'transaction_type' => 'donation',
                'reference' => 'DR-2025-002',
                'source' => 'LGU Opol',
                'date_received' => '2025-01-20',
                'transaction_date' => '2025-01-20',
                'unit_cost' => 50.00,
                'total_value' => 50000.00,
                'running_balance' => 1000.00,
                'batch_number' => 'VEG-2025-001',
                'expiry_date' => '2025-12-31',
                'is_verified' => true,
                'verified_by' => 2,
                'status' => 'completed',
                'approved_at' => now()->subDays(10),
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10)
            ],
            
            // Complete Fertilizer Stock In
            [
                'inventory_id' => 4, // 14-14-14 Complete Fertilizer
                'quantity' => 800,
                'movement_type' => 'stock_in',
                'transaction_type' => 'donation',
                'reference' => 'DR-2025-003',
                'source' => 'DA Region X',
                'date_received' => '2025-01-12',
                'transaction_date' => '2025-01-12',
                'unit_cost' => 1200.00,
                'total_value' => 960000.00,
                'running_balance' => 800.00,
                'batch_number' => 'FERT-2025-001',
                'expiry_date' => '2027-01-12',
                'is_verified' => true,
                'verified_by' => 1,
                'status' => 'completed',
                'approved_at' => now()->subDays(18),
                'created_at' => now()->subDays(18),
                'updated_at' => now()->subDays(18)
            ],
            
            // Urea Fertilizer Stock In
            [
                'inventory_id' => 5, // Urea Fertilizer
                'quantity' => 600,
                'movement_type' => 'stock_in',
                'transaction_type' => 'purchase',
                'reference' => 'PO-2025-002',
                'source' => 'Philphos Corporation',
                'date_received' => '2025-01-18',
                'transaction_date' => '2025-01-18',
                'unit_cost' => 1400.00,
                'total_value' => 840000.00,
                'running_balance' => 600.00,
                'batch_number' => 'UREA-2025-001',
                'expiry_date' => '2027-01-18',
                'is_verified' => true,
                'verified_by' => 1,
                'status' => 'completed',
                'approved_at' => now()->subDays(12),
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(12)
            ],
            
            // Organic Fertilizer Stock In
            [
                'inventory_id' => 6, // Organic Fertilizer
                'quantity' => 400,
                'movement_type' => 'stock_in',
                'transaction_type' => 'donation',
                'reference' => 'DR-2025-004',
                'source' => 'Opol Composting Facility',
                'date_received' => '2025-01-25',
                'transaction_date' => '2025-01-25',
                'unit_cost' => 300.00,
                'total_value' => 120000.00,
                'running_balance' => 400.00,
                'batch_number' => 'ORG-2025-001',
                'expiry_date' => '2026-01-25',
                'is_verified' => true,
                'verified_by' => 2,
                'status' => 'completed',
                'approved_at' => now()->subDays(5),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5)
            ],
            
            // Hand Tractor Stock In
            [
                'inventory_id' => 7, // Hand Tractor
                'quantity' => 5,
                'movement_type' => 'stock_in',
                'transaction_type' => 'purchase',
                'reference' => 'PO-2025-003',
                'source' => 'Kubota Philippines',
                'date_received' => '2025-01-08',
                'transaction_date' => '2025-01-08',
                'unit_cost' => 120000.00,
                'total_value' => 600000.00,
                'running_balance' => 5.00,
                'batch_number' => 'HT-2025-001',
                'is_verified' => true,
                'verified_by' => 1,
                'status' => 'completed',
                'approved_at' => now()->subDays(22),
                'created_at' => now()->subDays(22),
                'updated_at' => now()->subDays(22)
            ],
            
            // Water Pump Stock In
            [
                'inventory_id' => 8, // Water Pump
                'quantity' => 10,
                'movement_type' => 'stock_in',
                'transaction_type' => 'donation',
                'reference' => 'DR-2025-005',
                'source' => 'PCIC Misamis Oriental',
                'date_received' => '2025-01-22',
                'transaction_date' => '2025-01-22',
                'unit_cost' => 15000.00,
                'total_value' => 150000.00,
                'running_balance' => 10.00,
                'batch_number' => 'WP-2025-001',
                'is_verified' => true,
                'verified_by' => 2,
                'status' => 'completed',
                'approved_at' => now()->subDays(8),
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(8)
            ]
        ];

        DB::table('inventory_stocks')->insert($stocks);
    }
}