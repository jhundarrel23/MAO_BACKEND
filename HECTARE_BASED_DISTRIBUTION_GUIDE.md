# Hectare-Based Distribution System Guide

## 🚨 **Problem Fixed: Fair Distribution Based on Farm Size**

### ❌ **Previous Wrong System:**
- All farmers get same amount regardless of farm size
- Juan (2 hectares) gets 2 bags seeds
- David (1 hectare) gets 2 bags seeds  
- **UNFAIR!** Small farmers get too much, large farmers get too little

### ✅ **New Correct System:**
- Distribution based on actual farm hectares
- Juan (2 hectares) gets 2 bags seeds (1 bag per hectare)
- David (1 hectare) gets 1 bag seeds (1 bag per hectare)
- **FAIR!** Everyone gets proportional to their farm size

## 🧮 **Calculation Methods Available:**

### **1. Per Hectare Method** (Most Common)
```php
// Rule: 1 bag rice seeds per hectare
quantity_per_hectare: 1.0
minimum_quantity: 0.5 bags
maximum_quantity: 10.0 bags

// Results:
Juan (2.0 hectares) → 2.0 bags
David (1.0 hectare) → 1.0 bag  
Maria (0.3 hectares) → 0.5 bags (minimum applied)
Pedro (15 hectares) → 10.0 bags (maximum applied)
```

### **2. Sliding Scale Method** (For Complex Rules)
```json
{
  "0-1": {"quantity": 1.0, "amount": 2000},
  "1-3": {"quantity": 1.5, "amount": 3000}, 
  "3-5": {"quantity": 2.0, "amount": 4000},
  "5+": {"quantity": 2.5, "amount": 5000}
}
```

**Results:**
- Juan (2.0 hectares) → 1.5 bags + ₱3,000
- David (1.0 hectare) → 1.0 bag + ₱2,000
- Pedro (6.0 hectares) → 2.5 bags + ₱5,000

### **3. Per Farmer Method** (Fixed Amount)
```php
// Everyone gets same amount regardless of farm size
quantity_per_hectare: 2.0 (fixed)

// Results:
Juan (2.0 hectares) → 2.0 bags
David (1.0 hectare) → 2.0 bags
Maria (0.5 hectares) → 2.0 bags
```

## 📊 **Real-World Example: "Rice Seed Distribution 2025"**

### **Program Setup:**

#### **Calculation Rules:**
```php
// Rice Seeds Rule
SubsidyCalculationRule::create([
    'subsidy_program_id' => 1,
    'inventory_id' => 1, // Rice Seeds
    'calculation_method' => 'per_hectare',
    'quantity_per_hectare' => 1.0,  // 1 bag per hectare
    'minimum_quantity' => 0.5,       // At least 0.5 bags
    'maximum_quantity' => 10.0,      // Max 10 bags
    'min_farm_size_hectares' => 0.1, // Min 0.1 hectare to qualify
    'max_farm_size_hectares' => 20.0 // Max 20 hectares eligible
]);

// Fertilizer Rule  
SubsidyCalculationRule::create([
    'subsidy_program_id' => 1,
    'inventory_id' => 4, // Fertilizer
    'calculation_method' => 'per_hectare',
    'quantity_per_hectare' => 2.0,  // 2 bags per hectare
    'minimum_quantity' => 1.0,       // At least 1 bag
    'maximum_quantity' => 20.0       // Max 20 bags
]);

// Planting Allowance Rule
SubsidyCalculationRule::create([
    'subsidy_program_id' => 1,
    'financial_subsidy_type_id' => 1, // Planting Allowance
    'calculation_method' => 'per_hectare',
    'amount_per_hectare' => 1500.00, // ₱1,500 per hectare
    'minimum_amount' => 1000.00,     // Min ₱1,000
    'maximum_amount' => 15000.00     // Max ₱15,000
]);
```

### **Beneficiary Farm Sizes:**
```sql
-- Farmers and their farm sizes
Farmer Name    Farm Size    Farm Type        Tenure Type
Juan Dela Cruz  2.0 ha      irrigated        registered_owner
David Santos    1.0 ha      rainfed_upland   tenant
Maria Garcia    0.5 ha      irrigated        registered_owner
Pedro Lopez     5.0 ha      rainfed_lowland  registered_owner
Ana Reyes       0.3 ha      irrigated        lessee
```

### **Automatic Calculations:**

#### **Juan Dela Cruz (2.0 hectares):**
```php
Rice Seeds: 2.0 ha × 1.0 bag/ha = 2.0 bags
Fertilizer: 2.0 ha × 2.0 bags/ha = 4.0 bags  
Planting Allowance: 2.0 ha × ₱1,500/ha = ₱3,000
```

#### **David Santos (1.0 hectare):**
```php
Rice Seeds: 1.0 ha × 1.0 bag/ha = 1.0 bag
Fertilizer: 1.0 ha × 2.0 bags/ha = 2.0 bags
Planting Allowance: 1.0 ha × ₱1,500/ha = ₱1,500
```

