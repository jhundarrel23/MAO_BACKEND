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
        // Enhance beneficiary_profiles with missing RSBSA fields
        Schema::table('beneficiary_profiles', function (Blueprint $table) {
            // Household Information
            $table->integer('total_household_members')->default(1)->after('is_household_head');
            $table->integer('household_members_working_farm')->default(1)->after('total_household_members');
            $table->decimal('annual_family_income', 12, 2)->nullable()->after('household_members_working_farm');
            $table->enum('income_classification', ['below_poverty', 'low_income', 'middle_income'])->nullable()->after('annual_family_income');
            
            // Farming Experience
            $table->integer('years_farming_experience')->nullable()->after('income_classification');
            $table->enum('main_livelihood', ['farming', 'fishing', 'farm_labor', 'agri_business', 'mixed'])->nullable()->after('years_farming_experience');
            
            // Training and Capacity Building
            $table->boolean('attended_agricultural_training')->default(false)->after('main_livelihood');
            $table->text('training_programs_attended')->nullable()->after('attended_agricultural_training');
            $table->integer('training_count_last_3_years')->default(0)->after('training_programs_attended');
            
            // Financial Services
            $table->boolean('has_bank_account')->default(false)->after('training_count_last_3_years');
            $table->string('bank_name')->nullable()->after('has_bank_account');
            $table->boolean('has_insurance')->default(false)->after('bank_name');
            $table->string('insurance_type')->nullable()->after('has_insurance');
            
            // Market Access
            $table->enum('main_market_outlet', ['local_market', 'traders', 'cooperatives', 'direct_consumers', 'agri_companies'])->nullable()->after('insurance_type');
            $table->decimal('distance_to_market_km', 5, 2)->nullable()->after('main_market_outlet');
            
            // Technology Adoption
            $table->boolean('uses_improved_seeds')->default(false)->after('distance_to_market_km');
            $table->boolean('uses_organic_fertilizer')->default(false)->after('uses_improved_seeds');
            $table->boolean('uses_chemical_fertilizer')->default(false)->after('uses_organic_fertilizer');
            $table->boolean('uses_pesticides')->default(false)->after('uses_chemical_fertilizer');
            $table->boolean('has_farm_machinery')->default(false)->after('uses_pesticides');
            $table->text('farm_machinery_owned')->nullable()->after('has_farm_machinery');
        });

        // Create comprehensive RSBSA crop production table
        Schema::create('rsbsa_crop_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rsbsa_enrollment_id')->constrained('rsbsa_enrollments')->onDelete('cascade');
            $table->foreignId('commodity_id')->constrained('commodities')->onDelete('cascade');
            
            // Area and Production
            $table->decimal('area_planted_hectares', 8, 4);
            $table->decimal('volume_produced_tons', 10, 2)->nullable();
            $table->decimal('average_yield_tons_per_hectare', 6, 2)->nullable();
            
            // Farming Season
            $table->enum('farming_season', ['wet_season', 'dry_season', 'year_round']);
            $table->integer('crop_year');
            $table->date('planting_date')->nullable();
            $table->date('harvest_date')->nullable();
            
            // Irrigation and Technology
            $table->enum('irrigation_type', ['irrigated', 'rainfed', 'communal', 'pump']);
            $table->enum('seed_type', ['hybrid', 'inbred', 'traditional', 'certified']);
            $table->boolean('uses_contract_farming')->default(false);
            $table->string('buyer_contractor')->nullable();
            
            // Economics
            $table->decimal('total_production_cost', 10, 2)->nullable();
            $table->decimal('selling_price_per_kg', 8, 2)->nullable();
            $table->decimal('gross_income', 12, 2)->nullable();
            $table->decimal('net_income', 12, 2)->nullable();
            
            $table->timestamps();
            
            // Composite index for efficient querying
            $table->index(['rsbsa_enrollment_id', 'commodity_id', 'crop_year']);
        });

        // Create RSBSA livestock production table
        Schema::create('rsbsa_livestock_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rsbsa_enrollment_id')->constrained('rsbsa_enrollments')->onDelete('cascade');
            $table->string('livestock_type'); // cattle, carabao, goat, swine, chicken, etc.
            
            $table->integer('number_of_heads');
            $table->enum('purpose', ['meat', 'dairy', 'eggs', 'breeding', 'draft_power', 'mixed']);
            $table->enum('housing_type', ['traditional', 'improved', 'intensive', 'free_range']);
            
            // Production Data
            $table->decimal('annual_production_kg', 10, 2)->nullable(); // meat/milk/eggs
            $table->decimal('selling_price_per_kg', 8, 2)->nullable();
            $table->decimal('annual_gross_income', 12, 2)->nullable();
            $table->decimal('annual_production_cost', 12, 2)->nullable();
            
            // Health and Management
            $table->boolean('has_veterinary_care')->default(false);
            $table->boolean('uses_feeds_supplement')->default(false);
            $table->boolean('has_insurance_coverage')->default(false);
            
            $table->timestamps();
        });

        // Create RSBSA aquaculture production table
        Schema::create('rsbsa_aquaculture_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rsbsa_enrollment_id')->constrained('rsbsa_enrollments')->onDelete('cascade');
            $table->string('fish_species');
            
            $table->decimal('pond_area_hectares', 8, 4);
            $table->enum('culture_type', ['pond', 'cage', 'pen', 'rice_fish']);
            $table->enum('water_source', ['river', 'spring', 'artesian_well', 'irrigation']);
            
            // Production Data
            $table->integer('stocking_density_per_hectare');
            $table->integer('production_cycles_per_year');
            $table->decimal('average_harvest_kg_per_cycle', 10, 2)->nullable();
            $table->decimal('selling_price_per_kg', 8, 2)->nullable();
            $table->decimal('annual_gross_income', 12, 2)->nullable();
            $table->decimal('annual_production_cost', 12, 2)->nullable();
            
            // Technology and Feed
            $table->boolean('uses_commercial_feeds')->default(false);
            $table->boolean('uses_natural_feeds')->default(false);
            $table->boolean('has_aeration_system')->default(false);
            
            $table->timestamps();
        });

        // Create RSBSA document requirements tracking
        Schema::create('rsbsa_document_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rsbsa_enrollment_id')->constrained('rsbsa_enrollments')->onDelete('cascade');
            
            // Required Documents Checklist
            $table->boolean('has_id_photo')->default(false);
            $table->boolean('has_government_id')->default(false);
            $table->boolean('has_birth_certificate')->default(false);
            $table->boolean('has_proof_of_address')->default(false);
            
            // Land Documents
            $table->boolean('has_land_title')->default(false);
            $table->boolean('has_tax_declaration')->default(false);
            $table->boolean('has_deed_of_sale')->default(false);
            $table->boolean('has_tenancy_contract')->default(false);
            $table->boolean('has_barangay_certification')->default(false);
            
            // Association Documents
            $table->boolean('has_coop_membership')->default(false);
            $table->boolean('has_farmers_group_membership')->default(false);
            
            // Verification Status
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            
            $table->timestamps();
        });

        // Add RSBSA enrollment enhancements
        Schema::table('rsbsa_enrollments', function (Blueprint $table) {
            // Enrollment Period Tracking
            $table->string('enrollment_period')->after('reference_code'); // e.g., "2024-2025"
            $table->enum('enrollment_type', ['new', 'renewal', 'update'])->default('new')->after('enrollment_period');
            $table->string('previous_rsbsa_number')->nullable()->after('enrollment_type');
            
            // Processing Information
            $table->enum('application_method', ['online', 'manual', 'mobile_app'])->default('manual')->after('previous_rsbsa_number');
            $table->foreignId('encoded_by')->nullable()->after('application_method')->constrained('users')->nullOnDelete();
            $table->timestamp('encoding_date')->nullable()->after('encoded_by');
            
            // Approval Workflow
            $table->foreignId('reviewed_by')->nullable()->after('verified_by')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->foreignId('approved_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            
            // RSBSA Card Information
            $table->boolean('card_printed')->default(false)->after('approved_at');
            $table->timestamp('card_printed_at')->nullable()->after('card_printed');
            $table->boolean('card_released')->default(false)->after('card_printed_at');
            $table->timestamp('card_released_at')->nullable()->after('card_released');
            $table->string('card_received_by')->nullable()->after('card_released_at');
            
            // Renewal Tracking
            $table->date('registration_expires_at')->nullable()->after('card_received_by');
            $table->boolean('needs_renewal')->default(false)->after('registration_expires_at');
            
            // Add indexes for better performance
            $table->index(['enrollment_period', 'status']);
            $table->index(['enrollment_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('rsbsa_document_requirements');
        Schema::dropIfExists('rsbsa_aquaculture_productions');
        Schema::dropIfExists('rsbsa_livestock_productions');
        Schema::dropIfExists('rsbsa_crop_productions');
        
        // Remove columns from rsbsa_enrollments
        Schema::table('rsbsa_enrollments', function (Blueprint $table) {
            $table->dropIndex(['enrollment_period', 'status']);
            $table->dropIndex(['enrollment_type', 'status']);
            $table->dropColumn([
                'enrollment_period', 'enrollment_type', 'previous_rsbsa_number',
                'application_method', 'encoded_by', 'encoding_date',
                'reviewed_by', 'reviewed_at', 'approved_by', 'approved_at',
                'card_printed', 'card_printed_at', 'card_released', 'card_released_at',
                'card_received_by', 'registration_expires_at', 'needs_renewal'
            ]);
        });
        
        // Remove columns from beneficiary_profiles
        Schema::table('beneficiary_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'total_household_members', 'household_members_working_farm', 'annual_family_income',
                'income_classification', 'years_farming_experience', 'main_livelihood',
                'attended_agricultural_training', 'training_programs_attended', 'training_count_last_3_years',
                'has_bank_account', 'bank_name', 'has_insurance', 'insurance_type',
                'main_market_outlet', 'distance_to_market_km',
                'uses_improved_seeds', 'uses_organic_fertilizer', 'uses_chemical_fertilizer',
                'uses_pesticides', 'has_farm_machinery', 'farm_machinery_owned'
            ]);
        });
    }
};