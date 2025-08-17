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
        Schema::create('inventory_current_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            
            // Current stock information
            $table->decimal('current_quantity', 12, 2)->default(0.00); // Current available quantity
            $table->decimal('reserved_quantity', 12, 2)->default(0.00); // Quantity reserved for distributions
            $table->decimal('available_quantity', 12, 2)->default(0.00); // Available for new distributions
            
            // Stock value tracking
            $table->decimal('average_unit_cost', 10, 2)->default(0.00); // Weighted average cost
            $table->decimal('total_stock_value', 12, 2)->default(0.00); // Total value of current stock
            
            // Stock level alerts
            $table->decimal('minimum_stock_level', 10, 2)->default(0.00); // Reorder point
            $table->decimal('maximum_stock_level', 10, 2)->nullable(); // Maximum capacity
            $table->boolean('is_low_stock')->default(false); // Auto-calculated flag
            $table->boolean('is_out_of_stock')->default(false); // Auto-calculated flag
            
            // Last movement tracking
            $table->timestamp('last_stock_in')->nullable(); // Last time stock was added
            $table->timestamp('last_stock_out')->nullable(); // Last time stock was distributed
            $table->timestamp('last_updated')->nullable(); // Last stock calculation update
            
            // Location and batch tracking
            $table->string('primary_location')->nullable(); // Main storage location
            $table->json('batch_details')->nullable(); // JSON array of batches with quantities and expiry dates
            
            $table->timestamps();
            
            // Ensure one record per inventory item
            $table->unique('inventory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_current_stocks');
    }
};