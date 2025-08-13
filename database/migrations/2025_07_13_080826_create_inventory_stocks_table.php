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
           Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            $table->integer('quantity'); // positive = stock-in, negative = release
            $table->string('reference')->nullable(); // e.g., DR-2025-001, Prog-Rel-001
            $table->string('source')->nullable();    // e.g., DA Region X, LGU Opol
            $table->date('date_received')->nullable(); // Optional for stock-in only

            // Optional verification logic
            $table->boolean('is_verified')->default(false); // coordinator entries = false
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};
