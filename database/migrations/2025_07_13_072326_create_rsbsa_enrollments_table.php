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
        Schema::create('rsbsa_enrollments', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
    $table->foreignId('farm_profile_id')->constrained()->onDelete('cascade'); 

    $table->string('reference_code')->unique(); 

    // Status tracking
    $table->enum('status', ['pending', 'verifying', 'verified', 'rejected'])->default('pending');

    $table->timestamp('submitted_at')->nullable();
    $table->timestamp('verified_at')->nullable(); 
    $table->text('rejection_reason')->nullable();

    // Who processed the application
    $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete(); 

    $table->timestamps();
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rsbsa_enrollments');
    }
};
