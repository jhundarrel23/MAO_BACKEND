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
        Schema::create('commodities', function (Blueprint $table) {
            $table->id();
            $table->string('commodity_name');
            $table->foreignId('category_id')
                  ->constrained('commodity_categories')
                  ->cascadeOnDelete();
            $table->foreignId('sector_id')
                  ->nullable()
                  ->constrained('sectors')
                  ->nullOnDelete();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commodities');
    }
};
