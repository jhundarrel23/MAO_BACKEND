# Beneficiary Assistance History Queries

Instead of maintaining a separate `beneficiary_assistance_history` table, we can query assistance history directly from the existing `program_beneficiary_items` table. This approach eliminates data duplication and ensures data consistency.

## 📊 **Available Queries**

### **1. Complete Assistance History for a Beneficiary**

```sql
SELECT 
    bp.user_id,
    u.name as beneficiary_name,
    sp.title as program_name,
    i.item_name,
    i.item_type,
    i.assistance_category,
    pbi.quantity,
    pbi.unit,
    pbi.coordinator_amount,
    pbi.total_value,
    pbi.distribution_date,
    pbi.distribution_year,
    pbi.season,
    pbi.status,
    pbi.coordinator_notes
FROM program_beneficiary_items pbi
JOIN program_beneficiaries pb ON pbi.program_beneficiary_id = pb.id
JOIN beneficiary_profiles bp ON pb.beneficiary_profile_id = bp.id
JOIN users u ON bp.user_id = u.id
JOIN subsidy_programs sp ON pb.subsidy_program_id = sp.id
LEFT JOIN inventories i ON pbi.inventory_id = i.id
WHERE bp.id = ? -- Specific beneficiary ID
  AND pbi.status = 'distributed'
ORDER BY pbi.distribution_date DESC;
```

### **2. Check for Duplicate Benefits (Same Item Type, Year, Season)**

```sql
SELECT COUNT(*) as assistance_count
FROM program_beneficiary_items pbi
JOIN program_beneficiaries pb ON pbi.program_beneficiary_id = pb.id
JOIN inventories i ON pbi.inventory_id = i.id
WHERE pb.beneficiary_profile_id = ? -- Beneficiary ID
  AND i.item_type = ? -- e.g., 'seed', 'fertilizer', 'cash'
  AND pbi.distribution_year = ? -- e.g., 2025
  AND pbi.season = ? -- e.g., 'wet', 'dry'
  AND pbi.status IN ('distributed', 'approved');
```

### **3. Assistance Summary by Year**

```sql
SELECT 
    pbi.distribution_year,
    i.item_type,
    i.assistance_category,
    COUNT(*) as distribution_count,
    SUM(pbi.quantity) as total_quantity,
    SUM(pbi.total_value) as total_value
FROM program_beneficiary_items pbi
JOIN program_beneficiaries pb ON pbi.program_beneficiary_id = pb.id
LEFT JOIN inventories i ON pbi.inventory_id = i.id
WHERE pb.beneficiary_profile_id = ? -- Beneficiary ID
  AND pbi.status = 'distributed'
GROUP BY pbi.distribution_year, i.item_type, i.assistance_category
ORDER BY pbi.distribution_year DESC;
```

### **4. Recent Assistance (Last 6 months)**

```sql
SELECT 
    u.name as beneficiary_name,
    sp.title as program_name,
    i.item_name,
    pbi.quantity,
    pbi.unit,
    pbi.total_value,
    pbi.distribution_date,
    pbi.coordinator_notes
FROM program_beneficiary_items pbi
JOIN program_beneficiaries pb ON pbi.program_beneficiary_id = pb.id
JOIN beneficiary_profiles bp ON pb.beneficiary_profile_id = bp.id
JOIN users u ON bp.user_id = u.id
JOIN subsidy_programs sp ON pb.subsidy_program_id = sp.id
LEFT JOIN inventories i ON pbi.inventory_id = i.id
WHERE pb.beneficiary_profile_id = ? -- Beneficiary ID
  AND pbi.status = 'distributed'
  AND pbi.distribution_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
ORDER BY pbi.distribution_date DESC;
```

### **5. Assistance by Program**

```sql
SELECT 
    sp.title as program_name,
    sp.start_date,
    sp.end_date,
    COUNT(pbi.id) as items_received,
    SUM(pbi.total_value) as total_program_value
FROM program_beneficiary_items pbi
JOIN program_beneficiaries pb ON pbi.program_beneficiary_id = pb.id
JOIN subsidy_programs sp ON pb.subsidy_program_id = sp.id
WHERE pb.beneficiary_profile_id = ? -- Beneficiary ID
  AND pbi.status = 'distributed'
GROUP BY sp.id, sp.title, sp.start_date, sp.end_date
ORDER BY sp.start_date DESC;
```

### **6. Assistance by Item Type**

```sql
SELECT 
    i.item_type,
    i.assistance_category,
    COUNT(pbi.id) as times_received,
    SUM(pbi.quantity) as total_quantity,
    AVG(pbi.quantity) as average_quantity,
    SUM(pbi.total_value) as total_value,
    MAX(pbi.distribution_date) as last_received
FROM program_beneficiary_items pbi
JOIN program_beneficiaries pb ON pbi.program_beneficiary_id = pb.id
LEFT JOIN inventories i ON pbi.inventory_id = i.id
WHERE pb.beneficiary_profile_id = ? -- Beneficiary ID
  AND pbi.status = 'distributed'
GROUP BY i.item_type, i.assistance_category
ORDER BY total_value DESC;
```

