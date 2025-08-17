# Enhanced Inventory & Distribution Flow System

## Overview
This document explains the enhanced inventory management system that properly connects with the distribution system to track goods flow from receipt to beneficiary distribution.

## System Architecture

### **Core Tables and Their Relationships**

```
inventories (Items Master)
├── inventory_stocks (All Movements)
├── inventory_current_stocks (Current Levels)
├── inventory_reservations (Reserved Stock)
└── program_beneficiary_items (Distribution Items)
    └── distribution_packages (Grouped Distributions)
        └── beneficiary_assistance_history (Final Record)
```

## **1. Inventory Management Tables**

### **inventories** - Master Items List
- **Purpose**: Defines all items that can be stocked and distributed
- **Enhanced Features**:
  - Support for physical items (seeds, fertilizer) and monetary assistance (cash, fuel vouchers)
  - `assistance_category`: physical, monetary, service
  - `is_trackable_stock`: FALSE for cash assistance, TRUE for physical items
  - `unit_value`: Cost per unit for budgeting

### **inventory_stocks** - All Stock Movements
- **Purpose**: Records every stock movement (in/out/adjustments)
- **Movement Types**:
  - `stock_in`: New inventory received
  - `stock_out`: Items distributed or removed
  - `adjustment`: Corrections, damages, expired items
  - `transfer`: Movement between locations
  - `distribution`: Direct distribution to beneficiaries

- **Transaction Types**:
  - `purchase`, `donation`, `return`: Stock coming in
  - `distribution`: Items going to beneficiaries
  - `damage`, `expired`: Stock losses
  - `transfer_in`, `transfer_out`: Location transfers

### **inventory_current_stocks** - Real-time Stock Levels
- **Purpose**: Maintains current stock levels for each item
- **Key Fields**:
  - `current_quantity`: Total stock on hand
  - `reserved_quantity`: Stock allocated but not yet distributed
  - `available_quantity`: Stock available for new distributions
  - `is_low_stock`, `is_out_of_stock`: Alert flags

### **inventory_reservations** - Stock Allocation
- **Purpose**: Reserves stock when distributions are prepared but not yet completed
- **Workflow**:
  1. Coordinator prepares distribution package
  2. Stock gets reserved automatically
  3. Available stock reduces, reserved stock increases
  4. When distributed, reservation is fulfilled and stock moves out

## **2. Distribution Flow Process**

### **Step 1: Stock Receipt**
```sql
-- Example: Receiving 100 bags of rice seeds
INSERT INTO inventory_stocks (
    inventory_id, quantity, movement_type, transaction_type,
    source, reference, transaction_date, unit_cost, total_value
) VALUES (
    1, 100, 'stock_in', 'donation',
    'DA Region X', 'DR-2025-001', '2025-01-15', 500.00, 50000.00
);
```

### **Step 2: Distribution Preparation**
```sql
-- Coordinator creates distribution package
INSERT INTO distribution_packages (
    package_code, program_beneficiary_id, package_name, status
) VALUES (
    'PKG-2025-001', 1, 'Rice Farming Package - Juan Dela Cruz', 'pending'
);

-- Add items to package (coordinator customizes amounts)
INSERT INTO program_beneficiary_items (
    program_beneficiary_id, distribution_package_id, inventory_id,
    item_name, quantity, unit, coordinator_amount, coordinator_notes
) VALUES (
    1, 1, 1, 'Hybrid Rice Seeds', 5, 'bags', 5, 
    'Adjusted for 2-hectare farm size and senior citizen status'
);
```

### **Step 3: Stock Reservation**
```sql
-- System automatically reserves stock
INSERT INTO inventory_reservations (
    inventory_id, reservation_code, reserved_quantity,
    distribution_package_id, program_beneficiary_item_id,
    reserved_by, reserved_at
) VALUES (
    1, 'RSV-2025-001', 5, 1, 1, 2, NOW()
);
```

### **Step 4: Stock Distribution**
```sql
-- When items are actually distributed
INSERT INTO inventory_stocks (
    inventory_id, quantity, movement_type, transaction_type,
    distribution_package_id, program_beneficiary_item_id,
    destination, reference, transaction_date
) VALUES (
    1, -5, 'stock_out', 'distribution',
    1, 1, 'Juan Dela Cruz - Barangay Poblacion', 
    'DIST-2025-001', '2025-01-20'
);

-- Mark reservation as fulfilled
UPDATE inventory_reservations 
SET status = 'fulfilled', fulfilled_at = NOW() 
WHERE id = 1;
```

### **Step 5: History Recording**
```sql
-- Final record in assistance history (for duplicate prevention)
INSERT INTO beneficiary_assistance_history (
    beneficiary_id, subsidy_program_id, assistance_type,
    item_name, quantity_received, unit, total_value,
    distribution_date, assistance_year, season
) VALUES (
    1, 1, 'seed', 'Hybrid Rice Seeds', 5, 'bags', 2500.00,
    '2025-01-20', 2025, 'wet'
);
```

