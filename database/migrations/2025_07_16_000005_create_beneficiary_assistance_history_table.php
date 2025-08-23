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
        Schema::create('beneficiary_assistance_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained('beneficiary_details')->onDelete('cascade');
            $table->foreignId('subsidy_program_id')->constrained('subsidy_programs')->onDelete('cascade');
            $table->string('assistance_type'); // seed, fertilizer, cash, fuel, etc.
            $table->string('item_name');
            $table->decimal('quantity_received', 10, 2);
            $table->string('unit');
            $table->decimal('total_value', 12, 2)->default(0.00);
            
            // Time tracking
            $table->date('distribution_date');
            $table->year('assistance_year'); // For yearly duplicate checking
            $table->string('season')->nullable(); // wet, dry, year-round
            
            // Reference tracking
            $table->foreignId('program_beneficiary_item_id')->nullable()->constrained('program_beneficiary_items')->nullOnDelete();
            $table->string('reference_code')->nullable(); // Additional reference
            
            // Duplicate prevention
            $table->unique(['beneficiary_id', 'subsidy_program_id', 'assistance_type', 'assistance_year', 'season'], 'unique_assistance_per_season');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_assistance_history');
    }
};