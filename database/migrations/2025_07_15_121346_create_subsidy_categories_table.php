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
       Schema::create('subsidy_categories', function (Blueprint $table) {
    $table->id();
    $table->string('subsidy_name')->unique(); // e.g., 'seed', 'fertilizer', 'fuel'
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subsidy_categories');
    }
};
