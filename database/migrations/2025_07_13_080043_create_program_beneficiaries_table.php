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
      Schema::create('program_beneficiaries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('subsidy_program_id')->constrained('subsidy_programs')->onDelete('cascade');
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // RSBA farmer
    $table->foreignId('commodity_id')->constrained('commodities')->onDelete('cascade');
    $table->enum('status', ['pending', 'approved', 'released', 'rejected'])->default('pending');
    $table->timestamp('approved_at')->nullable();
    $table->timestamp('released_at')->nullable();
    $table->text('remarks')->nullable();
    $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete(); // coordinator/admin
    $table->timestamps();
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_beneficiaries');
    }
};
