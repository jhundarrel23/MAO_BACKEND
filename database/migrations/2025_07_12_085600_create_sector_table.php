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
       Schema::create('sector', function (Blueprint $table) {
    $table->id();
    $table->string('sector_name'); 
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->unsignedBigInteger('created_by')->nullable();
    $table->timestamps();

    $table->softDeletes(); // ✅ This enables soft deletes (adds deleted_at column)

    $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section');
    }
};
