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
        Schema::create('farmer_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('farm_profile_id')->constrained('farm_profiles')->onDelete('cascade');

    $table->boolean('is_rice')->default(false);
    $table->boolean('is_corn')->default(false);
    $table->boolean('is_other_crops')->default(false);
    $table->string('other_crops_description')->nullable();
    $table->boolean('is_livestock')->default(false);
    $table->string('livestock_description')->nullable();
    $table->boolean('is_poultry')->default(false);
    $table->string('poultry_description')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmer_details');
    }
};
