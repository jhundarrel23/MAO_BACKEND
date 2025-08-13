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
       Schema::create('barangay_production_reports', function (Blueprint $table) {
    $table->id();
    $table->foreignId('barangay_id')->constrained()->onDelete('cascade');
    $table->year('crop_year'); // e.g., 2023
    $table->decimal('area_hectares', 10, 2); // e.g., 14.75

    // Wet cropping
    $table->decimal('wet_hybrid_yield', 5, 2)->nullable();   // e.g. 4.0
    $table->decimal('wet_inbred_yield', 5, 2)->nullable();   // e.g. 4.2

    // Dry cropping
    $table->decimal('dry_hybrid_yield', 5, 2)->nullable();   // e.g. 2.9
    $table->decimal('dry_inbred_yield', 5, 2)->nullable();   // e.g. 3.2

    $table->text('remarks')->nullable(); // e.g., "pest infestation"
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangay_production_reports');
    }
};
