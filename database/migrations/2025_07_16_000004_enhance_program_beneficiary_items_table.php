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
        Schema::table('program_beneficiary_items', function (Blueprint $table) {
            // Add reference to distribution package for grouped items
            $table->foreignId('distribution_package_id')->nullable()
                  ->after('program_beneficiary_id')
                  ->constrained('distribution_packages')
                  ->nullOnDelete();
            
            // Add inventory reference for better tracking
            $table->foreignId('inventory_id')->nullable()
                  ->after('item_name')
                  ->constrained('inventories')
                  ->nullOnDelete();
            
            // Enhanced tracking fields
            $table->decimal('calculated_amount', 10, 2)->nullable()->after('quantity'); // Amount calculated by rules
            $table->decimal('actual_amount', 10, 2)->nullable()->after('calculated_amount'); // Actual distributed amount
            $table->text('calculation_notes')->nullable()->after('actual_amount'); // Notes about calculation
            
            // Distribution rule reference
            $table->foreignId('distribution_rule_id')->nullable()
                  ->after('calculation_notes')
                  ->constrained('distribution_rules')
                  ->nullOnDelete();
            
            // Item value tracking
            $table->decimal('unit_value', 10, 2)->nullable()->after('unit'); // Value per unit
            $table->decimal('total_value', 10, 2)->nullable()->after('unit_value'); // Total value of this item
            
            // Status tracking
            $table->enum('status', ['pending', 'approved', 'prepared', 'distributed', 'cancelled'])->default('pending')->after('total_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_beneficiary_items', function (Blueprint $table) {
            $table->dropForeign(['distribution_package_id']);
            $table->dropForeign(['inventory_id']);
            $table->dropForeign(['distribution_rule_id']);
            $table->dropColumn([
                'distribution_package_id',
                'inventory_id',
                'calculated_amount',
                'actual_amount',
                'calculation_notes',
                'distribution_rule_id',
                'unit_value',
                'total_value',
                'status'
            ]);
        });
    }
};