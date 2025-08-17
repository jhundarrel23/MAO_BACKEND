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
                'fname' => 'Admin',
                'mname' => null,
                'lname' => 'User',
                'extension_name' => null,
                'username' => 'admin',
                'email' => 'admin@opol.gov.ph',
                'phone_number' => '09171111111',
                'role' => 'admin',
                'status' => 'active',
                'sector_id' => null,
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fname' => 'Maria',
                'mname' => 'Cruz',
                'lname' => 'Santos',
                'extension_name' => null,
                'username' => 'coordinator',
                'email' => 'coordinator@opol.gov.ph',
                'phone_number' => '09172222222',
                'role' => 'coordinator',
                'status' => 'active',
                'sector_id' => 1, // Farmer sector
                'email_verified_at' => now(),
                'password' => Hash::make('coordinator123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fname' => 'Juan',
                'mname' => 'Santos',
                'lname' => 'Dela Cruz',
                'extension_name' => null,
                'username' => 'juan.delacruz',
                'email' => 'juan.delacruz@gmail.com',
                'phone_number' => '09171234567',
                'role' => 'beneficiary',
                'status' => 'active',
                'sector_id' => 1, // Farmer sector
                'email_verified_at' => now(),
                'password' => Hash::make('beneficiary123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fname' => 'Maria',
                'mname' => 'Reyes',
                'lname' => 'Gonzales',
                'extension_name' => null,
                'username' => 'maria.gonzales',
                'email' => 'maria.gonzales@gmail.com',
                'phone_number' => '09187654321',
                'role' => 'beneficiary',
                'status' => 'active',
                'sector_id' => 1, // Farmer sector
                'email_verified_at' => now(),
                'password' => Hash::make('beneficiary123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fname' => 'David',
                'mname' => 'Torres',
                'lname' => 'Rodriguez',
                'extension_name' => null,
                'username' => 'david.rodriguez',
                'email' => 'david.rodriguez@gmail.com',
                'phone_number' => '09195551234',
                'role' => 'beneficiary',
                'status' => 'active',
                'sector_id' => 1, // Farmer sector
                'email_verified_at' => now(),
                'password' => Hash::make('beneficiary123'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fname' => 'Ana',
                'mname' => 'Cruz',
                'lname' => 'Reyes',
                'extension_name' => null,
                'username' => 'ana.reyes',
                'email' => 'ana.reyes@gmail.com',
                'phone_number' => '09202224444',
                'role' => 'beneficiary',
                'status' => 'active',
                'sector_id' => 1, // Farmer sector
                'email_verified_at' => now(),
                'password' => Hash::make('beneficiary123'),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('users')->insert($users);
    }
}