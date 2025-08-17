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
        // Add indexes for better performance
        Schema::table('beneficiary_profiles', function (Blueprint $table) {
            $table->index('barangay');
            $table->index('RSBSA_NUMBER');
            $table->index('municipality');
            $table->index(['sex', 'barangay']); // Composite index for reporting
        });

        Schema::table('program_beneficiaries', function (Blueprint $table) {
            $table->index(['subsidy_program_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::table('farm_parcels', function (Blueprint $table) {
            $table->index('barangay');
            $table->index(['tenure_type', 'farm_type']);
        });

        Schema::table('inventory_stocks', function (Blueprint $table) {
            $table->index(['inventory_id', 'is_verified']);
            $table->index('date_received');
        });

        Schema::table('subsidy_programs', function (Blueprint $table) {
            $table->index(['status', 'approval_status']);
            $table->index(['start_date', 'end_date']);
        });

        // Add audit trail columns to critical tables
        Schema::table('commodity_categories', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('category_name')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        Schema::table('commodities', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('sector_id')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        Schema::table('livelihood_categories', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('livelihood_category_name')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('item_photo')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->decimal('unit_cost', 8, 2)->nullable()->after('updated_by');
            $table->integer('minimum_stock_level')->default(0)->after('unit_cost');
        });

        // Add soft deletes to important tables
        Schema::table('beneficiary_profiles', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('subsidy_programs', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        Schema::table('beneficiary_profiles', function (Blueprint $table) {
            $table->dropIndex(['barangay']);
            $table->dropIndex(['RSBSA_NUMBER']);
            $table->dropIndex(['municipality']);
            $table->dropIndex(['sex', 'barangay']);
            $table->dropSoftDeletes();
        });

        Schema::table('program_beneficiaries', function (Blueprint $table) {
            $table->dropIndex(['subsidy_program_id', 'status']);
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('farm_parcels', function (Blueprint $table) {
            $table->dropIndex(['barangay']);
            $table->dropIndex(['tenure_type', 'farm_type']);
        });

        Schema::table('inventory_stocks', function (Blueprint $table) {
            $table->dropIndex(['inventory_id', 'is_verified']);
            $table->dropIndex(['date_received']);
        });

        Schema::table('subsidy_programs', function (Blueprint $table) {
            $table->dropIndex(['status', 'approval_status']);
            $table->dropIndex(['start_date', 'end_date']);
            $table->dropSoftDeletes();
        });

        // Drop audit trail columns
        Schema::table('commodity_categories', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::table('commodities', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::table('livelihood_categories', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by', 'unit_cost', 'minimum_stock_level']);
            $table->dropSoftDeletes();
        });
    }
};