## **3. Goods Flow Tracking**

### **Real-time Stock Updates**
The system automatically maintains current stock levels:

```sql
-- Current stock calculation (simplified)
UPDATE inventory_current_stocks SET
    current_quantity = (
        SELECT COALESCE(SUM(quantity), 0) 
        FROM inventory_stocks 
        WHERE inventory_id = ? AND status = 'completed'
    ),
    reserved_quantity = (
        SELECT COALESCE(SUM(reserved_quantity), 0)
        FROM inventory_reservations 
        WHERE inventory_id = ? AND status = 'active'
    ),
    available_quantity = current_quantity - reserved_quantity,
    is_out_of_stock = (available_quantity <= 0),
    is_low_stock = (available_quantity <= minimum_stock_level)
WHERE inventory_id = ?;
```

### **Stock Availability Check**
Before creating distributions, system checks:
```sql
SELECT 
    i.item_name,
    ics.current_quantity,
    ics.reserved_quantity,
    ics.available_quantity,
    ics.is_out_of_stock,
    ics.is_low_stock
FROM inventories i
JOIN inventory_current_stocks ics ON i.id = ics.inventory_id
WHERE i.id = ? AND ics.available_quantity >= ?;
```

## **4. Distribution Scenarios**

### **Scenario A: Simple Distribution**
1. **Stock**: 100 bags rice seeds available
2. **Request**: Distribute 5 bags to Juan
3. **Process**:
   - Reserve 5 bags → Available: 95 bags
   - Distribute 5 bags → Current: 95 bags, Reserved: 0 bags
   - Record in history for duplicate prevention

### **Scenario B: Mixed Package Distribution**
1. **Stock**: 100 bags seeds, 200 bags fertilizer, ₱500,000 cash budget
2. **Request**: Complete package for Maria
3. **Process**:
   ```
   Package: "Complete Farming Support - Maria Santos"
   ├── 3 bags Rice Seeds (₱1,500) - Reserved from inventory
   ├── 5 bags Fertilizer (₱2,500) - Reserved from inventory  
   └── ₱8,000 Cash Assistance - Deducted from budget
   Total: ₱12,000
   ```

### **Scenario C: Insufficient Stock**
1. **Stock**: 2 bags rice seeds available
2. **Request**: Distribute 5 bags to David
3. **System Response**:
   - Alert: "Insufficient stock. Available: 2 bags, Requested: 5 bags"
   - Suggest: Reduce quantity or wait for new stock
   - Alternative: Partial distribution with coordinator approval

## **5. Key Benefits of Enhanced System**

### **Accurate Stock Tracking**
- Real-time stock levels
- Automatic reservation system
- Prevention of over-distribution
- Clear audit trail of all movements

### **Connected Distribution Flow**
- Direct link between inventory and distributions
- Automatic stock deduction upon distribution
- Proper goods flow from receipt to beneficiary
- Complete traceability

### **Better Inventory Management**
- Low stock alerts
- Expiry date tracking
- Batch number management
- Location tracking
- Cost and value monitoring

### **Coordinator Control**
- Manual customization of distribution amounts
- Guidelines for reference (not mandatory)
- Approval workflow for stock movements
- Detailed notes and reasoning tracking

## **6. Implementation Workflow**

### **For Coordinators:**
1. **Check Stock Availability**: View current stock before planning distributions
2. **Create Distribution Package**: Group multiple items for one beneficiary
3. **Customize Amounts**: Adjust quantities based on beneficiary needs
4. **System Reserves Stock**: Automatic reservation prevents double allocation
5. **Distribute Items**: Mark as distributed when physically handed over
6. **System Updates Stock**: Automatic deduction and history recording

### **For Inventory Managers:**
1. **Receive Stock**: Record all incoming inventory
2. **Monitor Levels**: Track current stock and alerts
3. **Manage Locations**: Organize stock by storage areas
4. **Handle Adjustments**: Record damages, expiries, corrections
5. **Generate Reports**: Stock levels, movements, distributions

## **7. Sample Reports Available**

### **Stock Status Report**
- Current quantity by item
- Reserved vs available stock
- Low stock alerts
- Items nearing expiry

### **Distribution Summary**
- Items distributed by program
- Beneficiaries served
- Total value distributed
- Remaining stock after distributions

### **Goods Flow Report**
- Stock movements over time
- Sources of stock receipts
- Distribution destinations
- Complete item traceability

This enhanced system ensures proper goods flow tracking from inventory receipt to beneficiary distribution, with coordinator control over amounts while maintaining accurate stock levels and preventing duplicate distributions.