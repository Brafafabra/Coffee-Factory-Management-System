<?php
// Dashboard statistics
$stats = [
    'farmers' => 0,
    'deliveries' => 0,
    'payments' => 0,
    'inventory' => 0,
    'activities' => []
];

// Get farmer count
$sql = "SELECT COUNT(*) AS count FROM farmers";
$result = $conn->query($sql);
if ($result) $stats['farmers'] = $result->fetch_assoc()['count'];

// Get delivery count (last 30 days)
$sql = "SELECT COUNT(*) AS count FROM deliveries 
        WHERE delivery_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$result = $conn->query($sql);
if ($result) $stats['deliveries'] = $result->fetch_assoc()['count'];

// Get payment sum (last 30 days)
$sql = "SELECT SUM(amount) AS total FROM payments 
        WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$result = $conn->query($sql);
if ($result) $stats['payments'] = $result->fetch_assoc()['total'] ?? 0;

// Get current inventory
$sql = "SELECT SUM(quantity) AS total FROM inventory 
        WHERE stock_movement = 'in'";
$out_sql = "SELECT SUM(quantity) AS total FROM inventory 
            WHERE stock_movement = 'out'";
$result = $conn->query($sql);
$out_result = $conn->query($out_sql);

if ($result && $out_result) {
    $in = $result->fetch_assoc()['total'] ?? 0;
    $out = $out_result->fetch_assoc()['total'] ?? 0;
    $stats['inventory'] = $in - $out;
}

// Get recent activities
$sql = "SELECT 'delivery' AS type, delivery_date AS date, 
               CONCAT('Delivery from ', f.first_name, ' ', f.last_name) AS description,
               quantity AS value
        FROM deliveries d
        JOIN farmers f ON d.farmer_id = f.farmer_id
        UNION ALL
        SELECT 'payment' AS type, payment_date AS date,
               CONCAT('Payment to ', f.first_name, ' ', f.last_name) AS description,
               amount AS value
        FROM payments p
        JOIN farmers f ON p.farmer_id = f.farmer_id
        ORDER BY date DESC
        LIMIT 10";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $stats['activities'][] = $row;
    }
}