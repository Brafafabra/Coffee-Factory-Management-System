<?php
$sql = "SELECT 
            'New Farmer' AS type, 
            CONCAT(first_name, ' ', last_name) AS name, 
            '' AS details, 
            join_date AS date 
        FROM farmers 
        UNION
        SELECT 
            'Delivery' AS type, 
            CONCAT(f.first_name, ' ', f.last_name) AS name, 
            CONCAT(d.weight, ' kg - ', d.grade) AS details, 
            d.delivery_date AS date 
        FROM deliveries d
        JOIN farmers f ON d.farmer_id = f.id
        UNION
        SELECT 
            'Payment' AS type, 
            CONCAT(f.first_name, ' ', f.last_name) AS name, 
            CONCAT('KES ', p.amount) AS details, 
            p.payment_date AS date 
        FROM payments p
        JOIN farmers f ON p.farmer_id = f.id
        ORDER BY date DESC LIMIT 10";

$result = $conn->query($sql);

if ($result->num_rows > 0): ?>
    <div class="list-group">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">
                        <i class="bi bi-<?= 
                            $row['type'] == 'New Farmer' ? 'person-plus' : 
                            ($row['type'] == 'Delivery' ? 'truck' : 'cash-coin') 
                        ?> me-2"></i>
                        <?= $row['type'] ?>
                    </h6>
                    <small class="text-muted"><?= date('M j, Y', strtotime($row['date'])) ?></small>
                </div>
                <p class="mb-1"><?= htmlspecialchars($row['name']) ?></p>
                <?php if ($row['details']): ?>
                    <small class="text-muted"><?= htmlspecialchars($row['details']) ?></small>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">No recent activities found</div>
<?php endif; ?>