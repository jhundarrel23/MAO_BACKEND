<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add fields to track DA-assigned RSBSA numbers
        Schema::table('rsbsa_enrollments', function (Blueprint $table) {
            // DA RSBSA Number Assignment Tracking
            $table->string('da_rsbsa_assigned_by')->nullable()->after('registration_expires_at');
            $table->date('da_assignment_date')->nullable()->after('da_rsbsa_assigned_by');
            $table->text('da_assignment_remarks')->nullable()->after('da_assignment_date');
            $table->boolean('rsbsa_number_assigned')->default(false)->after('da_assignment_remarks');
            $table->timestamp('rsbsa_number_assigned_at')->nullable()->after('rsbsa_number_assigned');
            
            // Add index for tracking assignments
            $table->index(['rsbsa_number_assigned', 'da_assignment_date']);
        });

        // Add fields to beneficiary_profiles for better RSBSA tracking
        Schema::table('beneficiary_profiles', function (Blueprint $table) {
            // Make RSBSA_NUMBER nullable initially (until DA assigns)
            $table->string('RSBSA_NUMBER', 20)->nullable()->change();
            
            // Add validation status for RSBSA numbers
            $table->boolean('rsbsa_number_validated')->default(false)->after('SYSTEM_GENERATED_RSBSA_NUMBER');
            $table->timestamp('rsbsa_number_validated_at')->nullable()->after('rsbsa_number_validated');
            $table->foreignId('rsbsa_validated_by')->nullable()->after('rsbsa_number_validated_at')->constrained('users')->nullOnDelete();
            
            // Add index for RSBSA number searches
            $table->index(['RSBSA_NUMBER', 'rsbsa_number_validated']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rsbsa_enrollments', function (Blueprint $table) {
            $table->dropIndex(['rsbsa_number_assigned', 'da_assignment_date']);
            $table->dropColumn([
                'da_rsbsa_assigned_by',
                'da_assignment_date', 
                'da_assignment_remarks',
                'rsbsa_number_assigned',
                'rsbsa_number_assigned_at'
            ]);
        });

        Schema::table('beneficiary_profiles', function (Blueprint $table) {
            $table->dropIndex(['RSBSA_NUMBER', 'rsbsa_number_validated']);
            $table->dropForeign(['rsbsa_validated_by']);
            $table->dropColumn([
                'rsbsa_number_validated',
                'rsbsa_number_validated_at',
                'rsbsa_validated_by'
            ]);
        });
    }
};