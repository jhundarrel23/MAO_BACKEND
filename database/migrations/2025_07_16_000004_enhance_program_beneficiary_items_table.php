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
            
            // Distribution tracking for duplicate prevention
            $table->date('distribution_date')->nullable()->after('approved_at');
            $table->year('distribution_year')->nullable()->after('distribution_date'); // For yearly duplicate checking
            $table->string('season')->nullable()->after('distribution_year'); // wet, dry, year-round
            
            // Add unique constraint to prevent duplicate benefits
            $table->index(['program_beneficiary_id', 'inventory_id', 'distribution_year', 'season'], 'idx_duplicate_prevention');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_beneficiary_items', function (Blueprint $table) {
            $table->dropForeign(['inventory_id']);
            $table->dropForeign(['approved_by']);
            $table->dropIndex('idx_duplicate_prevention');
            $table->dropColumn([
                'inventory_id',
                'suggested_amount',
                'coordinator_amount',
                'coordinator_notes',
                'unit_value',
                'total_value',
                'status',
                'approved_by',
                'approved_at',
                'distribution_date',
                'distribution_year',
                'season'
            ]);
        });
    }
};