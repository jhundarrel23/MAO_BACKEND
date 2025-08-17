<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialSubsidySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Common financial subsidy types in agricultural programs
        $financialSubsidyTypes = [
            [
                'type_name' => 'Planting Allowance',
                'description' => 'Cash assistance for land preparation and planting activities',
                'default_amount' => 3000.00,
                'disbursement_method' => 'cash',
                'requires_receipt' => true,
                'is_active' => true
            ],
            [
                'type_name' => 'Fuel Subsidy',
                'description' => 'Fuel allowance for farm machinery and transportation',
                'default_amount' => 2000.00,
                'disbursement_method' => 'cash',
                'requires_receipt' => true,
                'is_active' => true
            ],
            [
                'type_name' => 'Cash Assistance',
                'description' => 'General cash assistance for farmers',
                'default_amount' => 5000.00,
                'disbursement_method' => 'cash',
                'requires_receipt' => true,
                'is_active' => true
            ],
            [
                'type_name' => 'Harvest Incentive',
                'description' => 'Cash incentive based on harvest yield',
                'default_amount' => 1500.00,
                'disbursement_method' => 'cash',
                'requires_receipt' => true,
                'is_active' => true
            ],
            [
                'type_name' => 'Training Allowance',
                'description' => 'Transportation and meal allowance for attending training',
                'default_amount' => 500.00,
                'disbursement_method' => 'cash',
                'requires_receipt' => false,
                'is_active' => true
            ],
            [
                'type_name' => 'Emergency Assistance',
                'description' => 'Emergency cash assistance for natural disasters or crop loss',
                'default_amount' => 10000.00,
                'disbursement_method' => 'check',
                'requires_receipt' => true,
                'is_active' => true
            ],
            [
                'type_name' => 'Organic Certification Incentive',
                'description' => 'Cash incentive for farmers pursuing organic certification',
                'default_amount' => 8000.00,
                'disbursement_method' => 'bank_transfer',
                'requires_receipt' => true,
                'is_active' => true
            ],
            [
                'type_name' => 'Youth Farmer Startup Grant',
                'description' => 'Startup capital for young farmers (18-30 years old)',
                'default_amount' => 15000.00,
                'disbursement_method' => 'bank_transfer',
                'requires_receipt' => true,
                'is_active' => true
            ],
            [
                'type_name' => 'Cooperative Membership Fee',
                'description' => 'Assistance for farmers to join agricultural cooperatives',
                'default_amount' => 1000.00,
                'disbursement_method' => 'cash',
                'requires_receipt' => true,
                'is_active' => true
            ],
            [
                'type_name' => 'Farm Insurance Premium',
                'description' => 'Assistance for crop insurance premium payments',
                'default_amount' => 2500.00,
                'disbursement_method' => 'check',
                'requires_receipt' => true,
                'is_active' => true
            ]
        ];

        foreach ($financialSubsidyTypes as $type) {
            DB::table('financial_subsidy_types')->insert(array_merge($type, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $this->command->info('Financial subsidy types seeded successfully!');
        $this->command->info('Created ' . count($financialSubsidyTypes) . ' financial subsidy types.');
    }
}