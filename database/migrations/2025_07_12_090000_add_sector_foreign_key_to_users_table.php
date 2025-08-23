<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add foreign key constraint to users.sector_id after sectors table is created
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('sector_id')->references('id')->on('sectors')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
        });
    }
};