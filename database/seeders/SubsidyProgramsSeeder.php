<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubsidyProgramsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'title' => 'Rice Production Support Program 2025',
                'description' => 'Comprehensive support program for rice farmers including seeds, fertilizers, and cash assistance for the wet season 2025.',
                'start_date' => '2025-01-01',
                'end_date' => '2025-06-30',
                'status' => 'ongoing',
                'is_approved' => true,
                'approval_status' => 'approved',
                'approved_by' => 1, // Admin User
                'approved_at' => now()->subDays(30),
                'created_by' => 2, // Maria Santos (Coordinator)
                'created_at' => now()->subDays(35),
                'updated_at' => now()->subDays(30)
            ],
            [
                'title' => 'Corn Farming Enhancement Program 2025',
                'description' => 'Support program for corn farmers focusing on hybrid seeds and modern farming techniques.',
                'start_date' => '2025-02-01',
                'end_date' => '2025-07-31',
                'status' => 'ongoing',
                'is_approved' => true,
                'approval_status' => 'approved',
                'approved_by' => 1, // Admin User
                'approved_at' => now()->subDays(20),
                'created_by' => 2, // Maria Santos (Coordinator)
                'created_at' => now()->subDays(25),
                'updated_at' => now()->subDays(20)
            ],
            [
                'title' => 'Emergency Cash Assistance for Farmers',
                'description' => 'Emergency cash assistance for farmers affected by recent weather disturbances.',
                'start_date' => '2025-01-15',
                'end_date' => '2025-03-15',
                'status' => 'ongoing',
                'is_approved' => true,
                'approval_status' => 'approved',
                'approved_by' => 1, // Admin User
                'approved_at' => now()->subDays(10),
                'created_by' => 2, // Maria Santos (Coordinator)
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(10)
            ],
            [
                'title' => 'Vegetable Production Support Program',
                'description' => 'Support program for vegetable farmers including seeds, fertilizers, and technical assistance.',
                'start_date' => '2025-03-01',
                'end_date' => '2025-08-31',
                'status' => 'pending',
                'is_approved' => false,
                'approval_status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'created_by' => 2, // Maria Santos (Coordinator)
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5)
            ]
        ];

        DB::table('subsidy_programs')->insert($programs);
    }
}