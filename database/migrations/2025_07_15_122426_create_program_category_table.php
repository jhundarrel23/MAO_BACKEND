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
        Schema::create('program_category', function (Blueprint $table) {
    $table->id();
    $table->foreignId('subsidy_program_id')->constrained('subsidy_programs')->onDelete('cascade');
    $table->foreignId('subsidy_category_id')->constrained('subsidy_categories')->onDelete('cascade');
    $table->timestamps();

    $table->unique(['subsidy_program_id', 'subsidy_category_id']); // Prevent duplicate tagging
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_category');
    }
};
