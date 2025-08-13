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
      Schema::create('program_beneficiary_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('program_beneficiary_id')
                  ->constrained('program_beneficiaries')
                  ->onDelete('cascade');

            $table->string('item_name');            // e.g. "Hybrid Corn Seed"
            $table->decimal('quantity', 10, 2);     // e.g. 2.5
            $table->string('unit');                 // e.g. "bags", "PHP"

            $table->timestamp('released_at')->nullable();
            $table->foreignId('released_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_beneficiary_items');
    }
};
