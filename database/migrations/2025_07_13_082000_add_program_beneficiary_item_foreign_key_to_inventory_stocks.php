<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add foreign key constraint to inventory_stocks.program_beneficiary_item_id after program_beneficiary_items table is created
     */
    public function up(): void
    {
        Schema::table('inventory_stocks', function (Blueprint $table) {
            $table->foreign('program_beneficiary_item_id')->references('id')->on('program_beneficiary_items')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_stocks', function (Blueprint $table) {
            $table->dropForeign(['program_beneficiary_item_id']);
        });
    }
};