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
        // Create subsidy calculation rules table
        Schema::create('subsidy_calculation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidy_program_id')->constrained('subsidy_programs')->onDelete('cascade');
            $table->foreignId('inventory_id')->nullable()->constrained('inventories')->nullOnDelete();
            $table->foreignId('financial_subsidy_type_id')->nullable()->constrained('financial_subsidy_types')->nullOnDelete();
            $table->enum('calculation_method', ['per_hectare', 'per_farmer', 'per_commodity', 'sliding_scale']);
            
            // Per hectare calculation
            $table->decimal('quantity_per_hectare', 8, 4)->nullable(); // e.g., 1.5 bags per hectare
            $table->decimal('amount_per_hectare', 10, 2)->nullable();   // e.g., ₱2,000 per hectare
            
            // Minimum and maximum limits
            $table->decimal('minimum_quantity', 8, 4)->nullable();      // e.g., 0.5 bags minimum
            $table->decimal('maximum_quantity', 8, 4)->nullable();      // e.g., 10 bags maximum
            $table->decimal('minimum_amount', 10, 2)->nullable();       // e.g., ₱1,000 minimum
            $table->decimal('maximum_amount', 10, 2)->nullable();       // e.g., ₱20,000 maximum
            
            // Sliding scale rules (JSON format for complex rules)
            $table->json('sliding_scale_rules')->nullable(); // For complex calculations
            
            // Conditions
            $table->decimal('min_farm_size_hectares', 8, 4)->nullable(); // Minimum farm size to qualify
            $table->decimal('max_farm_size_hectares', 8, 4)->nullable(); // Maximum farm size eligible
            $table->enum('farm_type_eligible', ['all', 'irrigated', 'rainfed_upland', 'rainfed_lowland'])->default('all');
            $table->enum('tenure_eligible', ['all', 'registered_owner', 'tenant', 'lessee'])->default('all');
            
            $table->text('calculation_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['subsidy_program_id', 'calculation_method']);
            $table->index(['inventory_id', 'is_active']);
            $table->index(['financial_subsidy_type_id', 'is_active']);
        });

        // Enhance program_beneficiaries to include farm size calculation
        Schema::table('program_beneficiaries', function (Blueprint $table) {
            $table->decimal('beneficiary_farm_size_hectares', 8, 4)->nullable()->after('commodity_id');
            $table->enum('beneficiary_farm_type', ['irrigated', 'rainfed_upland', 'rainfed_lowland'])->nullable()->after('beneficiary_farm_size_hectares');
            $table->enum('beneficiary_tenure_type', ['registered_owner', 'tenant', 'lessee'])->nullable()->after('beneficiary_farm_type');
            $table->boolean('eligible_for_subsidy')->default(true)->after('beneficiary_tenure_type');
            $table->text('eligibility_notes')->nullable()->after('eligible_for_subsidy');
        });

        // Enhance program_beneficiary_items with calculated quantities
        Schema::table('program_beneficiary_items', function (Blueprint $table) {
            $table->decimal('calculated_quantity', 10, 4)->nullable()->after('quantity'); // System calculated quantity
            $table->decimal('calculated_amount', 10, 2)->nullable()->after('calculated_quantity'); // System calculated amount
            $table->decimal('approved_quantity', 10, 4)->nullable()->after('calculated_amount'); // Final approved quantity (may be adjusted)
            $table->decimal('approved_amount', 10, 2)->nullable()->after('approved_quantity'); // Final approved amount
            $table->text('calculation_notes')->nullable()->after('approved_amount');
            $table->foreignId('calculation_rule_id')->nullable()->after('calculation_notes')->constrained('subsidy_calculation_rules')->nullOnDelete();
            
            // Add index
            $table->index(['calculation_rule_id', 'disbursement_status']);
        });

        // Create beneficiary farm size summary table (for quick access)
        Schema::create('beneficiary_farm_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('commodity_id')->constrained('commodities')->onDelete('cascade');
            $table->decimal('total_farm_area_hectares', 8, 4);
            $table->decimal('irrigated_area_hectares', 8, 4)->default(0);
            $table->decimal('rainfed_upland_area_hectares', 8, 4)->default(0);
            $table->decimal('rainfed_lowland_area_hectares', 8, 4)->default(0);
            $table->integer('number_of_parcels');
            $table->enum('primary_tenure_type', ['registered_owner', 'tenant', 'lessee']);
            $table->boolean('has_organic_certification')->default(false);
            $table->date('last_updated');
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['user_id', 'commodity_id']);
            
            // Indexes
            $table->index(['commodity_id', 'total_farm_area_hectares']);
            $table->index(['primary_tenure_type', 'total_farm_area_hectares']);
        });

        // Create subsidy eligibility checks table
        Schema::create('subsidy_eligibility_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_beneficiary_id')->constrained('program_beneficiaries')->onDelete('cascade');
            $table->foreignId('calculation_rule_id')->constrained('subsidy_calculation_rules')->onDelete('cascade');
            $table->boolean('meets_farm_size_requirement')->default(false);
            $table->boolean('meets_farm_type_requirement')->default(false);
            $table->boolean('meets_tenure_requirement')->default(false);
            $table->boolean('overall_eligible')->default(false);
            $table->text('eligibility_details')->nullable();
            $table->timestamp('checked_at');
            $table->foreignId('checked_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['program_beneficiary_id', 'overall_eligible']);
            $table->index(['calculation_rule_id', 'overall_eligible']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('subsidy_eligibility_checks');
        Schema::dropIfExists('beneficiary_farm_summaries');
        Schema::dropIfExists('subsidy_calculation_rules');
        
        // Remove columns from existing tables
        Schema::table('program_beneficiary_items', function (Blueprint $table) {
            $table->dropForeign(['calculation_rule_id']);
            $table->dropIndex(['calculation_rule_id', 'disbursement_status']);
            $table->dropColumn([
                'calculated_quantity', 'calculated_amount', 'approved_quantity', 
                'approved_amount', 'calculation_notes', 'calculation_rule_id'
            ]);
        });
        
        Schema::table('program_beneficiaries', function (Blueprint $table) {
            $table->dropColumn([
                'beneficiary_farm_size_hectares', 'beneficiary_farm_type', 'beneficiary_tenure_type',
                'eligible_for_subsidy', 'eligibility_notes'
            ]);
        });
    }
};