<?php

namespace App\Services;

use App\Models\SubsidyProgram;
use App\Models\FinancialSubsidyType;
use App\Models\ProgramFinancialAllocation;
use App\Models\ProgramBeneficiaryItem;
use App\Models\FinancialDisbursementRecord;
use App\Models\DisbursementBatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class FinancialSubsidyService
{
    /**
     * Create financial subsidy types (setup)
     */
    public function createFinancialSubsidyType(array $data): FinancialSubsidyType
    {
        return FinancialSubsidyType::create([
            'type_name' => $data['type_name'],
            'description' => $data['description'] ?? null,
            'default_amount' => $data['default_amount'] ?? null,
            'disbursement_method' => $data['disbursement_method'] ?? 'cash',
            'requires_receipt' => $data['requires_receipt'] ?? true,
            'created_by' => Auth::id()
        ]);
    }

    /**
     * Allocate financial subsidies to a program
     */
    public function allocateFinancialSubsidiesToProgram(int $programId, array $allocations): array
    {
        try {
            DB::beginTransaction();
            
            $program = SubsidyProgram::findOrFail($programId);
            $results = [];
            $totalFinancialBudget = 0;
            
            foreach ($allocations as $allocation) {
                $subsidyType = FinancialSubsidyType::findOrFail($allocation['financial_subsidy_type_id']);
                $amountPerBeneficiary = $allocation['amount_per_beneficiary'];
                $targetBeneficiaries = $allocation['target_beneficiaries'];
                $totalAmount = $amountPerBeneficiary * $targetBeneficiaries;
                
                // Create or update financial allocation
                $financialAllocation = ProgramFinancialAllocation::updateOrCreate(
                    [
                        'subsidy_program_id' => $programId,
                        'financial_subsidy_type_id' => $subsidyType->id
                    ],
                    [
                        'amount_per_beneficiary' => $amountPerBeneficiary,
                        'target_beneficiaries' => $targetBeneficiaries,
                        'total_allocated_amount' => $totalAmount,
                        'remaining_amount' => $totalAmount,
                        'disbursement_method' => $allocation['disbursement_method'] ?? $subsidyType->disbursement_method,
                        'allocated_by' => Auth::id(),
                        'status' => 'active'
                    ]
                );
                
                $totalFinancialBudget += $totalAmount;
                
                $results[] = [
                    'financial_subsidy_type_id' => $subsidyType->id,
                    'type_name' => $subsidyType->type_name,
                    'amount_per_beneficiary' => $amountPerBeneficiary,
                    'target_beneficiaries' => $targetBeneficiaries,
                    'total_amount' => $totalAmount
                ];
            }
            
            // Update program financial budget and type
            $currentSubsidyType = $program->subsidy_type;
            $newSubsidyType = $currentSubsidyType === 'inventory_only' ? 'mixed' : 
                            ($currentSubsidyType === 'financial_only' ? 'financial_only' : 'mixed');
            
            $program->update([
                'subsidy_type' => $newSubsidyType,
                'total_financial_budget' => $program->total_financial_budget + $totalFinancialBudget,
                'remaining_financial_budget' => $program->remaining_financial_budget + $totalFinancialBudget
            ]);
            
            DB::commit();
            return $results;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Disburse financial subsidies to beneficiaries
     */
    public function disburseFinancialSubsidies(int $batchId, array $disbursements): array
    {
        try {
            DB::beginTransaction();
            
            $batch = DisbursementBatch::findOrFail($batchId);
            $results = [];
            $totalAmount = 0;
            
            foreach ($disbursements as $disbursement) {
                $beneficiaryItem = ProgramBeneficiaryItem::findOrFail($disbursement['program_beneficiary_item_id']);
                $amount = $disbursement['amount'];
                
                // Validate financial allocation
                $financialAllocation = ProgramFinancialAllocation::where('subsidy_program_id', $batch->subsidy_program_id)
                    ->where('financial_subsidy_type_id', $disbursement['financial_subsidy_type_id'])
                    ->first();
                
                if (!$financialAllocation || $financialAllocation->remaining_amount < $amount) {
                    throw new Exception("Insufficient financial allocation for this subsidy type");
                }
                
                // Update beneficiary item
                $beneficiaryItem->update([
                    'item_type' => 'financial',
                    'financial_subsidy_type_id' => $disbursement['financial_subsidy_type_id'],
                    'financial_amount' => $amount,
                    'disbursement_method' => $disbursement['disbursement_method'],
                    'reference_number' => $disbursement['reference_number'] ?? null,
                    'received_by_name' => $disbursement['received_by_name'],
                    'disbursement_status' => 'released',
                    'disbursement_batch_id' => $batchId,
                    'released_at' => now(),
                    'released_by' => Auth::id(),
                    'disbursement_remarks' => $disbursement['remarks'] ?? null
                ]);
                
                // Create financial disbursement record
                $disbursementRecord = FinancialDisbursementRecord::create([
                    'program_beneficiary_item_id' => $beneficiaryItem->id,
                    'disbursement_batch_id' => $batchId,
                    'amount' => $amount,
                    'disbursement_method' => $disbursement['disbursement_method'],
                    'reference_number' => $disbursement['reference_number'] ?? null,
                    'disbursement_date' => $disbursement['disbursement_date'] ?? today(),
                    'disbursement_location' => $disbursement['disbursement_location'] ?? null,
                    'received_by_name' => $disbursement['received_by_name'],
                    'received_by_signature' => $disbursement['received_by_signature'] ?? null,
                    'witness_name' => $disbursement['witness_name'] ?? null,
                    'witness_signature' => $disbursement['witness_signature'] ?? null,
                    'remarks' => $disbursement['remarks'] ?? null,
                    'disbursed_by' => Auth::id()
                ]);
                
                // Update financial allocation
                $financialAllocation->increment('disbursed_amount', $amount);
                $financialAllocation->decrement('remaining_amount', $amount);
                
                if ($financialAllocation->remaining_amount <= 0) {
                    $financialAllocation->update(['status' => 'completed']);
                }
                
                $totalAmount += $amount;
                
                $results[] = [
                    'beneficiary_item_id' => $beneficiaryItem->id,
                    'financial_subsidy_type_id' => $disbursement['financial_subsidy_type_id'],
                    'amount' => $amount,
                    'disbursement_method' => $disbursement['disbursement_method'],
                    'reference_number' => $disbursement['reference_number'] ?? null,
                    'received_by' => $disbursement['received_by_name']
                ];
            }
            
            // Update program financial amounts
            $program = SubsidyProgram::findOrFail($batch->subsidy_program_id);
            $program->increment('disbursed_financial_amount', $totalAmount);
            $program->update(['remaining_financial_budget' => $program->total_financial_budget - $program->disbursed_financial_amount]);
            
            DB::commit();
            return $results;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get available financial subsidy types
     */
    public function getAvailableFinancialSubsidyTypes(): array
    {
        return FinancialSubsidyType::where('is_active', true)
            ->orderBy('type_name')
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'type_name' => $type->type_name,
                    'description' => $type->description,
                    'default_amount' => $type->default_amount,
                    'disbursement_method' => $type->disbursement_method,
                    'requires_receipt' => $type->requires_receipt
                ];
            })
            ->toArray();
    }

    /**
     * Get program financial allocation summary
     */
    public function getProgramFinancialAllocationSummary(int $programId): array
    {
        $allocations = ProgramFinancialAllocation::where('subsidy_program_id', $programId)
            ->with('financialSubsidyType')
            ->get();
        
        return $allocations->map(function ($allocation) {
            return [
                'financial_subsidy_type_id' => $allocation->financial_subsidy_type_id,
                'type_name' => $allocation->financialSubsidyType->type_name,
                'amount_per_beneficiary' => $allocation->amount_per_beneficiary,
                'target_beneficiaries' => $allocation->target_beneficiaries,
                'total_allocated_amount' => $allocation->total_allocated_amount,
                'disbursed_amount' => $allocation->disbursed_amount,
                'remaining_amount' => $allocation->remaining_amount,
                'disbursement_method' => $allocation->disbursement_method,
                'status' => $allocation->status
            ];
        })->toArray();
    }

    /**
     * Generate financial disbursement report
     */
    public function generateFinancialDisbursementReport(int $programId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = FinancialDisbursementRecord::whereHas('programBeneficiaryItem.programBeneficiary', function ($q) use ($programId) {
            $q->whereHas('subsidyProgram', function ($sq) use ($programId) {
                $sq->where('id', $programId);
            });
        })->with([
            'programBeneficiaryItem.programBeneficiary.user',
            'programBeneficiaryItem.financialSubsidyType',
            'disbursedBy'
        ]);
        
        if ($startDate) {
            $query->whereDate('disbursement_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('disbursement_date', '<=', $endDate);
        }
        
        $records = $query->orderBy('disbursement_date', 'desc')->get();
        
        return [
            'total_records' => $records->count(),
            'total_amount' => $records->sum('amount'),
            'disbursement_methods' => $records->groupBy('disbursement_method')->map->count(),
            'records' => $records->map(function ($record) {
                return [
                    'disbursement_date' => $record->disbursement_date->format('Y-m-d'),
                    'beneficiary_name' => $record->programBeneficiaryItem->programBeneficiary->user->fname . ' ' . 
                                        $record->programBeneficiaryItem->programBeneficiary->user->lname,
                    'subsidy_type' => $record->programBeneficiaryItem->financialSubsidyType->type_name,
                    'amount' => $record->amount,
                    'disbursement_method' => $record->disbursement_method,
                    'reference_number' => $record->reference_number,
                    'received_by' => $record->received_by_name,
                    'disbursed_by' => $record->disbursedBy->fname . ' ' . $record->disbursedBy->lname,
                    'location' => $record->disbursement_location
                ];
            })
        ];
    }

    /**
     * Create mixed program (both inventory and financial)
     */
    public function createMixedProgram(int $programId, array $inventoryAllocations, array $financialAllocations): array
    {
        try {
            DB::beginTransaction();
            
            $program = SubsidyProgram::findOrFail($programId);
            
            // Allocate inventory items
            $inventoryResults = [];
            if (!empty($inventoryAllocations)) {
                $inventoryService = new InventoryDisbursementService();
                $inventoryResults = $inventoryService->allocateInventoryToProgram($programId, $inventoryAllocations);
            }
            
            // Allocate financial subsidies
            $financialResults = [];
            if (!empty($financialAllocations)) {
                $financialResults = $this->allocateFinancialSubsidiesToProgram($programId, $financialAllocations);
            }
            
            // Update program type
            $subsidyType = 'mixed';
            if (empty($inventoryAllocations)) {
                $subsidyType = 'financial_only';
            } elseif (empty($financialAllocations)) {
                $subsidyType = 'inventory_only';
            }
            
            $program->update(['subsidy_type' => $subsidyType]);
            
            DB::commit();
            
            return [
                'success' => true,
                'subsidy_type' => $subsidyType,
                'inventory_allocations' => $inventoryResults,
                'financial_allocations' => $financialResults
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}