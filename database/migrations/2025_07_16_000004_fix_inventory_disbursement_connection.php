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
        // First, let's enhance the inventories table
        Schema::table('inventories', function (Blueprint $table) {
            // Add more inventory management fields
            $table->string('item_code')->unique()->after('id'); // SKU/Item Code
            $table->text('description')->nullable()->after('item_name');
            $table->string('category')->nullable()->after('item_type'); // seeds, fertilizers, tools, etc.
            $table->decimal('unit_cost', 10, 2)->default(0)->after('unit');
            $table->integer('minimum_stock_level')->default(0)->after('unit_cost');
            $table->integer('maximum_stock_level')->default(1000)->after('minimum_stock_level');
            $table->integer('reorder_point')->default(0)->after('maximum_stock_level');
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active')->after('reorder_point');
            $table->boolean('is_subsidizable')->default(true)->after('status'); // Can this item be given as subsidy?
            $table->text('storage_location')->nullable()->after('is_subsidizable');
            $table->date('expiry_date')->nullable()->after('storage_location'); // For perishable items
            
            // Tracking fields
            $table->foreignId('created_by')->nullable()->after('expiry_date')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            
            // Add indexes
            $table->index(['category', 'status']);
            $table->index(['is_subsidizable', 'status']);
            $table->index('item_code');
        });

        // Create inventory stock movements table for better tracking
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            $table->enum('movement_type', ['stock_in', 'stock_out', 'adjustment', 'transfer', 'disbursement']);
            $table->integer('quantity'); // positive for in, negative for out
            $table->integer('balance_after'); // stock level after this movement
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            
            // Reference information
            $table->string('reference_type')->nullable(); // 'program_disbursement', 'purchase_order', 'donation', etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the related record
            $table->string('reference_number')->nullable(); // PO#, DR#, etc.
            $table->text('remarks')->nullable();
            
            // Tracking
            $table->foreignId('processed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('movement_date');
            $table->timestamps();
            
            // Indexes
            $table->index(['inventory_id', 'movement_date']);
            $table->index(['movement_type', 'movement_date']);
            $table->index(['reference_type', 'reference_id']);
        });

        // Create current stock levels view table for quick access
        Schema::create('inventory_current_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->unique()->constrained('inventories')->onDelete('cascade');
            $table->integer('current_stock')->default(0);
            $table->integer('reserved_stock')->default(0); // Stock reserved for approved programs
            $table->integer('available_stock')->default(0); // current_stock - reserved_stock
            $table->decimal('total_value', 12, 2)->default(0); // current_stock * unit_cost
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['current_stock', 'available_stock']);
        });

        // Now fix the program_beneficiary_items table to connect to inventory
        Schema::table('program_beneficiary_items', function (Blueprint $table) {
            // Add inventory connection
            $table->foreignId('inventory_id')->after('program_beneficiary_id')->constrained('inventories')->onDelete('cascade');
            
            // Keep item_name for display but make it nullable (will be filled from inventory)
            $table->string('item_name')->nullable()->change();
            $table->string('unit')->nullable()->change();
            
            // Add cost tracking
            $table->decimal('unit_cost', 10, 2)->default(0)->after('unit');
            $table->decimal('total_cost', 12, 2)->default(0)->after('unit_cost');
            
            // Add disbursement tracking
            $table->enum('disbursement_status', ['pending', 'reserved', 'released', 'cancelled'])->default('pending')->after('total_cost');
            $table->timestamp('reserved_at')->nullable()->after('disbursement_status');
            $table->foreignId('reserved_by')->nullable()->after('reserved_at')->constrained('users')->nullOnDelete();
            $table->string('batch_number')->nullable()->after('reserved_by'); // For tracking batches
            $table->text('disbursement_remarks')->nullable()->after('batch_number');
            
            // Add indexes
            $table->index(['inventory_id', 'disbursement_status']);
            $table->index(['disbursement_status', 'reserved_at']);
        });

        // Create subsidy program inventory allocation table
        Schema::create('program_inventory_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidy_program_id')->constrained('subsidy_programs')->onDelete('cascade');
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            $table->integer('allocated_quantity');
            $table->integer('distributed_quantity')->default(0);
            $table->integer('remaining_quantity')->default(0);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_allocation_cost', 12, 2);
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('allocation_remarks')->nullable();
            $table->foreignId('allocated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint to prevent duplicate allocations
            $table->unique(['subsidy_program_id', 'inventory_id']);
            
            // Indexes
            $table->index(['subsidy_program_id', 'status']);
            $table->index(['inventory_id', 'status']);
        });

        // Create disbursement batches table for better tracking
        Schema::create('disbursement_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->foreignId('subsidy_program_id')->constrained('subsidy_programs')->onDelete('cascade');
            $table->date('disbursement_date');
            $table->string('location'); // Where disbursement happened
            $table->integer('total_beneficiaries');
            $table->integer('total_items_distributed');
            $table->decimal('total_value_distributed', 12, 2);
            $table->enum('status', ['planned', 'ongoing', 'completed', 'cancelled'])->default('planned');
            $table->text('batch_remarks')->nullable();
            $table->foreignId('batch_coordinator')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['subsidy_program_id', 'disbursement_date']);
            $table->index(['status', 'disbursement_date']);
        });

        // Add batch tracking to program_beneficiary_items
        Schema::table('program_beneficiary_items', function (Blueprint $table) {
            $table->foreignId('disbursement_batch_id')->nullable()->after('batch_number')->constrained('disbursement_batches')->nullOnDelete();
        });

        // Enhance subsidy_programs table with inventory management
        Schema::table('subsidy_programs', function (Blueprint $table) {
            $table->decimal('total_budget', 12, 2)->nullable()->after('description');
            $table->decimal('allocated_budget', 12, 2)->default(0)->after('total_budget');
            $table->decimal('disbursed_amount', 12, 2)->default(0)->after('allocated_budget');
            $table->decimal('remaining_budget', 12, 2)->default(0)->after('disbursed_amount');
            $table->integer('target_beneficiaries')->nullable()->after('remaining_budget');
            $table->integer('actual_beneficiaries')->default(0)->after('target_beneficiaries');
            $table->enum('inventory_source', ['municipal_stock', 'procurement', 'donation'])->default('municipal_stock')->after('actual_beneficiaries');
            
            // Add indexes
            $table->index(['status', 'start_date']);
            $table->index(['inventory_source', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('disbursement_batches');
        Schema::dropIfExists('program_inventory_allocations');
        Schema::dropIfExists('inventory_current_stocks');
        Schema::dropIfExists('inventory_movements');
        
        // Remove columns from existing tables
        Schema::table('program_beneficiary_items', function (Blueprint $table) {
            $table->dropForeign(['inventory_id']);
            $table->dropForeign(['reserved_by']);
            $table->dropForeign(['disbursement_batch_id']);
            $table->dropIndex(['inventory_id', 'disbursement_status']);
            $table->dropIndex(['disbursement_status', 'reserved_at']);
            $table->dropColumn([
                'inventory_id', 'unit_cost', 'total_cost', 'disbursement_status',
                'reserved_at', 'reserved_by', 'batch_number', 'disbursement_remarks',
                'disbursement_batch_id'
            ]);
            $table->string('item_name')->nullable(false)->change();
            $table->string('unit')->nullable(false)->change();
        });
        
        Schema::table('subsidy_programs', function (Blueprint $table) {
            $table->dropIndex(['status', 'start_date']);
            $table->dropIndex(['inventory_source', 'status']);
            $table->dropColumn([
                'total_budget', 'allocated_budget', 'disbursed_amount', 'remaining_budget',
                'target_beneficiaries', 'actual_beneficiaries', 'inventory_source'
            ]);
        });
        
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropIndex(['category', 'status']);
            $table->dropIndex(['is_subsidizable', 'status']);
            $table->dropIndex(['item_code']);
            $table->dropColumn([
                'item_code', 'description', 'category', 'unit_cost', 'minimum_stock_level',
                'maximum_stock_level', 'reorder_point', 'status', 'is_subsidizable',
                'storage_location', 'expiry_date', 'created_by', 'updated_by'
            ]);
        });
    }
};