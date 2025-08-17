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
        // Enhance subsidy_programs approval workflow
        Schema::table('subsidy_programs', function (Blueprint $table) {
            // Add more detailed approval fields
            $table->text('approval_remarks')->nullable()->after('approved_at');
            $table->text('denial_reason')->nullable()->after('approval_remarks');
            
            // Add inventory allocation approval (separate from program approval)
            $table->enum('inventory_allocation_status', ['pending', 'approved', 'denied'])->default('pending')->after('denial_reason');
            $table->foreignId('inventory_approved_by')->nullable()->after('inventory_allocation_status')->constrained('users')->nullOnDelete();
            $table->timestamp('inventory_approved_at')->nullable()->after('inventory_approved_by');
            $table->text('inventory_approval_remarks')->nullable()->after('inventory_approved_at');
            
            // Add distribution approval (final approval to start distribution)
            $table->enum('distribution_approval_status', ['pending', 'approved', 'denied'])->default('pending')->after('inventory_approval_remarks');
            $table->foreignId('distribution_approved_by')->nullable()->after('distribution_approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('distribution_approved_at')->nullable()->after('distribution_approved_by');
            $table->text('distribution_approval_remarks')->nullable()->after('distribution_approved_at');
            
            // Add overall program status that considers all approvals
            $table->enum('overall_status', [
                'draft',           // Program created but not submitted
                'submitted',       // Submitted for approval
                'program_approved', // Program concept approved
                'inventory_approved', // Inventory allocation approved  
                'ready_for_distribution', // All approvals done, ready to distribute
                'distributing',    // Currently distributing
                'completed',       // Distribution completed
                'cancelled',       // Programme cancelled
                'denied'          // Programme denied
            ])->default('draft')->after('distribution_approval_remarks');
            
            // Add submission tracking
            $table->timestamp('submitted_at')->nullable()->after('overall_status');
            $table->foreignId('submitted_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            
            // Add indexes for better performance
            $table->index(['approval_status', 'overall_status']);
            $table->index(['inventory_allocation_status', 'distribution_approval_status']);
        });

        // Create program approval history table for audit trail
        Schema::create('program_approval_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidy_program_id')->constrained('subsidy_programs')->onDelete('cascade');
            $table->enum('approval_type', ['program', 'inventory', 'distribution']);
            $table->enum('action', ['submitted', 'approved', 'denied', 'returned_for_revision']);
            $table->enum('previous_status', ['pending', 'approved', 'denied'])->nullable();
            $table->enum('new_status', ['pending', 'approved', 'denied']);
            $table->text('remarks')->nullable();
            $table->foreignId('processed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('processed_at');
            $table->timestamps();
            
            // Indexes
            $table->index(['subsidy_program_id', 'approval_type']);
            $table->index(['processed_by', 'processed_at']);
        });

        // Enhance program_inventory_allocations with approval tracking
        Schema::table('program_inventory_allocations', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'denied'])->default('pending')->after('status');
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_remarks')->nullable()->after('approved_at');
            
            // Add index
            $table->index(['approval_status', 'status']);
        });

        // Enhance disbursement_batches with approval tracking
        Schema::table('disbursement_batches', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'denied'])->default('pending')->after('status');
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_remarks')->nullable()->after('approved_at');
            
            // Add index
            $table->index(['approval_status', 'status']);
        });

        // Create admin approval permissions table
        Schema::create('admin_approval_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('approval_type', ['program', 'inventory', 'distribution', 'all']);
            $table->decimal('max_budget_limit', 12, 2)->nullable(); // Maximum budget they can approve
            $table->boolean('can_approve_livestock', false)->default(false);
            $table->boolean('can_approve_seeds', false)->default(true);
            $table->boolean('can_approve_fertilizers', false)->default(true);
            $table->boolean('can_approve_tools', false)->default(true);
            $table->boolean('is_active')->default(true);
            $table->foreignId('granted_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['user_id', 'approval_type']);
            
            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index(['approval_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('admin_approval_permissions');
        Schema::dropIfExists('program_approval_history');
        
        // Remove columns from existing tables
        Schema::table('disbursement_batches', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['approval_status', 'status']);
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at', 'approval_remarks']);
        });
        
        Schema::table('program_inventory_allocations', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['approval_status', 'status']);
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at', 'approval_remarks']);
        });
        
        Schema::table('subsidy_programs', function (Blueprint $table) {
            $table->dropForeign(['inventory_approved_by']);
            $table->dropForeign(['distribution_approved_by']);
            $table->dropForeign(['submitted_by']);
            $table->dropIndex(['approval_status', 'overall_status']);
            $table->dropIndex(['inventory_allocation_status', 'distribution_approval_status']);
            $table->dropColumn([
                'approval_remarks', 'denial_reason', 'inventory_allocation_status',
                'inventory_approved_by', 'inventory_approved_at', 'inventory_approval_remarks',
                'distribution_approval_status', 'distribution_approved_by', 'distribution_approved_at',
                'distribution_approval_remarks', 'overall_status', 'submitted_at', 'submitted_by'
            ]);
        });
    }
};