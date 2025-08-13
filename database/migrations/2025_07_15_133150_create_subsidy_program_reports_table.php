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
       Schema::create('subsidy_program_reports', function (Blueprint $table) {
    $table->id();

    $table->foreignId('subsidy_program_id')
          ->constrained('subsidy_programs')
          ->onDelete('cascade');

    $table->integer('total_beneficiaries')->default(0);
    $table->decimal('total_quantity_distributed', 10, 2)->default(0.00);
    $table->string('unit')->nullable(); // bags, sacks, liters, PHP

    $table->integer('total_barangays')->nullable();
    $table->text('coordinator_remarks')->nullable();  // optional notes or observations

    $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');
    $table->timestamp('submitted_at')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subsidy_program_reports');
    }
};
