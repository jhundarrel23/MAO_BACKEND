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
        // Simplify subsidy_programs approval workflow
        Schema::table('subsidy_programs', function (Blueprint $table) {
            // Remove the confusing multiple approval stages
            $table->dropColumn([
                'inventory_allocation_status',
                'inventory_approved_by', 
                'inventory_approved_at',
                'inventory_approval_remarks',
                'distribution_approval_status',
                'distribution_approved_by',
                'distribution_approved_at', 
                'distribution_approval_remarks'
            ]);
            
            // Simplify overall_status to be more logical
            $table->dropColumn('overall_status');
            $table->enum('program_status', [
                'draft',              // Coordinator is still creating
                'submitted',          // Submitted to admin for approval
                'approved',           // Admin approved - ready for distribution  
                'active',             // Currently distributing
                'completed',          // Distribution completed
                'cancelled',          // Programme cancelled
                'denied'              // Programme denied by admin
            ])->default('draft')->after('distribution_approval_remarks');
            
            // Add program readiness check
            $table->boolean('inventory_allocated')->default(false)->after('program_status');
            $table->boolean('beneficiaries_assigned')->default(false)->after('inventory_allocated');
            $table->boolean('ready_for_distribution')->default(false)->after('beneficiaries_assigned');
        });

        // Remove the complex approval history - keep it simple
        Schema::dropIfExists('program_approval_history');
        
        // Create simple approval log instead
        Schema::create('program_approval_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidy_program_id')->constrained('subsidy_programs')->onDelete('cascade');
            $table->enum('action', ['submitted', 'approved', 'denied', 'cancelled']);
            $table->text('remarks')->nullable();
            $table->foreignId('processed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Index
            $table->index(['subsidy_program_id', 'action']);
        });

        // Simplify program_inventory_allocations - remove separate approval
        Schema::table('program_inventory_allocations', function (Blueprint $table) {
            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at', 
                'approval_remarks'
            ]);
            
            // Just track if allocation is active or completed
            $table->enum('allocation_status', ['allocated', 'distributing', 'completed', 'cancelled'])
                  ->default('allocated')
                  ->after('status');
        });

        // Simplify disbursement_batches - remove separate approval  
        Schema::table('disbursement_batches', function (Blueprint $table) {
            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'approval_remarks'
            ]);
            
            // Batch is automatically approved when program is approved
            $table->enum('batch_status', ['planned', 'ongoing', 'completed', 'cancelled'])
                  ->default('planned')
                  ->after('status');
        });

        // Remove the complex admin permissions - keep it simple
        Schema::dropIfExists('admin_approval_permissions');
        
        // Simple admin permissions
        Schema::create('admin_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('can_approve_programs')->default(false);
            $table->decimal('max_budget_limit', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore complex approval system
        Schema::dropIfExists('admin_permissions');
        Schema::dropIfExists('program_approval_log');
        
        // This would be complex to reverse, so we'll keep it simple
        // The enhanced system is still available in the previous migration
        
        Schema::table('subsidy_programs', function (Blueprint $table) {
            $table->dropColumn(['program_status', 'inventory_allocated', 'beneficiaries_assigned', 'ready_for_distribution']);
        });
        
        Schema::table('program_inventory_allocations', function (Blueprint $table) {
            $table->dropColumn('allocation_status');
        });
        
        Schema::table('disbursement_batches', function (Blueprint $table) {
            $table->dropColumn('batch_status');
        });
    }
};