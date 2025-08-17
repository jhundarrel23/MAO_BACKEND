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
        // Add image columns to beneficiary_profiles
        Schema::table('beneficiary_profiles', function (Blueprint $table) {
            $table->string('profile_photo')->nullable()->after('emergency_contact_number');
            $table->string('government_id_photo')->nullable()->after('profile_photo');
            $table->string('signature_photo')->nullable()->after('government_id_photo');
        });

        // Add image columns to farm_parcels
        Schema::table('farm_parcels', function (Blueprint $table) {
            $table->string('ownership_document_photo')->nullable()->after('ownership_document_number');
            $table->string('farm_location_photo')->nullable()->after('ownership_document_photo');
            $table->string('farm_sketch_map')->nullable()->after('farm_location_photo');
        });

        // Add image columns to subsidy_programs
        Schema::table('subsidy_programs', function (Blueprint $table) {
            $table->string('program_banner')->nullable()->after('description');
            $table->json('program_photos')->nullable()->after('program_banner'); // For multiple photos
        });

        // Add image columns to inventories
        Schema::table('inventories', function (Blueprint $table) {
            $table->string('item_photo')->nullable()->after('item_type');
        });

        // Add image columns to program_beneficiary_items (for distribution photos)
        Schema::table('program_beneficiary_items', function (Blueprint $table) {
            $table->string('distribution_photo')->nullable()->after('released_by');
            $table->string('beneficiary_signature')->nullable()->after('distribution_photo');
        });

        // Add image columns to rsbsa_enrollments
        Schema::table('rsbsa_enrollments', function (Blueprint $table) {
            $table->json('supporting_documents')->nullable()->after('rejection_reason'); // For multiple document photos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiary_profiles', function (Blueprint $table) {
            $table->dropColumn(['profile_photo', 'government_id_photo', 'signature_photo']);
        });

        Schema::table('farm_parcels', function (Blueprint $table) {
            $table->dropColumn(['ownership_document_photo', 'farm_location_photo', 'farm_sketch_map']);
        });

        Schema::table('subsidy_programs', function (Blueprint $table) {
            $table->dropColumn(['program_banner', 'program_photos']);
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('item_photo');
        });

        Schema::table('program_beneficiary_items', function (Blueprint $table) {
            $table->dropColumn(['distribution_photo', 'beneficiary_signature']);
        });

        Schema::table('rsbsa_enrollments', function (Blueprint $table) {
            $table->dropColumn('supporting_documents');
        });
    }
};