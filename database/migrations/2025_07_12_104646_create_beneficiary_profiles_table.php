<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores comprehensive personal information for users with role='beneficiary'
     * This is the main table for beneficiary personal details, verification status, and profile management
     */
    public function up(): void
    {
        Schema::create('beneficiary_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // =================================================================
            // RSBSA INFORMATION & VERIFICATION
            // =================================================================
            $table->string('system_generated_rsbsa_number', 50)->nullable();
            $table->string('manual_rsbsa_number', 50)->nullable();
            $table->enum('rsbsa_verification_status', [
                'not_verified', 'pending', 'verified', 'rejected'
            ])->default('not_verified');
            $table->text('rsbsa_verification_notes')->nullable();
            $table->timestamp('rsbsa_verified_at')->nullable();
            $table->foreignId('rsbsa_verified_by')->nullable()->constrained('users')->nullOnDelete();

            // =================================================================
            // LOCATION INFORMATION  
            // =================================================================
            $table->string('barangay', 100);
            $table->string('municipality', 100)->default('Opol');
            $table->string('province', 100)->default('Misamis Oriental');
            $table->string('region', 100)->default('Region X (Northern Mindanao)');

            // =================================================================
            // CONTACT INFORMATION
            // =================================================================
            $table->string('contact_number', 20);
            $table->string('emergency_contact_number', 20)->nullable();

            // =================================================================
            // PERSONAL INFORMATION
            // =================================================================
            $table->date('birth_date');
            $table->string('place_of_birth', 150)->nullable();
            $table->enum('sex', ['male', 'female']);
            $table->enum('civil_status', [
                'single', 'married', 'widowed', 'separated', 'divorced'
            ])->nullable();
            $table->string('name_of_spouse', 150)->nullable();

            // =================================================================
            // EDUCATIONAL & DEMOGRAPHIC INFORMATION
            // =================================================================
            $table->enum('highest_education', [
                'None',
                'Pre-school', 
                'Elementary',
                'Junior High School',
                'Senior High School',
                'Vocational',
                'College',
                'Post Graduate'
            ])->nullable();
            $table->string('religion', 100)->nullable();
            $table->boolean('is_pwd')->default(false);

            // =================================================================
            // GOVERNMENT ID INFORMATION
            // =================================================================
            $table->enum('has_government_id', ['yes', 'no'])->default('no');
            $table->string('gov_id_type', 100)->nullable();
            $table->string('gov_id_number', 100)->nullable();

            // =================================================================
            // ASSOCIATION & ORGANIZATION MEMBERSHIP
            // =================================================================
            $table->enum('is_association_member', ['yes', 'no'])->default('no');
            $table->string('association_name', 200)->nullable();

            // =================================================================
            // HOUSEHOLD INFORMATION
            // =================================================================
            $table->string('mothers_maiden_name', 150)->nullable();
            $table->boolean('is_household_head')->default(false);
            $table->string('household_head_name', 150)->nullable();

            // =================================================================
            // PROFILE COMPLETION & VERIFICATION SYSTEM
            // =================================================================
            $table->enum('profile_completion_status', [
                'incomplete', 'completed', 'verified', 'needs_update'
            ])->default('incomplete');
            $table->decimal('completion_percentage', 5, 2)->default(0.00);
            $table->boolean('is_profile_verified')->default(false);
            $table->text('verification_notes')->nullable();
            $table->timestamp('profile_verified_at')->nullable();
            $table->foreignId('profile_verified_by')->nullable()->constrained('users')->nullOnDelete();
            
            // =================================================================
            // DATA SOURCE & AUDIT TRACKING
            // =================================================================
            $table->enum('data_source', [
                'self_registration', 'coordinator_input', 'da_import', 'system_migration'
            ])->default('self_registration');
            $table->timestamp('last_updated_by_beneficiary')->nullable();
            $table->json('completion_tracking')->nullable();
            
            // =================================================================
            // SYSTEM TIMESTAMPS
            // =================================================================
            $table->timestamps();
            $table->softDeletes();

            // =================================================================
            // DATABASE INDEXES - SHORT NAMES TO AVOID MYSQL LIMIT
            // =================================================================
            $table->unique(['user_id'], 'bd_user_unique');
            $table->unique(['system_generated_rsbsa_number'], 'bd_sys_rsbsa_unique');
            
            // Short index names (under 64 characters)
            $table->index(['rsbsa_verification_status'], 'bd_rsbsa_status_idx');
            $table->index(['profile_completion_status'], 'bd_profile_status_idx');
            $table->index(['barangay'], 'bd_barangay_idx');
            $table->index(['is_profile_verified'], 'bd_verified_idx');
            $table->index(['data_source'], 'bd_data_source_idx');
            $table->index(['created_at'], 'bd_created_idx');
            
            // Composite indexes with short names
            $table->index(['barangay', 'profile_completion_status'], 'bd_brgy_status_idx');
            $table->index(['rsbsa_verification_status', 'profile_completion_status'], 'bd_dual_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_details');
    }
};
