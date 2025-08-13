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
       Schema::create('agri_youth_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('farm_profile_id')->constrained('farm_profiles')->onDelete('cascade');

    // Checkbox flags based on official form
    $table->boolean('is_agri_youth')->default(true);
    $table->boolean('is_part_of_farming_household')->default(false);
    $table->boolean('is_formal_agri_course')->default(false);
    $table->boolean('is_nonformal_agri_course')->default(false);
    $table->boolean('is_agri_program_participant')->default(false);
    $table->string('other_involvement_description')->nullable(); // “Others, specify”

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agri_youth_details');
    }
};
