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
        Schema::table('inventory_stocks', function (Blueprint $table) {
            // Enhanced movement tracking
            $table->enum('movement_type', ['stock_in', 'stock_out', 'adjustment', 'transfer', 'distribution'])
                  ->after('quantity');
            
            // Better categorization of stock movements
            $table->enum('transaction_type', [
                'purchase', 'donation', 'return', 'distribution', 
                'damage', 'expired', 'transfer_in', 'transfer_out', 
                'adjustment', 'initial_stock'
            ])->after('movement_type');
            
            // Connection to distribution system
            $table->foreignId('program_beneficiary_item_id')->nullable()
                  ->after('transaction_type')
                  ->constrained('program_beneficiary_items')
                  ->nullOnDelete();
            
            // Enhanced tracking fields
            $table->decimal('unit_cost', 10, 2)->nullable()->after('quantity'); // Cost per unit
            $table->decimal('total_value', 12, 2)->nullable()->after('unit_cost'); // Total value of movement
            $table->decimal('running_balance', 12, 2)->default(0.00)->after('total_value'); // Running stock balance
            
            // Better date tracking
            $table->date('transaction_date')->after('date_received'); // Actual transaction date
            $table->string('batch_number')->nullable()->after('reference'); // For batch tracking
            $table->date('expiry_date')->nullable()->after('batch_number'); // For items with expiry
            
            // Enhanced source/destination tracking
            $table->string('destination')->nullable()->after('source'); // Where items went (for stock-out)
            $table->text('remarks')->nullable()->after('destination'); // Additional notes
            
            // Approval workflow
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending')->after('is_verified');
            $table->timestamp('approved_at')->nullable()->after('verified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_stocks', function (Blueprint $table) {
            $table->dropForeign(['program_beneficiary_item_id']);
            $table->dropColumn([
                'movement_type',
                'transaction_type',
                'program_beneficiary_item_id',
                'unit_cost',
                'total_value',
                'running_balance',
                'transaction_date',
                'batch_number',
                'expiry_date',
                'destination',
                'remarks',
                'status',
                'approved_at'
            ]);
        });
    }
};