<?php

namespace App\Services;

use App\Models\SubsidyProgram;
use App\Models\ProgramApprovalLog;
use App\Models\AdminPermission;
use App\Models\ProgramInventoryAllocation;
use App\Models\ProgramBeneficiary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class SimplifiedProgramApprovalService
{
    /**
     * Submit program for admin approval (with inventory allocation included)
     */
    public function submitProgram(int $programId, array $inventoryAllocations, string $remarks = null): array
    {
        try {
            DB::beginTransaction();
            
            $program = SubsidyProgram::findOrFail($programId);
            
            // Check if program can be submitted
            if ($program->program_status !== 'draft') {
                throw new Exception('Program can only be submitted from draft status');
            }
            
            // Validate that program has required information
            if (empty($program->title) || empty($program->description) || !$program->total_budget) {
                throw new Exception('Program must have title, description, and budget before submission');
            }
            
            // Create inventory allocations
            if (!empty($inventoryAllocations)) {
                $inventoryService = new InventoryDisbursementService();
                $inventoryService->allocateInventoryToProgram($programId, $inventoryAllocations);
                $program->update(['inventory_allocated' => true]);
            }
            
            // Update program status
            $program->update([
                'program_status' => 'submitted',
                'submitted_at' => now(),
                'submitted_by' => Auth::id()
            ]);
            
            // Log the submission
            ProgramApprovalLog::create([
                'subsidy_program_id' => $programId,
                'action' => 'submitted',
                'remarks' => $remarks ?? 'Program submitted for admin approval',
                'processed_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Program submitted for admin approval successfully',
                'program_id' => $programId,
                'status' => 'submitted'
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Admin approves or denies the COMPLETE program (including inventory)
     */
    public function processProgram(int $programId, string $action, string $remarks = null): array
    {
        try {
            DB::beginTransaction();
            
            $program = SubsidyProgram::findOrFail($programId);
            
            // Check admin permissions
            if (!$this->canApproveProgram(Auth::id(), $program)) {
                throw new Exception('You do not have permission to approve this program');
            }
            
            // Validate program is in correct status
            if ($program->program_status !== 'submitted') {
                throw new Exception('Program must be in submitted status to be processed');
            }
            
            if ($action === 'approve') {
                // Approve the complete program
                $program->update([
                    'program_status' => 'approved',
                    'approval_status' => 'approved',
                    'is_approved' => true,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'approval_remarks' => $remarks
                ]);
                
                // Automatically activate all inventory allocations
                ProgramInventoryAllocation::where('subsidy_program_id', $programId)
                    ->update(['allocation_status' => 'allocated']);
                
                // Check if program is ready for distribution
                $this->checkProgramReadiness($program);
                
                $message = 'Program approved successfully! Inventory allocated and ready for beneficiary assignment.';
                $status = 'approved';
                
            } else if ($action === 'deny') {
                $program->update([
                    'program_status' => 'denied',
                    'approval_status' => 'denied',
                    'is_approved' => false,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'denial_reason' => $remarks
                ]);
                
                // Cancel all inventory allocations
                ProgramInventoryAllocation::where('subsidy_program_id', $programId)
                    ->update(['allocation_status' => 'cancelled']);
                
                $message = 'Program denied. Inventory allocations cancelled.';
                $status = 'denied';
                
            } else {
                throw new Exception('Invalid action. Must be approve or deny');
            }
            
            // Log the action
            ProgramApprovalLog::create([
                'subsidy_program_id' => $programId,
                'action' => $action === 'approve' ? 'approved' : 'denied',
                'remarks' => $remarks,
                'processed_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => $message,
                'program_id' => $programId,
                'status' => $status
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Start distribution (when coordinator is ready to distribute)
     */
    public function startDistribution(int $programId): array
    {
        try {
            DB::beginTransaction();
            
            $program = SubsidyProgram::findOrFail($programId);
            
            // Check if program is approved and ready
            if ($program->program_status !== 'approved') {
                throw new Exception('Program must be approved before distribution can start');
            }
            
            if (!$program->ready_for_distribution) {
                throw new Exception('Program is not ready for distribution. Please ensure beneficiaries are assigned and inventory is allocated.');
            }
            
            // Update program status
            $program->update(['program_status' => 'active']);
            
            // Update inventory allocations
            ProgramInventoryAllocation::where('subsidy_program_id', $programId)
                ->where('allocation_status', 'allocated')
                ->update(['allocation_status' => 'distributing']);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Distribution started successfully',
                'program_id' => $programId,
                'status' => 'active'
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Complete program distribution
     */
    public function completeProgram(int $programId, string $remarks = null): array
    {
        try {
            DB::beginTransaction();
            
            $program = SubsidyProgram::findOrFail($programId);
            
            // Update program status
            $program->update(['program_status' => 'completed']);
            
            // Update inventory allocations
            ProgramInventoryAllocation::where('subsidy_program_id', $programId)
                ->update(['allocation_status' => 'completed']);
            
            // Log completion
            ProgramApprovalLog::create([
                'subsidy_program_id' => $programId,
                'action' => 'completed',
                'remarks' => $remarks ?? 'Program distribution completed',
                'processed_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Program completed successfully',
                'program_id' => $programId,
                'status' => 'completed'
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get programs pending approval
     */
    public function getProgramsPendingApproval(): array
    {
        return SubsidyProgram::where('program_status', 'submitted')
            ->with(['createdBy', 'inventoryAllocations.inventory'])
            ->orderBy('submitted_at', 'asc')
            ->get()
            ->map(function ($program) {
                return [
                    'id' => $program->id,
                    'title' => $program->title,
                    'description' => $program->description,
                    'total_budget' => $program->total_budget,
                    'target_beneficiaries' => $program->target_beneficiaries,
                    'submitted_at' => $program->submitted_at,
                    'created_by' => $program->createdBy->fname . ' ' . $program->createdBy->lname,
                    'inventory_allocated' => $program->inventory_allocated,
                    'inventory_items' => $program->inventoryAllocations->map(function ($allocation) {
                        return [
                            'item_name' => $allocation->inventory->item_name,
                            'quantity' => $allocation->allocated_quantity,
                            'unit' => $allocation->inventory->unit,
                            'total_cost' => $allocation->total_allocation_cost
                        ];
                    })
                ];
            })
            ->toArray();
    }

    /**
     * Check if program is ready for distribution
     */
    private function checkProgramReadiness(SubsidyProgram $program): void
    {
        $hasInventory = $program->inventory_allocated;
        $hasBeneficiaries = ProgramBeneficiary::where('subsidy_program_id', $program->id)
            ->where('status', 'approved')
            ->exists();
        
        $isReady = $hasInventory && $hasBeneficiaries;
        
        $program->update([
            'beneficiaries_assigned' => $hasBeneficiaries,
            'ready_for_distribution' => $isReady
        ]);
    }

    /**
     * Check if user can approve programs
     */
    private function canApproveProgram(int $userId, SubsidyProgram $program): bool
    {
        $user = \App\Models\User::find($userId);
        
        // Check if user is admin
        if ($user->role !== 'admin') {
            return false;
        }
        
        // Check admin permissions
        $permission = AdminPermission::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
        
        if (!$permission || !$permission->can_approve_programs) {
            return false;
        }
        
        // Check budget limit
        if ($permission->max_budget_limit && $program->total_budget > $permission->max_budget_limit) {
            return false;
        }
        
        return true;
    }

    /**
     * Get program approval history
     */
    public function getApprovalHistory(int $programId): array
    {
        return ProgramApprovalLog::where('subsidy_program_id', $programId)
            ->with('processedBy')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'action' => $log->action,
                    'remarks' => $log->remarks,
                    'processed_by' => $log->processedBy->fname . ' ' . $log->processedBy->lname,
                    'processed_at' => $log->created_at->format('Y-m-d H:i:s')
                ];
            })
            ->toArray();
    }
}