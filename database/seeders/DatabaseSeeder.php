<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in proper order to respect foreign key constraints
        $this->call([
            // Basic reference data
            UsersSeeder::class,
            SectorSeeder::class,
            CommodityCategoriesSeeder::class,
            CommoditiesSeeder::class,
            BarangaysSeeder::class,
            
            // Beneficiary data
            BeneficiaryProfilesSeeder::class,
            
            // Program data
            SubsidyProgramsSeeder::class,
            
            // Inventory system
            InventoriesSeeder::class,
            InventoryStocksSeeder::class,
            InventoryCurrentStocksSeeder::class,
            
            // Distribution guidelines
            DistributionGuidelinesSeeder::class,
        ]);
    }
}
