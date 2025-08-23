<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table tracks RSBSA enrollment/application process
     * Once approved, the RSBSA number is stored in beneficiary_details table
     */
    public function up(): void
    {
        Schema::create('rsbsa_enrollments', function (Blueprint $table) {
            $table->id();

            // Links to beneficiary and their farm profile
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('beneficiary_id')->constrained('beneficiary_details')->onDelete('cascade');
            $table->foreignId('farm_profile_id')->constrained('farm_profiles')->onDelete('cascade'); 

            // Enrollment application details
            $table->string('application_reference_code')->unique(); // e.g., "RSBSA-APP-2025-001"
            $table->year('enrollment_year'); // Which year they're applying for
            $table->enum('enrollment_type', ['new', 'renewal', 'update'])->default('new');

            // Application status workflow
            $table->enum('application_status', [
                'draft',        // Beneficiary still filling out
                'submitted',    // Submitted to coordinator
                'reviewing',    // Coordinator reviewing
                'approved',     // Approved - RSBSA number will be assigned
                'rejected',     // Rejected - needs resubmission
                'cancelled'     // Cancelled by beneficiary/coordinator
            ])->default('draft');

            // Process tracking
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable(); 
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('coordinator_notes')->nullable();

            // Who processed the application
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete(); 

            // Once approved, this links to the actual assigned RSBSA number in beneficiary_details
            $table->string('assigned_rsbsa_number')->nullable(); // Populated after approval
            $table->timestamp('rsbsa_number_assigned_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['application_status']);
            $table->index(['enrollment_year']);
            $table->index(['beneficiary_id', 'enrollment_year']); // One application per year per beneficiary
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rsbsa_enrollments');
    }
};
