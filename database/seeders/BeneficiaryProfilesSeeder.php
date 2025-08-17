<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BeneficiaryProfilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $beneficiaries = [
            [
                'user_id' => 3, // Juan Dela Cruz
                'SYSTEM_GENERATED_RSBSA_NUMBER' => 2025001,
                'RSBSA_NUMBER' => 100001,
                'barangay' => 'Poblacion',
                'municipality' => 'Opol',
                'province' => 'Misamis Oriental',
                'region' => 'X',
                'contact_number' => '09171234567',
                'birth_date' => '1975-05-15',
                'place_of_birth' => 'Opol, Misamis Oriental',
                'sex' => 'male',
                'civil_status' => 'married',
                'name_of_spouse' => 'Rosa Dela Cruz',
                'highest_education' => 'Elementary',
                'religion' => 'Catholic',
                'is_pwd' => false,
                'has_government_id' => 'yes',
                'gov_id_type' => 'PhilID',
                'gov_id_number' => '1234-5678-9012',
                'is_association_member' => 'yes',
                'association_name' => 'Opol Farmers Association',
                'mothers_maiden_name' => 'Santos',
                'is_household_head' => true,
                'emergency_contact_number' => 9176543210,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 4, // Maria Gonzales
                'SYSTEM_GENERATED_RSBSA_NUMBER' => 2025002,
                'RSBSA_NUMBER' => 100002,
                'barangay' => 'Awang',
                'municipality' => 'Opol',
                'province' => 'Misamis Oriental',
                'region' => 'X',
                'contact_number' => '09187654321',
                'birth_date' => '1980-08-22',
                'place_of_birth' => 'Cagayan de Oro City',
                'sex' => 'female',
                'civil_status' => 'single',
                'name_of_spouse' => null,
                'highest_education' => 'Senior High School',
                'religion' => 'Catholic',
                'is_pwd' => false,
                'has_government_id' => 'yes',
                'gov_id_type' => 'Voters ID',
                'gov_id_number' => 'VID-2024-001',
                'is_association_member' => 'no',
                'association_name' => null,
                'mothers_maiden_name' => 'Reyes',
                'is_household_head' => true,
                'emergency_contact_number' => 9189876543,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 5, // David Rodriguez
                'SYSTEM_GENERATED_RSBSA_NUMBER' => 2025003,
                'RSBSA_NUMBER' => 100003,
                'barangay' => 'Bonbon',
                'municipality' => 'Opol',
                'province' => 'Misamis Oriental',
                'region' => 'X',
                'contact_number' => '09195551234',
                'birth_date' => '1985-12-10',
                'place_of_birth' => 'Opol, Misamis Oriental',
                'sex' => 'male',
                'civil_status' => 'married',
                'name_of_spouse' => 'Carmen Rodriguez',
                'highest_education' => 'Junior High School',
                'religion' => 'Protestant',
                'is_pwd' => false,
                'has_government_id' => 'yes',
                'gov_id_type' => 'Drivers License',
                'gov_id_number' => 'N01-85-123456',
                'is_association_member' => 'yes',
                'association_name' => 'Bonbon Corn Growers',
                'mothers_maiden_name' => 'Torres',
                'is_household_head' => true,
                'emergency_contact_number' => 9194443333,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 6, // Ana Reyes
                'SYSTEM_GENERATED_RSBSA_NUMBER' => 2025004,
                'RSBSA_NUMBER' => 100004,
                'barangay' => 'Bugo',
                'municipality' => 'Opol',
                'province' => 'Misamis Oriental',
                'region' => 'X',
                'contact_number' => '09202224444',
                'birth_date' => '1970-03-25',
                'place_of_birth' => 'Opol, Misamis Oriental',
                'sex' => 'female',
                'civil_status' => 'widow',
                'name_of_spouse' => 'Pedro Reyes (deceased)',
                'highest_education' => 'Elementary',
                'religion' => 'Catholic',
                'is_pwd' => true, // PWD - Senior citizen
                'has_government_id' => 'yes',
                'gov_id_type' => 'Senior Citizen ID',
                'gov_id_number' => 'SC-2020-001',
                'is_association_member' => 'no',
                'association_name' => null,
                'mothers_maiden_name' => 'Cruz',
                'is_household_head' => true,
                'emergency_contact_number' => 9203334444,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('beneficiary_profiles')->insert($beneficiaries);
    }
}