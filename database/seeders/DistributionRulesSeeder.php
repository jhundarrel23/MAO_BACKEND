<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistributionRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            // Seed distribution based on farm area
            [
                'rule_name' => 'Rice Seed Distribution by Farm Area',
                'item_type' => 'seed',
                'calculation_basis' => 'farm_area',
                'base_amount' => 2.00, // 2 bags per hectare
                'multiplier' => 1.0000,
                'minimum_amount' => 1.00,
                'maximum_amount' => 10.00,
                'area_brackets' => json_encode([
                    '0-1' => ['multiplier' => 1.0, 'base' => 2],
                    '1-3' => ['multiplier' => 1.0, 'base' => 2],
                    '3-5' => ['multiplier' => 0.9, 'base' => 2], // Slight reduction for larger farms
                    '5+' => ['multiplier' => 0.8, 'base' => 2]
                ]),
                'condition_modifiers' => json_encode([
                    'is_senior_citizen' => 1.2,
                    'is_solo_parent' => 1.15,
                    'has_disabled_member' => 1.1,
                    'disaster_vulnerability' => [
                        'high' => 1.3,
                        'moderate' => 1.1,
                        'low' => 1.0
                    ]
                ]),
                'is_active' => true,
                'priority_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Fertilizer distribution
            [
                'rule_name' => 'Fertilizer Distribution by Farm Area',
                'item_type' => 'fertilizer',
                'calculation_basis' => 'farm_area',
                'base_amount' => 3.00, // 3 bags per hectare
                'multiplier' => 1.0000,
                'minimum_amount' => 2.00,
                'maximum_amount' => 15.00,
                'area_brackets' => json_encode([
                    '0-1' => ['multiplier' => 1.0, 'base' => 3],
                    '1-3' => ['multiplier' => 1.0, 'base' => 3],
                    '3-5' => ['multiplier' => 0.9, 'base' => 3],
                    '5+' => ['multiplier' => 0.8, 'base' => 3]
                ]),
                'condition_modifiers' => json_encode([
                    'is_organic_farmer' => 0.5, // Less chemical fertilizer for organic farmers
                    'soil_fertility' => [
                        'poor' => 1.3,
                        'fair' => 1.1,
                        'good' => 1.0,
                        'excellent' => 0.8
                    ]
                ]),
                'is_active' => true,
                'priority_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Cash assistance based on income level
            [
                'rule_name' => 'Cash Assistance by Income Level',
                'item_type' => 'cash',
                'calculation_basis' => 'income_level',
                'base_amount' => 5000.00, // Base 5000 PHP
                'multiplier' => 1.0000,
                'minimum_amount' => 2000.00,
                'maximum_amount' => 15000.00,
                'income_brackets' => json_encode([
                    'very_low' => 1.8,
                    'low' => 1.4,
                    'moderate' => 1.0,
                    'high' => 0.6
                ]),
                'condition_modifiers' => json_encode([
                    'household_size' => [
                        '1-2' => 1.0,
                        '3-5' => 1.2,
                        '6-8' => 1.4,
                        '9+' => 1.6
                    ],
                    'is_solo_parent' => 1.3,
                    'has_disabled_member' => 1.2
                ]),
                'is_active' => true,
                'priority_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // Fuel assistance
            [
                'rule_name' => 'Fuel Assistance by Farm Area and Equipment',
                'item_type' => 'fuel',
                'calculation_basis' => 'farm_area',
                'base_amount' => 200.00, // 200 liters per hectare
                'multiplier' => 1.0000,
                'minimum_amount' => 100.00,
                'maximum_amount' => 1000.00,
                'area_brackets' => json_encode([
                    '0-1' => ['multiplier' => 1.0, 'base' => 200],
                    '1-3' => ['multiplier' => 1.0, 'base' => 200],
                    '3-5' => ['multiplier' => 0.9, 'base' => 200],
                    '5+' => ['multiplier' => 0.8, 'base' => 200]
                ]),
                'condition_modifiers' => json_encode([
                    'has_farm_equipment' => 1.5,
                    'farming_experience_level' => [
                        'beginner' => 0.8,
                        'intermediate' => 1.0,
                        'experienced' => 1.1,
                        'expert' => 1.2
                    ]
                ]),
                'is_active' => true,
                'priority_order' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('distribution_rules')->insert($rules);
    }
}