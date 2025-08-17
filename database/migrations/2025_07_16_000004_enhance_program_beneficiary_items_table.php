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
            
            // Enhanced tracking fields for coordinator customization
            $table->decimal('suggested_amount', 10, 2)->nullable()->after('quantity'); // System suggested amount (optional)
            $table->decimal('coordinator_amount', 10, 2)->nullable()->after('suggested_amount'); // Coordinator customized amount
            $table->text('coordinator_notes')->nullable()->after('coordinator_amount'); // Coordinator's reasoning for the amount
            
            // Item value tracking
            $table->decimal('unit_value', 10, 2)->nullable()->after('unit'); // Value per unit
            $table->decimal('total_value', 10, 2)->nullable()->after('unit_value'); // Total value of this item
            
            // Status tracking
            $table->enum('status', ['pending', 'approved', 'prepared', 'distributed', 'cancelled'])->default('pending')->after('total_value');
            
            // Coordinator approval tracking
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
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
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'distribution_package_id',
                'inventory_id',
                'suggested_amount',
                'coordinator_amount',
                'coordinator_notes',
                'unit_value',
                'total_value',
                'status',
                'approved_by',
                'approved_at'
            ]);
        });
    }
};