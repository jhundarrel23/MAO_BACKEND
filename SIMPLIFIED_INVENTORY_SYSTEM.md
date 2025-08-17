# Simplified Inventory System

## 📦 **2-Table Approach**

### **Table 1: `inventories` (Master Items)**
- Defines all items that can be stocked and distributed
- Seeds, fertilizers, equipment, cash assistance, fuel subsidies
- Item properties, unit values, categories

### **Table 2: `inventory_stocks` (All Movements + Balance)**
- Records every stock movement (in/out/adjustments)
- Maintains running balance automatically
- Connected to distribution system
- Complete audit trail

## 🔄 **How It Works**

### **Stock Flow:**
```
1. Stock In:    +500 bags rice seeds → Running Balance: 500
2. Distribution: -50 bags to Juan    → Running Balance: 450  
3. Distribution: -30 bags to Maria   → Running Balance: 420
4. Adjustment:   -5 bags (damaged)   → Running Balance: 415
```

### **Current Stock Query:**
```sql
-- Get current stock level for any item
SELECT 
    i.item_name,
    i.unit,
    COALESCE(SUM(s.quantity), 0) as current_stock,
    i.unit_value,
    COALESCE(SUM(s.quantity), 0) * i.unit_value as stock_value
FROM inventories i
LEFT JOIN inventory_stocks s ON i.id = s.inventory_id 
    AND s.status = 'completed'
WHERE i.id = ?
GROUP BY i.id;
```

### **Available Stock (Excluding Reserved):**
```sql
-- Get available stock (current - reserved for pending distributions)
SELECT 
    i.item_name,
    COALESCE(SUM(CASE WHEN s.status = 'completed' THEN s.quantity ELSE 0 END), 0) as current_stock,
    COALESCE(SUM(CASE WHEN s.status = 'pending' AND s.quantity < 0 THEN ABS(s.quantity) ELSE 0 END), 0) as reserved_stock,
    COALESCE(SUM(CASE WHEN s.status = 'completed' THEN s.quantity ELSE 0 END), 0) - 
    COALESCE(SUM(CASE WHEN s.status = 'pending' AND s.quantity < 0 THEN ABS(s.quantity) ELSE 0 END), 0) as available_stock
FROM inventories i
LEFT JOIN inventory_stocks s ON i.id = s.inventory_id
WHERE i.id = ?
GROUP BY i.id;
```

## 📊 **Stock Operations**

### **1. Receiving Stock**
```sql
INSERT INTO inventory_stocks (
    inventory_id, quantity, movement_type, transaction_type,
    source, reference, transaction_date, unit_cost, total_value,
    running_balance, status
) VALUES (
    1, 100, 'stock_in', 'donation',
    'DA Region X', 'DR-2025-001', '2025-01-15', 500.00, 50000.00,
    (SELECT COALESCE(SUM(quantity), 0) + 100 FROM inventory_stocks WHERE inventory_id = 1 AND status = 'completed'),
    'completed'
);
```

### **2. Distribution (Reserve First)**
```sql
-- Step 1: Reserve stock (pending status)
INSERT INTO inventory_stocks (
    inventory_id, quantity, movement_type, transaction_type,
    program_beneficiary_item_id, destination, reference, 
    transaction_date, status
) VALUES (
    1, -5, 'stock_out', 'distribution',
    123, 'Juan Dela Cruz - Poblacion', 'DIST-2025-001',
    '2025-01-20', 'pending'
);

-- Step 2: Complete distribution (change status to completed)
UPDATE inventory_stocks 
SET status = 'completed',
    running_balance = (
        SELECT COALESCE(SUM(quantity), 0) 
        FROM inventory_stocks 
        WHERE inventory_id = 1 AND status = 'completed'
    )
WHERE id = ?;
```

### **3. Stock Adjustment**
```sql
INSERT INTO inventory_stocks (
    inventory_id, quantity, movement_type, transaction_type,
    remarks, transaction_date, status, running_balance
) VALUES (
    1, -10, 'adjustment', 'damage',
    'Water damage during storage', '2025-01-25', 'completed',
    (SELECT COALESCE(SUM(quantity), 0) - 10 FROM inventory_stocks WHERE inventory_id = 1 AND status = 'completed')
);
```

## 📈 **Useful Queries**

### **Stock Status Dashboard**
```sql
SELECT 
    i.item_name,
    i.item_type,
    i.assistance_category,
    COALESCE(SUM(CASE WHEN s.status = 'completed' THEN s.quantity ELSE 0 END), 0) as current_stock,
    COALESCE(SUM(CASE WHEN s.status = 'pending' AND s.quantity < 0 THEN ABS(s.quantity) ELSE 0 END), 0) as reserved_stock,
    COALESCE(SUM(CASE WHEN s.status = 'completed' THEN s.quantity ELSE 0 END), 0) - 
    COALESCE(SUM(CASE WHEN s.status = 'pending' AND s.quantity < 0 THEN ABS(s.quantity) ELSE 0 END), 0) as available_stock,
    i.unit,
    i.unit_value
FROM inventories i
LEFT JOIN inventory_stocks s ON i.id = s.inventory_id
GROUP BY i.id
ORDER BY i.item_name;
```

### **Stock Movements History**
```sql
SELECT 
    i.item_name,
    s.quantity,
    s.movement_type,
    s.transaction_type,
    s.source,
    s.destination,
    s.reference,
    s.transaction_date,
    s.running_balance,
    s.status,
    u.name as processed_by
FROM inventory_stocks s
JOIN inventories i ON s.inventory_id = i.id
LEFT JOIN users u ON s.verified_by = u.id
WHERE s.inventory_id = ?
ORDER BY s.transaction_date DESC, s.id DESC;
```

### **Low Stock Alert**
```sql
-- Items with less than 50 units available
SELECT 
    i.item_name,
    COALESCE(SUM(CASE WHEN s.status = 'completed' THEN s.quantity ELSE 0 END), 0) as current_stock
FROM inventories i
LEFT JOIN inventory_stocks s ON i.id = s.inventory_id
GROUP BY i.id
HAVING current_stock < 50
ORDER BY current_stock ASC;
```

### **Distribution Impact**
```sql
-- See how distributions affect stock levels
SELECT 
    i.item_name,
    DATE(s.transaction_date) as distribution_date,
    SUM(ABS(s.quantity)) as total_distributed,
    COUNT(DISTINCT s.program_beneficiary_item_id) as beneficiaries_served
FROM inventory_stocks s
JOIN inventories i ON s.inventory_id = i.id
WHERE s.movement_type = 'distribution' 
  AND s.transaction_type = 'distribution'
  AND s.status = 'completed'
GROUP BY i.id, DATE(s.transaction_date)
ORDER BY distribution_date DESC;
```

## ✅ **Benefits of Simplified System**

1. **Simple Schema**: Only 2 tables to maintain
2. **Single Source**: All stock info in one place
3. **Real-time Balance**: Running balance always current
4. **Complete History**: Full audit trail preserved
5. **Easy Queries**: Straightforward to get current stock
6. **Flexible**: Can still track reservations with status field
7. **Connected**: Direct link to distribution system

## 🎯 **Perfect for Your Needs**

- Coordinators can check stock before distributions
- System prevents over-distribution
- Complete tracking from receipt to beneficiary
- Simple enough to maintain and understand
- Flexible enough for future enhancements

This simplified approach gives you all the functionality you need without the complexity! 🌾