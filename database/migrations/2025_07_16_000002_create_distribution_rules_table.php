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
        Schema::create('distribution_guidelines', function (Blueprint $table) {
            $table->id();
            $table->string('guideline_name'); // e.g., "Rice Seed Distribution Guidelines"
            $table->string('item_type'); // seed, fertilizer, cash, fuel, etc.
            $table->text('description'); // Guidelines description
            
            // Simple guideline parameters for coordinator reference
            $table->decimal('suggested_amount_per_hectare', 10, 2)->nullable(); // Suggested amount per hectare
            $table->decimal('minimum_amount', 10, 2)->default(0.00); // Minimum distribution amount
            $table->decimal('maximum_amount', 10, 2)->nullable(); // Maximum distribution amount
            $table->string('unit'); // bags, liters, PHP, etc.
            
            // Simple guidelines text
            $table->text('considerations')->nullable(); // Text guidelines for coordinators
            // Example: "Consider farm size, household needs, previous assistance received"
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_guidelines');
    }
};