<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../config.php';

// Get report data
$farmers_count = $conn->query("SELECT COUNT(*) AS count FROM farmers")->fetch_assoc()['count'];
$deliveries_total = $conn->query("SELECT SUM(weight) AS total FROM deliveries")->fetch_assoc()['total'];
$payments_total = $conn->query("SELECT SUM(amount) AS total FROM payments")->fetch_assoc()['total'];
$inventory_total = $conn->query("SELECT SUM(quantity) AS total FROM inventory")->fetch_assoc()['total'];

// Get top farmers
$top_farmers = $conn->query("
    SELECT f.id, f.first_name, f.last_name, f.membership_no,
           SUM(d.weight) AS total_weight,
           SUM(p.amount) AS total_payments
    FROM farmers f
    LEFT JOIN deliveries d ON f.id = d.farmer_id
    LEFT JOIN payments p ON d.id = p.delivery_id
    GROUP BY f.id
    ORDER BY total_weight DESC
    LIMIT 5
");

// Get delivery by grade
$delivery_by_grade = $conn->query("
    SELECT grade, SUM(weight) AS total_weight
    FROM deliveries
    GROUP BY grade
    ORDER BY total_weight DESC
");

// Get monthly deliveries
$monthly_deliveries = $conn->query("
    SELECT DATE_FORMAT(delivery_date, '%Y-%m') AS month, 
           SUM(weight) AS total_weight
    FROM deliveries
    WHERE delivery_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month
");
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">Reports & Analytics</h2>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body text-center">
                <h5 class="card-title">Farmers</h5>
                <p class="display-4"><?= $farmers_count ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body text-center">
                <h5 class="card-title">Deliveries</h5>
                <p class="display-4"><?= number_format($deliveries_total, 2) ?> kg</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body text-center">
                <h5 class="card-title">Payments</h5>
                <p class="display-4">KES <?= number_format($payments_total, 2) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body text-center">
                <h5 class="card-title">Inventory</h5>
                <p class="display-4"><?= number_format($inventory_total, 2) ?> kg</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Top Farmers by Delivery</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Farmer</th>
                                <th>Membership No</th>
                                <th>Total Weight (kg)</th>
                                <th>Total Payments (KES)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($farmer = $top_farmers->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name']) ?></td>
                                    <td><?= htmlspecialchars($farmer['membership_no']) ?></td>
                                    <td><?= number_format($farmer['total_weight'] ?? 0, 2) ?></td>
                                    <td><?= number_format($farmer['total_payments'] ?? 0, 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Delivery by Grade</h5>
            </div>
            <div class="card-body">
                <canvas id="gradeChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Monthly Delivery Trend (Last 6 Months)</h5>
    </div>
    <div class="card-body">
        <canvas id="monthlyChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grade distribution chart
    const gradeCtx = document.getElementById('gradeChart').getContext('2d');
    const gradeChart = new Chart(gradeCtx, {
        type: 'bar',
        data: {
            labels: [
                <?php 
                $delivery_by_grade->data_seek(0);
                while ($row = $delivery_by_grade->fetch_assoc()): 
                    echo "'" . $row['grade'] . "',";
                endwhile; 
                ?>
            ],
            datasets: [{
                label: 'Total Weight (kg)',
                data: [
                    <?php 
                    $delivery_by_grade->data_seek(0);
                    while ($row = $delivery_by_grade->fetch_assoc()): 
                        echo $row['total_weight'] . ",";
                    endwhile; 
                    ?>
                ],
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Monthly deliveries chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: [
                <?php 
                $monthly_deliveries->data_seek(0);
                while ($row = $monthly_deliveries->fetch_assoc()): 
                    $date = DateTime::createFromFormat('Y-m', $row['month']);
                    echo "'" . $date->format('M Y') . "',";
                endwhile; 
                ?>
            ],
            datasets: [{
                label: 'Coffee Delivery (kg)',
                data: [
                    <?php 
                    $monthly_deliveries->data_seek(0);
                    while ($row = $monthly_deliveries->fetch_assoc()): 
                        echo $row['total_weight'] . ",";
                    endwhile; 
                    ?>
                ],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
<?php include '../includes/footer.php'; ?>