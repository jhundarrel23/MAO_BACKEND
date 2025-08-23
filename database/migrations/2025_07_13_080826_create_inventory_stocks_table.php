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
            $table->decimal('quantity', 10, 2); // Changed from integer to decimal for precision
            
            // Enhanced movement tracking
            $table->enum('movement_type', ['stock_in', 'stock_out', 'adjustment', 'transfer', 'distribution']);
            
            // Better categorization of stock movements
            $table->enum('transaction_type', [
                'purchase', 'donation', 'return', 'distribution', 
                'damage', 'expired', 'transfer_in', 'transfer_out', 
                'adjustment', 'initial_stock'
            ]);
            
            // Connection to distribution system (foreign key added later)
            $table->unsignedBigInteger('program_beneficiary_item_id')->nullable();
            
            // Enhanced tracking fields
            $table->decimal('unit_cost', 10, 2)->nullable(); // Cost per unit
            $table->decimal('total_value', 12, 2)->nullable(); // Total value of movement
            $table->decimal('running_balance', 12, 2)->default(0.00); // Running stock balance
            
            $table->string('reference')->nullable(); // e.g., DR-2025-001, Prog-Rel-001
            $table->string('source')->nullable(); // e.g., DA Region X, LGU Opol
            $table->string('destination')->nullable(); // Where items went (for stock-out)
            
            // Better date tracking
            $table->date('date_received')->nullable(); // For stock-in
            $table->date('transaction_date'); // Actual transaction date
            $table->string('batch_number')->nullable(); // For batch tracking
            $table->date('expiry_date')->nullable(); // For items with expiry
            
            $table->text('remarks')->nullable(); // Additional notes

            // Approval workflow
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['inventory_id', 'movement_type']);
            $table->index(['status']);
            $table->index(['transaction_date']);
            $table->index(['batch_number']);
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
