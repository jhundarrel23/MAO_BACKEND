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
        Schema::create('distribution_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name'); // e.g., "Seed Distribution by Farm Area", "Cash Assistance by Income Level"
            $table->string('item_type'); // seed, fertilizer, cash, etc.
            $table->enum('calculation_basis', ['farm_area', 'household_size', 'income_level', 'priority_score', 'fixed_amount', 'custom_formula']);
            
            // Base calculation parameters
            $table->decimal('base_amount', 10, 2)->default(0.00); // Base amount per unit
            $table->decimal('multiplier', 8, 4)->default(1.0000); // Multiplier factor
            $table->decimal('minimum_amount', 10, 2)->default(0.00); // Minimum distribution amount
            $table->decimal('maximum_amount', 10, 2)->nullable(); // Maximum distribution amount (null = no limit)
            
            // Conditional modifiers
            $table->json('condition_modifiers')->nullable(); // JSON for complex conditions
            // Example: {"is_senior_citizen": 1.2, "has_disabled_member": 1.15, "disaster_vulnerability": {"high": 1.3, "moderate": 1.1}}
            
            // Farm area brackets (for area-based calculations)
            $table->json('area_brackets')->nullable(); 
            // Example: {"0-1": {"multiplier": 1.0, "base": 2}, "1-2": {"multiplier": 1.0, "base": 3}, "2+": {"multiplier": 1.0, "base": 4}}
            
            // Income brackets (for income-based calculations)
            $table->json('income_brackets')->nullable();
            // Example: {"very_low": 1.5, "low": 1.2, "moderate": 1.0, "high": 0.8}
            
            $table->text('custom_formula')->nullable(); // For complex custom calculations
            $table->boolean('is_active')->default(true);
            $table->integer('priority_order')->default(1); // Rule application order
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_rules');
    }
};