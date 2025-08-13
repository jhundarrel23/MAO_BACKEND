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
        Schema::create('subsidy_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subsidy_program_id')
                  ->constrained('subsidy_programs')
                  ->onDelete('cascade');

            $table->string('item_name');              // e.g. "Hybrid Corn Seed", "Fertilizer", "Fuel Subsidy"
            $table->decimal('quantity', 10, 2);       // Total planned amount or per beneficiary
            $table->string('unit');                   // e.g. "bags", "sacks", "PHP"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subsidy_items');
    }
};
