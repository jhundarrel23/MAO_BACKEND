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
        // Create financial subsidy types table
        Schema::create('financial_subsidy_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_name'); // e.g., "Cash Assistance", "Fuel Allowance", "Planting Allowance"
            $table->text('description')->nullable();
            $table->decimal('default_amount', 10, 2)->nullable(); // Default amount per beneficiary
            $table->enum('disbursement_method', ['cash', 'check', 'bank_transfer', 'mobile_money'])->default('cash');
            $table->boolean('requires_receipt')->default(true);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Index
            $table->index(['is_active', 'type_name']);
        });

        // Enhance subsidy_programs to support both inventory and financial subsidies
        Schema::table('subsidy_programs', function (Blueprint $table) {
            $table->enum('subsidy_type', ['inventory_only', 'financial_only', 'mixed'])
                  ->default('inventory_only')
                  ->after('inventory_source');
            $table->decimal('total_financial_budget', 12, 2)->default(0)->after('subsidy_type');
            $table->decimal('disbursed_financial_amount', 12, 2)->default(0)->after('total_financial_budget');
            $table->decimal('remaining_financial_budget', 12, 2)->default(0)->after('disbursed_financial_amount');
        });

        // Create program financial allocations table
        Schema::create('program_financial_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidy_program_id')->constrained('subsidy_programs')->onDelete('cascade');
            $table->foreignId('financial_subsidy_type_id')->constrained('financial_subsidy_types')->onDelete('cascade');
            $table->decimal('amount_per_beneficiary', 10, 2);
            $table->integer('target_beneficiaries');
            $table->decimal('total_allocated_amount', 12, 2); // amount_per_beneficiary * target_beneficiaries
            $table->decimal('disbursed_amount', 12, 2)->default(0);
            $table->decimal('remaining_amount', 12, 2)->default(0);
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->enum('disbursement_method', ['cash', 'check', 'bank_transfer', 'mobile_money'])->default('cash');
            $table->text('allocation_remarks')->nullable();
            $table->foreignId('allocated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['subsidy_program_id', 'financial_subsidy_type_id'], 'unique_program_financial_type');
            
            // Indexes
            $table->index(['subsidy_program_id', 'status']);
        });

        // Enhance program_beneficiary_items to support financial subsidies
        Schema::table('program_beneficiary_items', function (Blueprint $table) {
            $table->enum('item_type', ['inventory', 'financial'])->default('inventory')->after('inventory_id');
            $table->foreignId('financial_subsidy_type_id')->nullable()->after('item_type')->constrained('financial_subsidy_types')->nullOnDelete();
            $table->decimal('financial_amount', 10, 2)->nullable()->after('financial_subsidy_type_id');
            $table->enum('disbursement_method', ['cash', 'check', 'bank_transfer', 'mobile_money'])->nullable()->after('financial_amount');
            $table->string('reference_number')->nullable()->after('disbursement_method'); // Check number, transaction ID, etc.
            $table->boolean('receipt_provided')->default(false)->after('reference_number');
            $table->string('received_by_name')->nullable()->after('receipt_provided'); // Who received the money
            $table->string('received_by_signature')->nullable()->after('received_by_name'); // Signature image path
            
            // Make inventory_id nullable since financial subsidies don't need it
            $table->foreignId('inventory_id')->nullable()->change();
            
            // Add indexes
            $table->index(['item_type', 'disbursement_status']);
            $table->index(['financial_subsidy_type_id', 'disbursement_status']);
        });

        // Create financial disbursement tracking table
        Schema::create('financial_disbursement_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_beneficiary_item_id')->constrained('program_beneficiary_items')->onDelete('cascade');
            $table->foreignId('disbursement_batch_id')->nullable()->constrained('disbursement_batches')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('disbursement_method', ['cash', 'check', 'bank_transfer', 'mobile_money']);
            $table->string('reference_number')->nullable(); // Check number, transaction ID
            $table->date('disbursement_date');
            $table->string('disbursement_location')->nullable();
            $table->string('received_by_name');
            $table->string('received_by_signature')->nullable(); // Image path
            $table->string('witness_name')->nullable();
            $table->string('witness_signature')->nullable(); // Image path
            $table->text('remarks')->nullable();
            $table->foreignId('disbursed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['disbursement_date', 'disbursement_method']);
            $table->index(['disbursement_batch_id', 'disbursement_date']);
        });

        // Create financial subsidy summary table for reporting
        Schema::create('financial_subsidy_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidy_program_id')->constrained('subsidy_programs')->onDelete('cascade');
            $table->foreignId('financial_subsidy_type_id')->constrained('financial_subsidy_types')->onDelete('cascade');
            $table->date('summary_date');
            $table->integer('total_beneficiaries');
            $table->decimal('total_amount_disbursed', 12, 2);
            $table->integer('cash_disbursements')->default(0);
            $table->integer('check_disbursements')->default(0);
            $table->integer('bank_transfer_disbursements')->default(0);
            $table->integer('mobile_money_disbursements')->default(0);
            $table->foreignId('compiled_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['subsidy_program_id', 'financial_subsidy_type_id', 'summary_date'], 'unique_financial_summary');
            
            // Indexes
            $table->index(['summary_date', 'subsidy_program_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('financial_subsidy_summaries');
        Schema::dropIfExists('financial_disbursement_records');
        Schema::dropIfExists('program_financial_allocations');
        Schema::dropIfExists('financial_subsidy_types');
        
        // Remove columns from existing tables
        Schema::table('program_beneficiary_items', function (Blueprint $table) {
            $table->dropForeign(['financial_subsidy_type_id']);
            $table->dropIndex(['item_type', 'disbursement_status']);
            $table->dropIndex(['financial_subsidy_type_id', 'disbursement_status']);
            $table->dropColumn([
                'item_type', 'financial_subsidy_type_id', 'financial_amount', 
                'disbursement_method', 'reference_number', 'receipt_provided',
                'received_by_name', 'received_by_signature'
            ]);
            
            // Make inventory_id required again
            $table->foreignId('inventory_id')->nullable(false)->change();
        });
        
        Schema::table('subsidy_programs', function (Blueprint $table) {
            $table->dropColumn([
                'subsidy_type', 'total_financial_budget', 'disbursed_financial_amount', 'remaining_financial_budget'
            ]);
        });
    }
};