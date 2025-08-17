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
        Schema::create('inventory_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            $table->string('reservation_code')->unique(); // e.g., "RSV-2025-001"
            
            // Reservation details
            $table->decimal('reserved_quantity', 10, 2);
            $table->string('unit');
            $table->enum('reservation_type', ['distribution', 'transfer', 'adjustment', 'other'])->default('distribution');
            
            // Connection to distribution system
            $table->foreignId('distribution_package_id')->nullable()
                  ->constrained('distribution_packages')
                  ->nullOnDelete();
            
            $table->foreignId('program_beneficiary_item_id')->nullable()
                  ->constrained('program_beneficiary_items')
                  ->nullOnDelete();
            
            $table->foreignId('subsidy_program_id')->nullable()
                  ->constrained('subsidy_programs')
                  ->nullOnDelete();
            
            // Reservation status and timing
            $table->enum('status', ['active', 'fulfilled', 'cancelled', 'expired'])->default('active');
            $table->timestamp('reserved_at');
            $table->timestamp('expires_at')->nullable(); // Auto-cancel if not fulfilled by this date
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // User tracking
            $table->foreignId('reserved_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('fulfilled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Additional details
            $table->text('reservation_notes')->nullable();
            $table->string('batch_numbers')->nullable(); // Specific batches reserved
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['inventory_id', 'status']);
            $table->index(['distribution_package_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_reservations');
    }
};