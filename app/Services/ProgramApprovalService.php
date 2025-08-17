<?php

namespace App\Services;

use App\Models\SubsidyProgram;
use App\Models\ProgramApprovalHistory;
use App\Models\AdminApprovalPermission;
use App\Models\ProgramInventoryAllocation;
use App\Models\DisbursementBatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class ProgramApprovalService
{
    /**
     * Submit program for approval
     */
    public function submitProgramForApproval(int $programId, string $remarks = null): array
    {
        try {
            DB::beginTransaction();
            
            $program = SubsidyProgram::findOrFail($programId);
            
            // Check if program can be submitted
            if ($program->overall_status !== 'draft') {
                throw new Exception('Program can only be submitted from draft status');
            }
            
            // Update program status
            $program->update([
                'overall_status' => 'submitted',
                'submitted_at' => now(),
                'submitted_by' => Auth::id()
            ]);
            
            // Record in approval history
            $this->recordApprovalHistory($programId, 'program', 'submitted', null, 'pending', $remarks);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Program submitted for approval successfully',
                'program_id' => $programId,
                'status' => 'submitted'
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve/Deny program by admin
     */
    public function processProgramApproval(int $programId, string $action, string $remarks = null): array
    {
        try {
            DB::beginTransaction();
            
            $program = SubsidyProgram::findOrFail($programId);
            
            // Check admin permissions
            if (!$this->canApproveProgram(Auth::id(), $program)) {
                throw new Exception('You do not have permission to approve this program');
            }
            
            // Validate action
            if (!in_array($action, ['approve', 'deny'])) {
                throw new Exception('Invalid action. Must be approve or deny');
            }
            
            $previousStatus = $program->approval_status;
            
            if ($action === 'approve') {
                $program->update([
                    'approval_status' => 'approved',
                    'is_approved' => true,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'approval_remarks' => $remarks,
                    'overall_status' => 'program_approved'
                ]);
                $newStatus = 'approved';
                $message = 'Program approved successfully';
            } else {
                $program->update([
                    'approval_status' => 'denied',
                    'is_approved' => false,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'denial_reason' => $remarks,
                    'overall_status' => 'denied'
                ]);
                $newStatus = 'denied';
                $message = 'Program denied';
            }
            
            // Record in approval history
            $this->recordApprovalHistory($programId, 'program', $action === 'approve' ? 'approved' : 'denied', $previousStatus, $newStatus, $remarks);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => $message,
                'program_id' => $programId,
                'status' => $newStatus
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve inventory allocation
     */
    public function approveInventoryAllocation(int $programId, string $action, string $remarks = null): array
    {
        try {
            DB::beginTransaction();
            
            $program = SubsidyProgram::findOrFail($programId);
            
            // Check if program is approved first
            if ($program->approval_status !== 'approved') {
                throw new Exception('Program must be approved before inventory allocation can be processed');
            }
            
            // Check admin permissions
            if (!$this->canApproveInventory(Auth::id(), $program)) {
                throw new Exception('You do not have permission to approve inventory allocation');
            }
            
            $previousStatus = $program->inventory_allocation_status;
            
            if ($action === 'approve') {
                $program->update([
                    'inventory_allocation_status' => 'approved',
                    'inventory_approved_by' => Auth::id(),
                    'inventory_approved_at' => now(),
                    'inventory_approval_remarks' => $remarks,
                    'overall_status' => 'inventory_approved'
                ]);
                
                // Also approve all inventory allocations for this program
                ProgramInventoryAllocation::where('subsidy_program_id', $programId)
                    ->update([
                        'approval_status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                        'approval_remarks' => $remarks ?? 'Bulk approved with program'
                    ]);
                
                $newStatus = 'approved';
                $message = 'Inventory allocation approved successfully';
            } else {
                $program->update([
                    'inventory_allocation_status' => 'denied',
                    'inventory_approved_by' => Auth::id(),
                    'inventory_approved_at' => now(),
                    'inventory_approval_remarks' => $remarks,
                    'overall_status' => 'denied'
                ]);
                
                $newStatus = 'denied';
                $message = 'Inventory allocation denied';
            }
            
            // Record in approval history
            $this->recordApprovalHistory($programId, 'inventory', $action === 'approve' ? 'approved' : 'denied', $previousStatus, $newStatus, $remarks);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => $message,
                'program_id' => $programId,
                'status' => $newStatus
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve distribution (final approval to start distribution)
     */
    public function approveDistribution(int $programId, string $action, string $remarks = null): array
    {
        try {
            DB::beginTransaction();
            
            $program = SubsidyProgram::findOrFail($programId);
            
            // Check if inventory is approved first
            if ($program->inventory_allocation_status !== 'approved') {
                throw new Exception('Inventory allocation must be approved before distribution can be processed');
            }
            
            // Check admin permissions
            if (!$this->canApproveDistribution(Auth::id(), $program)) {
                throw new Exception('You do not have permission to approve distribution');
            }
            
            $previousStatus = $program->distribution_approval_status;
            
            if ($action === 'approve') {
                $program->update([
                    'distribution_approval_status' => 'approved',
                    'distribution_approved_by' => Auth::id(),
                    'distribution_approved_at' => now(),
                    'distribution_approval_remarks' => $remarks,
                    'overall_status' => 'ready_for_distribution'
                ]);
                $newStatus = 'approved';
                $message = 'Distribution approved successfully. Program is ready for distribution.';
            } else {
                $program->update([
                    'distribution_approval_status' => 'denied',
                    'distribution_approved_by' => Auth::id(),
                    'distribution_approved_at' => now(),
                    'distribution_approval_remarks' => $remarks,
                    'overall_status' => 'denied'
                ]);
                $newStatus = 'denied';
                $message = 'Distribution denied';
            }
            
            // Record in approval history
            $this->recordApprovalHistory($programId, 'distribution', $action === 'approve' ? 'approved' : 'denied', $previousStatus, $newStatus, $remarks);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => $message,
                'program_id' => $programId,
                'status' => $newStatus
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve disbursement batch
     */
    public function approveDisbursementBatch(int $batchId, string $action, string $remarks = null): array
    {
        try {
            DB::beginTransaction();
            
            $batch = DisbursementBatch::findOrFail($batchId);
            $program = $batch->subsidyProgram;
            
            // Check if program is ready for distribution
            if ($program->overall_status !== 'ready_for_distribution' && $program->overall_status !== 'distributing') {
                throw new Exception('Program must be approved for distribution before batch can be processed');
            }
            
            // Check admin permissions
            if (!$this->canApproveDistribution(Auth::id(), $program)) {
                throw new Exception('You do not have permission to approve disbursement batches');
            }
            
            if ($action === 'approve') {
                $batch->update([
                    'approval_status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'approval_remarks' => $remarks,
                    'status' => 'planned'
                ]);
                
                // Update program status to distributing if first batch
                if ($program->overall_status === 'ready_for_distribution') {
                    $program->update(['overall_status' => 'distributing']);
                }
                
                $message = 'Disbursement batch approved successfully';
            } else {
                $batch->update([
                    'approval_status' => 'denied',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'approval_remarks' => $remarks,
                    'status' => 'cancelled'
                ]);
                $message = 'Disbursement batch denied';
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => $message,
                'batch_id' => $batchId,
                'status' => $action === 'approve' ? 'approved' : 'denied'
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get programs pending approval
     */
    public function getProgramsPendingApproval(string $approvalType = 'all'): array
    {
        $query = SubsidyProgram::with(['createdBy', 'approvedBy']);
        
        switch ($approvalType) {
            case 'program':
                $query->where('approval_status', 'pending')
                      ->where('overall_status', 'submitted');
                break;
            case 'inventory':
                $query->where('approval_status', 'approved')
                      ->where('inventory_allocation_status', 'pending');
                break;
            case 'distribution':
                $query->where('inventory_allocation_status', 'approved')
                      ->where('distribution_approval_status', 'pending');
                break;
            default:
                $query->whereIn('overall_status', ['submitted', 'program_approved', 'inventory_approved']);
        }
        
        return $query->orderBy('submitted_at', 'asc')->get()->toArray();
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
        
        // Check approval permissions
        $permission = AdminApprovalPermission::where('user_id', $userId)
            ->where('approval_type', 'program')
            ->where('is_active', true)
            ->first();
        
        if (!$permission) {
            // Check if they have 'all' permissions
            $permission = AdminApprovalPermission::where('user_id', $userId)
                ->where('approval_type', 'all')
                ->where('is_active', true)
                ->first();
        }
        
        if (!$permission) {
            return false;
        }
        
        // Check budget limit
        if ($permission->max_budget_limit && $program->total_budget > $permission->max_budget_limit) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if user can approve inventory
     */
    private function canApproveInventory(int $userId, SubsidyProgram $program): bool
    {
        $user = \App\Models\User::find($userId);
        
        if ($user->role !== 'admin') {
            return false;
        }
        
        $permission = AdminApprovalPermission::where('user_id', $userId)
            ->whereIn('approval_type', ['inventory', 'all'])
            ->where('is_active', true)
            ->first();
        
        return $permission !== null;
    }

    /**
     * Check if user can approve distribution
     */
    private function canApproveDistribution(int $userId, SubsidyProgram $program): bool
    {
        $user = \App\Models\User::find($userId);
        
        if ($user->role !== 'admin') {
            return false;
        }
        
        $permission = AdminApprovalPermission::where('user_id', $userId)
            ->whereIn('approval_type', ['distribution', 'all'])
            ->where('is_active', true)
            ->first();
        
        return $permission !== null;
    }

    /**
     * Record approval history
     */
    private function recordApprovalHistory(int $programId, string $approvalType, string $action, ?string $previousStatus, string $newStatus, ?string $remarks): void
    {
        ProgramApprovalHistory::create([
            'subsidy_program_id' => $programId,
            'approval_type' => $approvalType,
            'action' => $action,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'remarks' => $remarks,
            'processed_by' => Auth::id(),
            'processed_at' => now()
        ]);
    }

    /**
     * Get approval history for a program
     */
    public function getApprovalHistory(int $programId): array
    {
        return ProgramApprovalHistory::where('subsidy_program_id', $programId)
            ->with('processedBy')
            ->orderBy('processed_at', 'desc')
            ->get()
            ->toArray();
    }
}