<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../config.php';

// Update inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inventory'])) {
    $parchment = floatval($_POST['parchment']);
    $green_coffee = floatval($_POST['green_coffee']);
    
    // Update parchment
    $stmt = $conn->prepare("UPDATE inventory SET quantity = ? WHERE coffee_type = 'Parchment'");
    $stmt->bind_param("d", $parchment);
    $stmt->execute();
    
    // Update green coffee
    $stmt = $conn->prepare("UPDATE inventory SET quantity = ? WHERE coffee_type = 'Green Coffee'");
    $stmt->bind_param("d", $green_coffee);
    $stmt->execute();
    
    $success = "Inventory updated successfully!";
}

// Get current inventory
$inventory = $conn->query("SELECT * FROM inventory");
$inventory_data = [];
while ($row = $inventory->fetch_assoc()) {
    $inventory_data[$row['coffee_type']] = $row['quantity'];
}
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">Inventory Management</h2>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Current Inventory</h5>
            </div>
            <div class="card-body">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="parchment" class="form-label">Parchment Coffee (kg)</label>
                        <input type="number" step="0.01" class="form-control" id="parchment" name="parchment" 
                               value="<?= $inventory_data['Parchment'] ?? 0 ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="green_coffee" class="form-label">Green Coffee (kg)</label>
                        <input type="number" step="0.01" class="form-control" id="green_coffee" name="green_coffee" 
                               value="<?= $inventory_data['Green Coffee'] ?? 0 ?>" required>
                    </div>
                    <button type="submit" name="update_inventory" class="btn btn-primary">Update Inventory</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Inventory Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span>Parchment Coffee:</span>
                    <strong><?= number_format($inventory_data['Parchment'] ?? 0, 2) ?> kg</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Green Coffee:</span>
                    <strong><?= number_format($inventory_data['Green Coffee'] ?? 0, 2) ?> kg</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Total Inventory:</span>
                    <strong><?= number_format(($inventory_data['Parchment'] ?? 0) + ($inventory_data['Green Coffee'] ?? 0), 2) ?> kg</strong>
                </div>
                <hr>
                <div class="text-center">
                    <div class="mb-2">
                        <canvas id="inventoryChart" width="300" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('inventoryChart').getContext('2d');
    const inventoryChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Parchment Coffee', 'Green Coffee'],
            datasets: [{
                data: [
                    <?= $inventory_data['Parchment'] ?? 0 ?>,
                    <?= $inventory_data['Green Coffee'] ?? 0 ?>
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(75, 192, 192, 0.7)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
<?php include '../includes/footer.php'; ?>