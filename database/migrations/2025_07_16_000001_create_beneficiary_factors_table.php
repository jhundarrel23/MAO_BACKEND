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
        Schema::create('beneficiary_factors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained('beneficiary_profiles')->onDelete('cascade');
            
            // Farm-related factors
            $table->decimal('total_farm_area', 10, 2)->default(0.00); // Total hectares owned/managed
            $table->integer('number_of_parcels')->default(0); // Number of farm parcels
            $table->enum('primary_crop_type', ['rice', 'corn', 'vegetable', 'fruit', 'mixed', 'other'])->nullable();
            $table->enum('farming_experience_level', ['beginner', 'intermediate', 'experienced', 'expert'])->default('intermediate');
            
            // Household factors
            $table->integer('household_size')->default(1); // Number of family members
            $table->integer('dependents_count')->default(0); // Number of dependents (children, elderly)
            $table->decimal('estimated_monthly_income', 10, 2)->nullable(); // In PHP
            $table->enum('income_level', ['very_low', 'low', 'moderate', 'high'])->default('low');
            
            // Vulnerability factors
            $table->boolean('is_senior_citizen')->default(false);
            $table->boolean('is_solo_parent')->default(false);
            $table->boolean('has_disabled_member')->default(false);
            $table->boolean('is_indigenous_people')->default(false);
            $table->enum('disaster_vulnerability', ['low', 'moderate', 'high'])->default('moderate');
            
            // Agricultural factors
            $table->boolean('is_organic_farmer')->default(false);
            $table->boolean('has_irrigation_access')->default(false);
            $table->enum('soil_fertility', ['poor', 'fair', 'good', 'excellent'])->default('fair');
            $table->boolean('has_farm_equipment')->default(false);
            
            // Priority scoring (calculated field)
            $table->decimal('priority_score', 5, 2)->default(0.00); // Calculated based on factors
            $table->text('special_needs')->nullable(); // Additional considerations
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_factors');
    }
};