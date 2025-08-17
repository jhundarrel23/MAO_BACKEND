<?php

namespace App\Services;

use App\Models\SubsidyProgram;
use App\Models\SubsidyCalculationRule;
use App\Models\ProgramBeneficiary;
use App\Models\ProgramBeneficiaryItem;
use App\Models\BeneficiaryFarmSummary;
use App\Models\FarmParcel;
use App\Models\Inventory;
use App\Models\FinancialSubsidyType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class HectareBasedCalculationService
{
    /**
     * Create calculation rule for a program
     */
    public function createCalculationRule(int $programId, array $ruleData): SubsidyCalculationRule
    {
        return SubsidyCalculationRule::create([
            'subsidy_program_id' => $programId,
            'inventory_id' => $ruleData['inventory_id'] ?? null,
            'financial_subsidy_type_id' => $ruleData['financial_subsidy_type_id'] ?? null,
            'calculation_method' => $ruleData['calculation_method'],
            'quantity_per_hectare' => $ruleData['quantity_per_hectare'] ?? null,
            'amount_per_hectare' => $ruleData['amount_per_hectare'] ?? null,
            'minimum_quantity' => $ruleData['minimum_quantity'] ?? null,
            'maximum_quantity' => $ruleData['maximum_quantity'] ?? null,
            'minimum_amount' => $ruleData['minimum_amount'] ?? null,
            'maximum_amount' => $ruleData['maximum_amount'] ?? null,
            'min_farm_size_hectares' => $ruleData['min_farm_size_hectares'] ?? 0.1,
            'max_farm_size_hectares' => $ruleData['max_farm_size_hectares'] ?? null,
            'farm_type_eligible' => $ruleData['farm_type_eligible'] ?? 'all',
            'tenure_eligible' => $ruleData['tenure_eligible'] ?? 'all',
            'calculation_notes' => $ruleData['calculation_notes'] ?? null,
            'created_by' => Auth::id()
        ]);
    }

    /**
     * Calculate subsidies for all beneficiaries in a program
     */
    public function calculateSubsidiesForProgram(int $programId): array
    {
        try {
            DB::beginTransaction();
            
            $program = SubsidyProgram::findOrFail($programId);
            $beneficiaries = ProgramBeneficiary::where('subsidy_program_id', $programId)
                ->where('status', 'approved')
                ->with(['user.beneficiaryProfile', 'commodity'])
                ->get();
            
            $results = [];
            
            foreach ($beneficiaries as $beneficiary) {
                // Get farmer's total farm size for this commodity
                $farmSize = $this->getFarmerTotalFarmSize($beneficiary->user_id, $beneficiary->commodity_id);
                
                // Update beneficiary with farm size info
                $beneficiary->update([
                    'beneficiary_farm_size_hectares' => $farmSize['total_hectares'],
                    'beneficiary_farm_type' => $farmSize['primary_farm_type'],
                    'beneficiary_tenure_type' => $farmSize['primary_tenure_type']
                ]);
                
                // Calculate subsidies based on farm size
                $calculatedItems = $this->calculateSubsidiesForBeneficiary($beneficiary, $farmSize);
                
                $results[] = [
                    'beneficiary_id' => $beneficiary->id,
                    'farmer_name' => $beneficiary->user->fname . ' ' . $beneficiary->user->lname,
                    'farm_size_hectares' => $farmSize['total_hectares'],
                    'calculated_items' => $calculatedItems
                ];
            }
            
            DB::commit();
            return $results;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate subsidies for individual beneficiary
     */
    private function calculateSubsidiesForBeneficiary(ProgramBeneficiary $beneficiary, array $farmSize): array
    {
        $calculationRules = SubsidyCalculationRule::where('subsidy_program_id', $beneficiary->subsidy_program_id)
            ->where('is_active', true)
            ->get();
        
        $calculatedItems = [];
        
        foreach ($calculationRules as $rule) {
            // Check eligibility
            if (!$this->checkEligibility($beneficiary, $rule, $farmSize)) {
                continue;
            }
            
            $calculatedQuantity = null;
            $calculatedAmount = null;
            
            switch ($rule->calculation_method) {
                case 'per_hectare':
                    $calculatedQuantity = $this->calculatePerHectare($farmSize['total_hectares'], $rule);
                    $calculatedAmount = $this->calculateAmountPerHectare($farmSize['total_hectares'], $rule);
                    break;
                    
                case 'per_farmer':
                    $calculatedQuantity = $rule->quantity_per_hectare; // Fixed amount per farmer
                    $calculatedAmount = $rule->amount_per_hectare;     // Fixed amount per farmer
                    break;
                    
                case 'sliding_scale':
                    $result = $this->calculateSlidingScale($farmSize['total_hectares'], $rule);
                    $calculatedQuantity = $result['quantity'];
                    $calculatedAmount = $result['amount'];
                    break;
            }
            
            // Apply min/max limits
            if ($calculatedQuantity !== null) {
                $calculatedQuantity = $this->applyLimits($calculatedQuantity, $rule->minimum_quantity, $rule->maximum_quantity);
            }
            
            if ($calculatedAmount !== null) {
                $calculatedAmount = $this->applyLimits($calculatedAmount, $rule->minimum_amount, $rule->maximum_amount);
            }
            
            // Create or update beneficiary item
            $beneficiaryItem = ProgramBeneficiaryItem::updateOrCreate(
                [
                    'program_beneficiary_id' => $beneficiary->id,
                    'inventory_id' => $rule->inventory_id,
                    'financial_subsidy_type_id' => $rule->financial_subsidy_type_id
                ],
                [
                    'item_type' => $rule->inventory_id ? 'inventory' : 'financial',
                    'calculated_quantity' => $calculatedQuantity,
                    'calculated_amount' => $calculatedAmount,
                    'approved_quantity' => $calculatedQuantity, // Initially same as calculated
                    'approved_amount' => $calculatedAmount,     // Initially same as calculated
                    'quantity' => $calculatedQuantity ?? 0,
                    'financial_amount' => $calculatedAmount ?? 0,
                    'calculation_rule_id' => $rule->id,
                    'calculation_notes' => "Calculated based on {$farmSize['total_hectares']} hectares using {$rule->calculation_method} method"
                ]
            );
            
            $calculatedItems[] = [
                'rule_id' => $rule->id,
                'item_type' => $rule->inventory_id ? 'inventory' : 'financial',
                'item_name' => $rule->inventory ? $rule->inventory->item_name : $rule->financialSubsidyType->type_name,
                'calculated_quantity' => $calculatedQuantity,
                'calculated_amount' => $calculatedAmount,
                'calculation_method' => $rule->calculation_method,
                'farm_hectares_used' => $farmSize['total_hectares']
            ];
        }
        
        return $calculatedItems;
    }

    /**
     * Get farmer's total farm size for specific commodity
     */
    private function getFarmerTotalFarmSize(int $userId, int $commodityId): array
    {
        // Get all farm parcels for this farmer and commodity
        $farmParcels = FarmParcel::whereHas('farmProfile.beneficiaryProfile', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->whereHas('commodities', function ($query) use ($commodityId) {
            $query->where('commodity_id', $commodityId);
        })
        ->get();
        
        $totalHectares = $farmParcels->sum('farm_area');
        $irrigatedHectares = $farmParcels->where('farm_type', 'irrigated')->sum('farm_area');
        $rainfedUplandHectares = $farmParcels->where('farm_type', 'rainfed_upland')->sum('farm_area');
        $rainfedLowlandHectares = $farmParcels->where('farm_type', 'rainfed_lowland')->sum('farm_area');
        
        // Determine primary farm type and tenure
        $primaryFarmType = $farmParcels->groupBy('farm_type')->sortByDesc(function ($group) {
            return $group->sum('farm_area');
        })->keys()->first() ?? 'rainfed_upland';
        
        $primaryTenureType = $farmParcels->groupBy('tenure_type')->sortByDesc(function ($group) {
            return $group->sum('farm_area');
        })->keys()->first() ?? 'registered_owner';
        
        return [
            'total_hectares' => $totalHectares,
            'irrigated_hectares' => $irrigatedHectares,
            'rainfed_upland_hectares' => $rainfedUplandHectares,
            'rainfed_lowland_hectares' => $rainfedLowlandHectares,
            'number_of_parcels' => $farmParcels->count(),
            'primary_farm_type' => $primaryFarmType,
            'primary_tenure_type' => $primaryTenureType
        ];
    }

    /**
     * Calculate per hectare quantity
     */
    private function calculatePerHectare(float $hectares, SubsidyCalculationRule $rule): ?float
    {
        if ($rule->quantity_per_hectare === null) {
            return null;
        }
        
        return $hectares * $rule->quantity_per_hectare;
    }

    /**
     * Calculate per hectare amount
     */
    private function calculateAmountPerHectare(float $hectares, SubsidyCalculationRule $rule): ?float
    {
        if ($rule->amount_per_hectare === null) {
            return null;
        }
        
        return $hectares * $rule->amount_per_hectare;
    }

    /**
     * Calculate using sliding scale
     */
    private function calculateSlidingScale(float $hectares, SubsidyCalculationRule $rule): array
    {
        // Example sliding scale rules in JSON:
        // {
        //   "0-1": {"quantity": 1.0, "amount": 2000},
        //   "1-3": {"quantity": 1.5, "amount": 2500},
        //   "3-5": {"quantity": 2.0, "amount": 3000},
        //   "5+": {"quantity": 2.5, "amount": 3500}
        // }
        
        $slidingRules = json_decode($rule->sliding_scale_rules, true) ?? [];
        
        foreach ($slidingRules as $range => $values) {
            if ($this->isInRange($hectares, $range)) {
                return [
                    'quantity' => $values['quantity'] ?? null,
                    'amount' => $values['amount'] ?? null
                ];
            }
        }
        
        return ['quantity' => null, 'amount' => null];
    }

    /**
     * Check if hectares fall within range
     */
    private function isInRange(float $hectares, string $range): bool
    {
        if (str_ends_with($range, '+')) {
            $min = (float) str_replace('+', '', $range);
            return $hectares >= $min;
        }
        
        $parts = explode('-', $range);
        if (count($parts) === 2) {
            $min = (float) $parts[0];
            $max = (float) $parts[1];
            return $hectares >= $min && $hectares <= $max;
        }
        
        return false;
    }

    /**
     * Apply minimum and maximum limits
     */
    private function applyLimits(?float $value, ?float $min, ?float $max): ?float
    {
        if ($value === null) {
            return null;
        }
        
        if ($min !== null && $value < $min) {
            return $min;
        }
        
        if ($max !== null && $value > $max) {
            return $max;
        }
        
        return $value;
    }

    /**
     * Check beneficiary eligibility
     */
    private function checkEligibility(ProgramBeneficiary $beneficiary, SubsidyCalculationRule $rule, array $farmSize): bool
    {
        // Check minimum farm size
        if ($rule->min_farm_size_hectares && $farmSize['total_hectares'] < $rule->min_farm_size_hectares) {
            return false;
        }
        
        // Check maximum farm size
        if ($rule->max_farm_size_hectares && $farmSize['total_hectares'] > $rule->max_farm_size_hectares) {
            return false;
        }
        
        // Check farm type eligibility
        if ($rule->farm_type_eligible !== 'all' && $farmSize['primary_farm_type'] !== $rule->farm_type_eligible) {
            return false;
        }
        
        // Check tenure eligibility
        if ($rule->tenure_eligible !== 'all' && $farmSize['primary_tenure_type'] !== $rule->tenure_eligible) {
            return false;
        }
        
        return true;
    }

    /**
     * Get calculation preview for a program
     */
    public function getCalculationPreview(int $programId): array
    {
        $program = SubsidyProgram::findOrFail($programId);
        $rules = SubsidyCalculationRule::where('subsidy_program_id', $programId)->get();
        
        // Sample calculations for different farm sizes
        $sampleSizes = [0.5, 1.0, 2.0, 3.0, 5.0, 10.0]; // hectares
        $preview = [];
        
        foreach ($sampleSizes as $hectares) {
            $sampleCalculations = [];
            
            foreach ($rules as $rule) {
                $quantity = null;
                $amount = null;
                
                switch ($rule->calculation_method) {
                    case 'per_hectare':
                        $quantity = $rule->quantity_per_hectare ? $hectares * $rule->quantity_per_hectare : null;
                        $amount = $rule->amount_per_hectare ? $hectares * $rule->amount_per_hectare : null;
                        break;
                        
                    case 'per_farmer':
                        $quantity = $rule->quantity_per_hectare;
                        $amount = $rule->amount_per_hectare;
                        break;
                        
                    case 'sliding_scale':
                        $result = $this->calculateSlidingScale($hectares, $rule);
                        $quantity = $result['quantity'];
                        $amount = $result['amount'];
                        break;
                }
                
                // Apply limits
                $quantity = $this->applyLimits($quantity, $rule->minimum_quantity, $rule->maximum_quantity);
                $amount = $this->applyLimits($amount, $rule->minimum_amount, $rule->maximum_amount);
                
                $sampleCalculations[] = [
                    'item_name' => $rule->inventory ? $rule->inventory->item_name : $rule->financialSubsidyType->type_name,
                    'item_type' => $rule->inventory_id ? 'inventory' : 'financial',
                    'calculated_quantity' => $quantity,
                    'calculated_amount' => $amount,
                    'unit' => $rule->inventory ? $rule->inventory->unit : 'PHP'
                ];
            }
            
            $preview[] = [
                'farm_size_hectares' => $hectares,
                'calculated_subsidies' => $sampleCalculations
            ];
        }
        
        return $preview;
    }
}