#### **Maria Garcia (0.5 hectares):**
```php
Rice Seeds: 0.5 ha × 1.0 bag/ha = 0.5 bags (meets minimum)
Fertilizer: 0.5 ha × 2.0 bags/ha = 1.0 bag (meets minimum)
Planting Allowance: 0.5 ha × ₱1,500/ha = ₱1,000 (minimum applied)
```

#### **Ana Reyes (0.3 hectares):**
```php
Rice Seeds: 0.3 ha × 1.0 bag/ha = 0.5 bags (minimum applied)
Fertilizer: 0.3 ha × 2.0 bags/ha = 1.0 bag (minimum applied)  
Planting Allowance: 0.3 ha × ₱1,500/ha = ₱1,000 (minimum applied)
```

#### **Pedro Lopez (5.0 hectares):**
```php
Rice Seeds: 5.0 ha × 1.0 bag/ha = 5.0 bags
Fertilizer: 5.0 ha × 2.0 bags/ha = 10.0 bags
Planting Allowance: 5.0 ha × ₱1,500/ha = ₱7,500
```

## 📋 **Database Structure:**

### **`subsidy_calculation_rules` Table:**
```sql
id  program_id  inventory_id  calculation_method  quantity_per_hectare  min_quantity  max_quantity
1   1          1             per_hectare         1.0                   0.5           10.0
2   1          4             per_hectare         2.0                   1.0           20.0
3   1          NULL          per_hectare         NULL                  NULL          NULL
```

### **`program_beneficiaries` Table (Enhanced):**
```sql
id  user_id  program_id  beneficiary_farm_size_hectares  beneficiary_farm_type
1   101     1           2.0                             irrigated
2   102     1           1.0                             rainfed_upland  
3   103     1           0.5                             irrigated
4   104     1           5.0                             rainfed_lowland
```

### **`program_beneficiary_items` Table (Enhanced):**
```sql
id  beneficiary_id  inventory_id  calculated_quantity  approved_quantity  calculation_notes
1   1              1             2.0                  2.0                "2.0 ha × 1.0 bag/ha"
2   1              4             4.0                  4.0                "2.0 ha × 2.0 bags/ha"
3   2              1             1.0                  1.0                "1.0 ha × 1.0 bag/ha"  
4   2              4             2.0                  2.0                "1.0 ha × 2.0 bags/ha"
5   3              1             0.5                  0.5                "0.5 ha × 1.0 bag/ha"
6   3              4             1.0                  1.0                "0.5 ha × 2.0 bags/ha (min applied)"
```

## 🔧 **How to Use:**

### **Step 1: Create Program with Calculation Rules**
```php
// Create program
$program = SubsidyProgram::create([
    'title' => 'Rice Seed Distribution 2025',
    'total_budget' => 500000.00
]);

// Create calculation rules
$calculationService = new HectareBasedCalculationService();

// Rule for rice seeds: 1 bag per hectare
$calculationService->createCalculationRule($program->id, [
    'inventory_id' => 1,
    'calculation_method' => 'per_hectare',
    'quantity_per_hectare' => 1.0,
    'minimum_quantity' => 0.5,
    'maximum_quantity' => 10.0
]);

// Rule for planting allowance: ₱1,500 per hectare
$calculationService->createCalculationRule($program->id, [
    'financial_subsidy_type_id' => 1,
    'calculation_method' => 'per_hectare', 
    'amount_per_hectare' => 1500.00,
    'minimum_amount' => 1000.00,
    'maximum_amount' => 15000.00
]);
```

### **Step 2: Add Beneficiaries**
```php
// Add Juan (2 hectares)
ProgramBeneficiary::create([
    'subsidy_program_id' => $program->id,
    'user_id' => 101, // Juan
    'commodity_id' => 1, // Rice
    'status' => 'approved'
]);

// Add David (1 hectare)  
ProgramBeneficiary::create([
    'subsidy_program_id' => $program->id,
    'user_id' => 102, // David
    'commodity_id' => 1, // Rice
    'status' => 'approved'
]);
```

### **Step 3: Calculate Subsidies Automatically**
```php
// System automatically calculates based on farm sizes
$results = $calculationService->calculateSubsidiesForProgram($program->id);

// Results:
// Juan gets: 2.0 bags rice seeds + 4.0 bags fertilizer + ₱3,000
// David gets: 1.0 bag rice seeds + 2.0 bags fertilizer + ₱1,500
```

## ✅ **Benefits of New System:**

### **✅ Fair Distribution:**
- Larger farms get more subsidies (they need more inputs)
- Smaller farms get appropriate amounts
- No waste or shortage

### **✅ Flexible Rules:**
- Can set different rates per hectare for different items
- Can set minimum and maximum limits
- Can use sliding scales for complex policies

### **✅ Automatic Calculation:**
- System calculates everything automatically
- No manual calculation errors
- Consistent application of rules

### **✅ Eligibility Controls:**
- Minimum farm size requirements
- Maximum farm size limits
- Farm type restrictions (irrigated vs rainfed)
- Tenure type restrictions

**Now your distribution system is FAIR and LOGICAL based on actual farm sizes!** 🌾📐✅