### **7. Seasonal Assistance Pattern**

```sql
SELECT 
    pbi.distribution_year,
    pbi.season,
    COUNT(pbi.id) as assistance_count,
    SUM(pbi.total_value) as season_total_value,
    GROUP_CONCAT(DISTINCT i.item_type) as item_types_received
FROM program_beneficiary_items pbi
JOIN program_beneficiaries pb ON pbi.program_beneficiary_id = pb.id
LEFT JOIN inventories i ON pbi.inventory_id = i.id
WHERE pb.beneficiary_profile_id = ? -- Beneficiary ID
  AND pbi.status = 'distributed'
  AND pbi.season IS NOT NULL
GROUP BY pbi.distribution_year, pbi.season
ORDER BY pbi.distribution_year DESC, pbi.season;
```

### **8. Compare Beneficiary Assistance (Multiple Beneficiaries)**

```sql
SELECT 
    u.name as beneficiary_name,
    bp.barangay,
    COUNT(pbi.id) as total_assistance_received,
    SUM(pbi.total_value) as total_value_received,
    MAX(pbi.distribution_date) as last_assistance_date,
    COUNT(DISTINCT sp.id) as programs_participated
FROM program_beneficiary_items pbi
JOIN program_beneficiaries pb ON pbi.program_beneficiary_id = pb.id
JOIN beneficiary_profiles bp ON pb.beneficiary_profile_id = bp.id
JOIN users u ON bp.user_id = u.id
JOIN subsidy_programs sp ON pb.subsidy_program_id = sp.id
WHERE pbi.status = 'distributed'
  AND pbi.distribution_year = ? -- Specific year
GROUP BY bp.id, u.name, bp.barangay
ORDER BY total_value_received DESC;
```

## 🔍 **Duplicate Prevention Logic**

Instead of a separate history table, use this query to check for duplicates before creating new distributions:

```sql
-- Check if beneficiary already received same item type in same season/year
SELECT 
    pbi.id,
    i.item_name,
    pbi.quantity,
    pbi.distribution_date
FROM program_beneficiary_items pbi
JOIN program_beneficiaries pb ON pbi.program_beneficiary_id = pb.id
JOIN inventories i ON pbi.inventory_id = i.id
WHERE pb.beneficiary_profile_id = ? -- Beneficiary ID
  AND i.item_type = ? -- Item type to check
  AND pbi.distribution_year = ? -- Current year
  AND pbi.season = ? -- Current season
  AND pbi.status IN ('distributed', 'approved', 'prepared');

-- If this returns any rows, beneficiary already received this type of assistance
```

## 📈 **Benefits of Query-Based Approach**

### **Advantages:**
1. **No Data Duplication**: Single source of truth
2. **Real-time Data**: Always current, no sync issues
3. **Simpler Schema**: Fewer tables to maintain
4. **Flexible Queries**: Can aggregate data in any way needed
5. **Better Performance**: No need to maintain duplicate records

### **Performance Optimization:**
```sql
-- Add indexes for better query performance
CREATE INDEX idx_beneficiary_status_date ON program_beneficiary_items(program_beneficiary_id, status, distribution_date);
CREATE INDEX idx_item_type_year_season ON program_beneficiary_items(inventory_id, distribution_year, season);
CREATE INDEX idx_distribution_date ON program_beneficiary_items(distribution_date);
```

## 🚀 **Implementation in Application**

### **Laravel Eloquent Examples:**

```php
// Get beneficiary assistance history
$history = ProgramBeneficiaryItem::with(['programBeneficiary.beneficiaryProfile.user', 'programBeneficiary.subsidyProgram', 'inventory'])
    ->whereHas('programBeneficiary', function($query) use ($beneficiaryId) {
        $query->where('beneficiary_profile_id', $beneficiaryId);
    })
    ->where('status', 'distributed')
    ->orderBy('distribution_date', 'desc')
    ->get();

// Check for duplicates
$duplicateCount = ProgramBeneficiaryItem::whereHas('programBeneficiary', function($query) use ($beneficiaryId) {
        $query->where('beneficiary_profile_id', $beneficiaryId);
    })
    ->whereHas('inventory', function($query) use ($itemType) {
        $query->where('item_type', $itemType);
    })
    ->where('distribution_year', $year)
    ->where('season', $season)
    ->whereIn('status', ['distributed', 'approved', 'prepared'])
    ->count();
```

This approach is much cleaner and eliminates the need for a separate history table while providing all the same functionality! 🎯