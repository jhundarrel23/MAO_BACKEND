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
        Schema::create('distribution_packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_code')->unique(); // e.g., "PKG-2025-001"
            $table->foreignId('program_beneficiary_id')->constrained('program_beneficiaries')->onDelete('cascade');
            $table->string('package_name'); // e.g., "Complete Farming Support Package"
            
            // Package status and tracking
            $table->enum('status', ['pending', 'prepared', 'distributed', 'completed', 'cancelled'])->default('pending');
            $table->decimal('total_value', 12, 2)->default(0.00); // Total value of the package
            
            // Distribution tracking
            $table->timestamp('prepared_at')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('distributed_at')->nullable();
            $table->foreignId('distributed_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Beneficiary acknowledgment
            $table->boolean('beneficiary_acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->text('beneficiary_signature')->nullable(); // Could store signature image path
            $table->text('distribution_notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_packages');
    }
};