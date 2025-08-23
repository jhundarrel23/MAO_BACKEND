<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsbsa_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('beneficiary_id')->constrained('beneficiary_details')->onDelete('cascade');
            $table->foreignId('farm_profile_id')->constrained('farm_profiles')->onDelete('cascade'); 

            // Enrollment application details
            $table->string('application_reference_code')->unique();
            $table->year('enrollment_year');
            $table->enum('enrollment_type', ['new', 'renewal', 'update'])->default('new');

            // Application status workflow
            $table->enum('application_status', [
                'draft', 'submitted', 'reviewing', 'approved', 'rejected', 'cancelled'
            ])->default('draft');

            // Process tracking
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable(); 
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('coordinator_notes')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_rsbsa_number')->nullable();
            $table->timestamp('rsbsa_number_assigned_at')->nullable();

            $table->timestamps();

            $table->index(['application_status']);
            $table->index(['enrollment_year']);
            $table->index(['beneficiary_id', 'enrollment_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rsbsa_enrollments');
    }
};