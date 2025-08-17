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
       Schema::create('inventories', function (Blueprint $table) {
    $table->id();
    $table->string('item_name'); // e.g. "Rice Seed", "14-14-14 Fertilizer", "Fuel Subsidy", "Cash Assistance"
    $table->string('unit');      // e.g. "kg", "bag", "head", "liters", "PHP"
    $table->enum('item_type', ['seed', 'fertilizer', 'pesticide', 'equipment', 'fuel', 'cash', 'other']); // Enhanced types
    $table->enum('assistance_category', ['physical', 'monetary', 'service']); // New field to categorize assistance
    $table->boolean('is_trackable_stock')->default(true); // False for services or one-time assistance
    $table->decimal('unit_value', 10, 2)->nullable(); // Value per unit in PHP for budgeting
    $table->text('description')->nullable(); // Additional details about the item
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
