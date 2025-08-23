<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('item_name', 150);
            $table->string('unit', 50);
            $table->enum('item_type', ['seed', 'fertilizer', 'pesticide', 'equipment', 'fuel', 'cash', 'other']);
            $table->enum('assistance_category', ['physical', 'monetary', 'service']);
            $table->boolean('is_trackable_stock')->default(true);
            $table->decimal('unit_value', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};