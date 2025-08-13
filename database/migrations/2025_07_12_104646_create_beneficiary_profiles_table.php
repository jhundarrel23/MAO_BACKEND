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
       Schema::create('beneficiary_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->integer('SYSTEM_GENERATED_RSBSA_NUMBER')->nullable();
    $table->integer('RSBSA_NUMBER')->nullable();

    $table->string('barangay');
    $table->string('municipality')->default('Opol');
    $table->string('province')->default('Misamis Oriental');
    $table->string('region')->default('X');

    // Contact
    $table->string('contact_number');

    // Personal Info
    $table->date('birth_date');
    $table->string('place_of_birth');
    $table->enum('sex', ['male', 'female']);
    $table->string('civil_status')->nullable(); // e.g., single, married, etc.
    $table->string('name_of_spouse')->nullable();

    // Education
    $table->enum('highest_education', [
        'None',
        'Pre-school',
        'Elementary',
        'Junior High School',
        'Senior High School',
        'Vocational',
        'College',
        'Post Graduate'
    ])->nullable();

    // Religion & PWD
    $table->string('religion')->nullable();
    $table->boolean('is_pwd')->default(false);

    // ID Information
    $table->enum('has_government_id', ['yes', 'no'])->default('no');
    $table->string('gov_id_type')->nullable();
    $table->string('gov_id_number')->nullable();

    // Association Membership
    $table->enum('is_association_member', ['yes', 'no'])->default('no');
    $table->string('association_name')->nullable();

    // Household Head
    $table->string('mothers_maiden_name')->nullable();
    $table->boolean('is_household_head')->default(false);
    $table->string('household_head_name')->nullable();

    // Emergency Contact
    $table->integer('emergency_contact_number')->nullable();



    $table->timestamps();
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_profiles');
    }
};
