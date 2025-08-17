<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@opol.gov.ph',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'coordinator@opol.gov.ph',
                'email_verified_at' => now(),
                'password' => Hash::make('coordinator123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('beneficiary123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Maria Gonzales',
                'email' => 'maria.gonzales@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('beneficiary123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'David Rodriguez',
                'email' => 'david.rodriguez@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('beneficiary123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Ana Reyes',
                'email' => 'ana.reyes@gmail.com',
                'email_verified_at' => now(),
                'password' => Hash::make('beneficiary123'),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('users')->insert($users);
    }
}