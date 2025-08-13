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
       Schema::create('municipal_subsidy_summary_reports', function (Blueprint $table) {
    $table->id();
    $table->year('report_year'); // e.g. 2025

    $table->integer('total_programs')->default(0);
    $table->integer('total_barangays_covered')->default(0);
    $table->integer('total_farmers_served')->default(0);
    $table->decimal('total_quantity_distributed', 12, 2)->default(0.00);
    $table->string('unit')->nullable(); // e.g., "bags", "kg", "liters", "PHP"
    $table->decimal('estimated_total_value', 12, 2)->nullable(); // if valued in PHP

    $table->text('summary_notes')->nullable(); // summary by the agri head

    $table->foreignId('compiled_by')->constrained('users')->onDelete('cascade');
    $table->timestamp('compiled_at')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('municipal_subsidy_summary_reports');
    }
};
