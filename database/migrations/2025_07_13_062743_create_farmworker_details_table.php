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
       Schema::create('farmworker_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('farm_profile_id')->constrained('farm_profiles')->onDelete('cascade');

    $table->boolean('is_land_preparation')->default(false);
    $table->boolean('is_planting')->default(false);
    $table->boolean('is_cultivation')->default(false);
    $table->boolean('is_harvesting')->default(false);
    $table->string('other_work_description')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmworker_details');
    }
};
