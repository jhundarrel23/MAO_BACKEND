<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryCurrentStock;
use App\Models\InventoryMovement;
use App\Models\ProgramBeneficiaryItem;
use App\Models\ProgramInventoryAllocation;
use App\Models\DisbursementBatch;
use App\Models\SubsidyProgram;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class InventoryDisbursementService
{
    /**
     * Allocate inventory items to a subsidy program
     */
    public function allocateInventoryToProgram(int $programId, array $allocations): array
    {
        try {
            DB::beginTransaction();
            
            $program = SubsidyProgram::findOrFail($programId);
            $results = [];
            
            foreach ($allocations as $allocation) {
                $inventory = Inventory::findOrFail($allocation['inventory_id']);
                $quantity = $allocation['quantity'];
                
                // Check if item is subsidizable
                if (!$inventory->is_subsidizable) {
                    throw new Exception("Item '{$inventory->item_name}' is not available for subsidy distribution");
                }
                
                // Check available stock
                $currentStock = $this->getCurrentStock($inventory->id);
                if ($currentStock['available_stock'] < $quantity) {
                    throw new Exception("Insufficient stock for '{$inventory->item_name}'. Available: {$currentStock['available_stock']}, Requested: {$quantity}");
                }
                
                // Create or update allocation
                $programAllocation = ProgramInventoryAllocation::updateOrCreate(
                    [
                        'subsidy_program_id' => $programId,
                        'inventory_id' => $inventory->id
                    ],
                    [
                        'allocated_quantity' => $quantity,
                        'remaining_quantity' => $quantity,
                        'unit_cost' => $inventory->unit_cost,
                        'total_allocation_cost' => $quantity * $inventory->unit_cost,
                        'allocated_by' => Auth::id(),
                        'status' => 'active'
                    ]
                );
                
                // Reserve stock
                $this->reserveStock($inventory->id, $quantity);
                
                $results[] = [
                    'inventory_id' => $inventory->id,
                    'item_name' => $inventory->item_name,
                    'allocated_quantity' => $quantity,
                    'unit_cost' => $inventory->unit_cost,
                    'total_cost' => $quantity * $inventory->unit_cost
                ];
            }
            
            // Update program allocated budget
            $totalAllocatedCost = collect($results)->sum('total_cost');
            $program->increment('allocated_budget', $totalAllocatedCost);
            
            DB::commit();
            return $results;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create disbursement batch
     */
    public function createDisbursementBatch(array $data): DisbursementBatch
    {
        return DisbursementBatch::create([
            'batch_number' => $this->generateBatchNumber(),
            'subsidy_program_id' => $data['subsidy_program_id'],
            'disbursement_date' => $data['disbursement_date'],
            'location' => $data['location'],
            'total_beneficiaries' => 0,
            'total_items_distributed' => 0,
            'total_value_distributed' => 0,
            'batch_coordinator' => Auth::id(),
            'batch_remarks' => $data['remarks'] ?? null
        ]);
    }

    /**
     * Disburse items to beneficiaries
     */
    public function disburseItemsToBeneficiaries(int $batchId, array $disbursements): array
    {
        try {
            DB::beginTransaction();
            
            $batch = DisbursementBatch::findOrFail($batchId);
            $batch->update(['status' => 'ongoing']);
            
            $results = [];
            $totalItems = 0;
            $totalValue = 0;
            
            foreach ($disbursements as $disbursement) {
                $beneficiaryItem = ProgramBeneficiaryItem::findOrFail($disbursement['program_beneficiary_item_id']);
                $inventory = Inventory::findOrFail($disbursement['inventory_id']);
                $quantity = $disbursement['quantity'];
                
                // Validate stock availability
                $currentStock = $this->getCurrentStock($inventory->id);
                if ($currentStock['available_stock'] < $quantity) {
                    throw new Exception("Insufficient stock for '{$inventory->item_name}' for beneficiary item #{$beneficiaryItem->id}");
                }
                
                // Update beneficiary item
                $beneficiaryItem->update([
                    'inventory_id' => $inventory->id,
                    'item_name' => $inventory->item_name,
                    'unit' => $inventory->unit,
                    'quantity' => $quantity,
                    'unit_cost' => $inventory->unit_cost,
                    'total_cost' => $quantity * $inventory->unit_cost,
                    'disbursement_status' => 'released',
                    'disbursement_batch_id' => $batchId,
                    'released_at' => now(),
                    'released_by' => Auth::id(),
                    'disbursement_remarks' => $disbursement['remarks'] ?? null
                ]);
                
                // Record stock movement
                $this->recordStockMovement([
                    'inventory_id' => $inventory->id,
                    'movement_type' => 'disbursement',
                    'quantity' => -$quantity, // negative for outgoing
                    'unit_cost' => $inventory->unit_cost,
                    'reference_type' => 'program_disbursement',
                    'reference_id' => $beneficiaryItem->id,
                    'reference_number' => $batch->batch_number,
                    'remarks' => "Disbursed to beneficiary via batch {$batch->batch_number}"
                ]);
                
                // Update program allocation
                $this->updateProgramAllocation($batch->subsidy_program_id, $inventory->id, $quantity);
                
                $totalItems += $quantity;
                $totalValue += $quantity * $inventory->unit_cost;
                
                $results[] = [
                    'beneficiary_item_id' => $beneficiaryItem->id,
                    'inventory_id' => $inventory->id,
                    'item_name' => $inventory->item_name,
                    'quantity' => $quantity,
                    'unit_cost' => $inventory->unit_cost,
                    'total_cost' => $quantity * $inventory->unit_cost
                ];
            }
            
            // Update batch totals
            $batch->update([
                'total_beneficiaries' => count($disbursements),
                'total_items_distributed' => $totalItems,
                'total_value_distributed' => $totalValue,
                'status' => 'completed'
            ]);
            
            // Update program disbursed amount
            $program = SubsidyProgram::findOrFail($batch->subsidy_program_id);
            $program->increment('disbursed_amount', $totalValue);
            $program->increment('actual_beneficiaries', count($disbursements));
            $program->update(['remaining_budget' => $program->total_budget - $program->disbursed_amount]);
            
            DB::commit();
            return $results;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get current stock levels for an inventory item
     */
    public function getCurrentStock(int $inventoryId): array
    {
        $stock = InventoryCurrentStock::where('inventory_id', $inventoryId)->first();
        
        if (!$stock) {
            // Create initial stock record if doesn't exist
            $stock = InventoryCurrentStock::create([
                'inventory_id' => $inventoryId,
                'current_stock' => 0,
                'reserved_stock' => 0,
                'available_stock' => 0,
                'total_value' => 0
            ]);
        }
        
        return [
            'current_stock' => $stock->current_stock,
            'reserved_stock' => $stock->reserved_stock,
            'available_stock' => $stock->available_stock,
            'total_value' => $stock->total_value
        ];
    }

    /**
     * Add stock to inventory
     */
    public function addStock(int $inventoryId, int $quantity, array $data = []): void
    {
        $inventory = Inventory::findOrFail($inventoryId);
        
        // Record stock movement
        $this->recordStockMovement([
            'inventory_id' => $inventoryId,
            'movement_type' => 'stock_in',
            'quantity' => $quantity,
            'unit_cost' => $data['unit_cost'] ?? $inventory->unit_cost,
            'reference_type' => $data['reference_type'] ?? 'manual_adjustment',
            'reference_id' => $data['reference_id'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
            'remarks' => $data['remarks'] ?? 'Stock added'
        ]);
    }

    /**
     * Reserve stock for program allocation
     */
    private function reserveStock(int $inventoryId, int $quantity): void
    {
        $stock = InventoryCurrentStock::where('inventory_id', $inventoryId)->first();
        
        if ($stock) {
            $stock->increment('reserved_stock', $quantity);
            $stock->update(['available_stock' => $stock->current_stock - $stock->reserved_stock]);
        }
    }

    /**
     * Record inventory movement
     */
    private function recordStockMovement(array $data): void
    {
        $inventory = Inventory::findOrFail($data['inventory_id']);
        $currentStock = $this->getCurrentStock($data['inventory_id']);
        
        $newBalance = $currentStock['current_stock'] + $data['quantity'];
        $totalCost = abs($data['quantity']) * ($data['unit_cost'] ?? $inventory->unit_cost);
        
        // Create movement record
        InventoryMovement::create([
            'inventory_id' => $data['inventory_id'],
            'movement_type' => $data['movement_type'],
            'quantity' => $data['quantity'],
            'balance_after' => $newBalance,
            'unit_cost' => $data['unit_cost'] ?? $inventory->unit_cost,
            'total_cost' => $totalCost,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'reference_number' => $data['reference_number'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'processed_by' => Auth::id(),
            'movement_date' => now()
        ]);
        
        // Update current stock
        $stockRecord = InventoryCurrentStock::where('inventory_id', $data['inventory_id'])->first();
        $stockRecord->update([
            'current_stock' => $newBalance,
            'available_stock' => $newBalance - $stockRecord->reserved_stock,
            'total_value' => $newBalance * $inventory->unit_cost,
            'last_movement_at' => now()
        ]);
    }

    /**
     * Update program allocation after disbursement
     */
    private function updateProgramAllocation(int $programId, int $inventoryId, int $quantity): void
    {
        $allocation = ProgramInventoryAllocation::where('subsidy_program_id', $programId)
            ->where('inventory_id', $inventoryId)
            ->first();
        
        if ($allocation) {
            $allocation->increment('distributed_quantity', $quantity);
            $allocation->decrement('remaining_quantity', $quantity);
            
            // Mark as completed if fully distributed
            if ($allocation->remaining_quantity <= 0) {
                $allocation->update(['status' => 'completed']);
            }
        }
        
        // Release reserved stock
        $stock = InventoryCurrentStock::where('inventory_id', $inventoryId)->first();
        if ($stock) {
            $stock->decrement('reserved_stock', $quantity);
            $stock->update(['available_stock' => $stock->current_stock - $stock->reserved_stock]);
        }
    }

    /**
     * Generate unique batch number
     */
    private function generateBatchNumber(): string
    {
        $date = now()->format('Ymd');
        $sequence = DisbursementBatch::whereDate('created_at', today())->count() + 1;
        return "BATCH-{$date}-" . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get inventory items available for subsidy
     */
    public function getAvailableInventoryForSubsidy(): array
    {
        return Inventory::where('is_subsidizable', true)
            ->where('status', 'active')
            ->with('currentStock')
            ->get()
            ->map(function ($inventory) {
                $stock = $this->getCurrentStock($inventory->id);
                return [
                    'id' => $inventory->id,
                    'item_code' => $inventory->item_code,
                    'item_name' => $inventory->item_name,
                    'unit' => $inventory->unit,
                    'unit_cost' => $inventory->unit_cost,
                    'current_stock' => $stock['current_stock'],
                    'available_stock' => $stock['available_stock'],
                    'reserved_stock' => $stock['reserved_stock']
                ];
            })
            ->toArray();
    }

    /**
     * Get program allocation summary
     */
    public function getProgramAllocationSummary(int $programId): array
    {
        $allocations = ProgramInventoryAllocation::where('subsidy_program_id', $programId)
            ->with('inventory')
            ->get();
        
        return $allocations->map(function ($allocation) {
            return [
                'inventory_id' => $allocation->inventory_id,
                'item_name' => $allocation->inventory->item_name,
                'allocated_quantity' => $allocation->allocated_quantity,
                'distributed_quantity' => $allocation->distributed_quantity,
                'remaining_quantity' => $allocation->remaining_quantity,
                'unit_cost' => $allocation->unit_cost,
                'total_cost' => $allocation->total_allocation_cost,
                'status' => $allocation->status
            ];
        })->toArray();
    }
}