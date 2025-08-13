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
       Schema::create('farm_parcels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_profile_id')->constrained('farm_profiles')->onDelete('cascade');

            $table->string('parcel_number')->nullable();
            $table->string('barangay');
            $table->decimal('farm_area', 10, 2);

            // Tenure / Ownership
            $table->enum('tenure_type', ['registered_owner', 'tenant', 'lessee']);
            $table->string('landowner_name')->nullable(); // only if tenant/lessee
            $table->string('ownership_document_number')->nullable();

            // Legal and classification
            $table->boolean('is_ancestral_domain')->default(false);
            $table->boolean('is_agrarian_reform_beneficiary')->default(false);

            // Removed commodity_id (handled by pivot table now)
            $table->enum('farm_type', ['irrigated', 'rainfed upland', 'rainfed lowland']);
            $table->boolean('is_organic_practitioner')->default(false);

            $table->text('remarks')->nullable();
            $table->timestamps();
});




    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_parcels');
    }
};
