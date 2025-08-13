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
       Schema::create('fisherfolk_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('farm_profile_id')->constrained('farm_profiles')->onDelete('cascade');

    $table->boolean('is_fish_capture')->default(false);
    $table->boolean('is_aquaculture')->default(false);
    $table->boolean('is_gleaning')->default(false);
    $table->boolean('is_fish_processing')->default(false);
    $table->string('other_fishing_description')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fisherfolk_details');
    }
};
