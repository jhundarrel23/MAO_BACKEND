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
     Schema::create('subsidy_programs', function (Blueprint $table) {
            $table->id();

            $table->string('title'); // e.g., "Corn Seed and Fertilizer Program 2025"
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Status of the program: lifecycle control
            $table->enum('status', ['pending', 'ongoing', 'completed', 'cancelled'])->default('pending');

            // Admin approval tracking
            $table->boolean('is_approved')->default(false);
            $table->enum('approval_status', ['pending', 'approved', 'denied'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();


            // Creator (usually the coordinator)
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subsidy_programs');
    }
};
