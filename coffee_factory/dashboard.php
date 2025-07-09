<?php
require_once 'includes/auth.php';
require_once 'includes/db_connect.php';
require_once 'config.php';
?>

<?php include 'includes/header.php'; ?>
<h2 class="mb-4">Dashboard</h2>
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Farmers</h5>
                <?php
                $result = $conn->query("SELECT COUNT(*) AS total FROM farmers");
                $row = $result->fetch_assoc();
                ?>
                <p class="display-4"><?= $row['total'] ?></p>
                <a href="modules/farmers.php" class="text-white">View Farmers</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Deliveries</h5>
                <?php
                $result = $conn->query("SELECT COUNT(*) AS total FROM deliveries");
                $row = $result->fetch_assoc();
                ?>
                <p class="display-4"><?= $row['total'] ?></p>
                <a href="modules/deliveries.php" class="text-white">View Deliveries</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Payments</h5>
                <?php
                $result = $conn->query("SELECT SUM(amount) AS total FROM payments");
                $row = $result->fetch_assoc();
                ?>
                <p class="display-4">KES <?= number_format($row['total'], 2) ?></p>
                <a href="modules/payments.php" class="text-white">View Payments</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Inventory</h5>
                <?php
                $result = $conn->query("SELECT SUM(quantity) AS total FROM inventory");
                $row = $result->fetch_assoc();
                ?>
                <p class="display-4"><?= number_format($row['total'], 2) ?> kg</p>
                <a href="modules/inventory.php" class="text-white">View Inventory</a>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Recent Activities</h5>
    </div>
    <div class="card-body">
        <div class="list-group">
            <?php
            $sql = "SELECT 
                        'New Farmer' AS type, first_name, last_name, join_date AS date 
                    FROM farmers 
                    UNION
                    SELECT 
                        'Delivery' AS type, CONCAT(f.first_name, ' ', f.last_name) AS name, 
                        CONCAT(d.weight, ' kg - ', d.grade) AS details, d.delivery_date AS date 
                    FROM deliveries d
                    JOIN farmers f ON d.farmer_id = f.id
                    UNION
                    SELECT 
                        'Payment' AS type, CONCAT(f.first_name, ' ', f.last_name) AS name, 
                        CONCAT('KES ', p.amount) AS details, p.payment_date AS date 
                    FROM payments p
                    JOIN farmers f ON p.farmer_id = f.id
                    ORDER BY date DESC LIMIT 10";
            
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">' . $row['type'] . '</h6>
                                <small>' . $row['date'] . '</small>
                            </div>
                            <p class="mb-1">' . $row['first_name'] . ' ' . $row['last_name'] . '</p>
                          </div>';
                }
            } else {
                echo '<p class="text-center">No recent activities</p>';
            }
            ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>