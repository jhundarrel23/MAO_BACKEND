<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_beneficiary_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_beneficiary_id')->constrained('program_beneficiaries')->onDelete('cascade');
            $table->string('item_name');
            $table->foreignId('inventory_id')->nullable()->constrained('inventories')->nullOnDelete();
            
            $table->decimal('quantity', 10, 2);
            $table->string('unit');

            // Enhanced tracking fields for coordinator customization
            $table->decimal('suggested_amount', 10, 2)->nullable();
            $table->decimal('coordinator_amount', 10, 2)->nullable();
            $table->text('coordinator_notes')->nullable();

            // Item value tracking
            $table->decimal('unit_value', 10, 2)->nullable();
            $table->decimal('total_value', 10, 2)->nullable();

            // Status tracking
            $table->enum('status', ['pending', 'approved', 'prepared', 'distributed', 'cancelled'])->default('pending');

            // Release tracking
            $table->timestamp('released_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Coordinator approval tracking
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['status']);
            $table->index(['program_beneficiary_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_beneficiary_items');
    }
};