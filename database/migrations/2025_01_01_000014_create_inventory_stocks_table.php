<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            
            // Enhanced movement tracking
            $table->enum('movement_type', ['stock_in', 'stock_out', 'adjustment', 'transfer', 'distribution']);
            $table->enum('transaction_type', [
                'purchase', 'donation', 'return', 'distribution', 
                'damage', 'expired', 'transfer_in', 'transfer_out', 
                'adjustment', 'initial_stock'
            ]);
            
            // Connection to distribution system (foreign key added later)
            $table->unsignedBigInteger('program_beneficiary_item_id')->nullable();
            
            // Enhanced tracking fields
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_value', 12, 2)->nullable();
            $table->decimal('running_balance', 12, 2)->default(0.00);
            
            $table->string('reference')->nullable();
            $table->string('source')->nullable();
            $table->string('destination')->nullable();
            
            // Better date tracking
            $table->date('date_received')->nullable();
            $table->date('transaction_date');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            
            $table->text('remarks')->nullable();

            // Approval workflow
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['inventory_id', 'movement_type']);
            $table->index(['status']);
            $table->index(['transaction_date']);
            $table->index(['batch_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};