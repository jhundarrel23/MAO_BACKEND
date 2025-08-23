<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores detailed personal information for users with role='beneficiary'
     * This is the main table for beneficiary personal details and verification status
     */
    public function up(): void
    {
        Schema::create('beneficiary_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // RSBSA Numbers - Fixed to handle string format like "10-43-21-015-000107"
            $table->string('system_generated_rsbsa_number')->nullable(); // System generated
            $table->string('manual_rsbsa_number')->nullable(); // Manually added by coordinator
            
            // RSBSA Verification Status
            $table->enum('rsbsa_verification_status', ['not_verified', 'pending', 'verified', 'rejected'])->default('not_verified');
            $table->text('rsbsa_verification_notes')->nullable(); // Coordinator notes for verification
            $table->timestamp('rsbsa_verified_at')->nullable();
            $table->foreignId('rsbsa_verified_by')->nullable()->constrained('users')->nullOnDelete();

            // Location Information
            $table->string('barangay');
            $table->string('municipality')->default('Opol');
            $table->string('province')->default('Misamis Oriental');
            $table->string('region')->default('X');

            // Contact Information - Standardized as string
            $table->string('contact_number');
            $table->string('emergency_contact_number')->nullable(); // Fixed: changed from integer

            // Personal Information
            $table->date('birth_date');
            $table->string('place_of_birth');
            $table->enum('sex', ['male', 'female']);
            $table->string('civil_status')->nullable(); // e.g., single, married, etc.
            $table->string('name_of_spouse')->nullable();

            // Education
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

            // Religion & PWD
            $table->string('religion')->nullable();
            $table->boolean('is_pwd')->default(false);

            // ID Information
            $table->enum('has_government_id', ['yes', 'no'])->default('no');
            $table->string('gov_id_type')->nullable();
            $table->string('gov_id_number')->nullable();

            // Association Membership
            $table->enum('is_association_member', ['yes', 'no'])->default('no');
            $table->string('association_name')->nullable();

            // Household Information
            $table->string('mothers_maiden_name')->nullable();
            $table->boolean('is_household_head')->default(false);
            $table->string('household_head_name')->nullable();

            // Profile Completion and Verification System
            $table->enum('profile_completion_status', ['incomplete', 'completed', 'verified', 'needs_update'])->default('incomplete');
            $table->decimal('completion_percentage', 5, 2)->default(0.00); // 0.00 to 100.00
            $table->boolean('is_profile_verified')->default(false);
            $table->text('verification_notes')->nullable(); // Coordinator notes
            $table->timestamp('profile_verified_at')->nullable();
            $table->foreignId('profile_verified_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Data source tracking
            $table->enum('data_source', ['self_registration', 'coordinator_input', 'da_import'])->default('self_registration');
            $table->timestamp('last_updated_by_beneficiary')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['rsbsa_verification_status']);
            $table->index(['profile_completion_status']);
            $table->index(['barangay']);
            $table->index(['system_generated_rsbsa_number']);
            $table->index(['manual_rsbsa_number']);
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
