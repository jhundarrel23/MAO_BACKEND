<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistributionGuidelinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guidelines = [
            [
                'guideline_name' => 'Rice Seed Distribution Guidelines',
                'item_type' => 'seed',
                'description' => 'Guidelines for distributing rice seeds to farmers based on farm area and needs.',
                'suggested_amount_per_hectare' => 2.00, // 2 bags per hectare
                'minimum_amount' => 1.00,
                'maximum_amount' => 10.00,
                'unit' => 'bags',
                'considerations' => 'Consider farm area, soil type, farming experience, household size, and previous assistance received. Senior citizens and solo parents may need additional support.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            [
                'guideline_name' => 'Fertilizer Distribution Guidelines',
                'item_type' => 'fertilizer',
                'description' => 'Guidelines for distributing fertilizer to support crop production.',
                'suggested_amount_per_hectare' => 3.00, // 3 bags per hectare
                'minimum_amount' => 2.00,
                'maximum_amount' => 15.00,
                'unit' => 'bags',
                'considerations' => 'Consider farm area, soil fertility, crop type, and organic farming practices. Organic farmers may need less chemical fertilizer.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            [
                'guideline_name' => 'Cash Assistance Guidelines',
                'item_type' => 'cash',
                'description' => 'Guidelines for providing cash assistance to farming families.',
                'suggested_amount_per_hectare' => null, // Not hectare-based
                'minimum_amount' => 2000.00,
                'maximum_amount' => 15000.00,
                'unit' => 'PHP',
                'considerations' => 'Consider household size, income level, vulnerability status (senior citizens, solo parents, disabled members), and urgent needs. Prioritize families with very low income.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            [
                'guideline_name' => 'Fuel Assistance Guidelines',
                'item_type' => 'fuel',
                'description' => 'Guidelines for fuel subsidy distribution to support farm operations.',
                'suggested_amount_per_hectare' => 200.00, // 200 liters per hectare
                'minimum_amount' => 100.00,
                'maximum_amount' => 1000.00,
                'unit' => 'liters',
                'considerations' => 'Consider farm area, equipment ownership, farming intensity, and transportation needs. Farmers with their own equipment may need more fuel.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('distribution_guidelines')->insert($guidelines);
    }
}