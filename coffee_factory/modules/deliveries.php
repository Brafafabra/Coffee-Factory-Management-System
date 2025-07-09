<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../config.php';

$page_title = "Coffee Deliveries";
$page_subtitle = "Record and track coffee deliveries";
$page_actions = '
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newDeliveryModal">
        <i class="bi bi-plus-circle"></i> New Delivery
    </button>
';






// Record new delivery
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_delivery'])) {
    $farmer_id = $_POST['farmer_id'];
    $weight = $_POST['weight'];
    $grade = $_POST['grade'];
    $delivery_date = $_POST['delivery_date'];
    $recorded_by = $_SESSION['user_id'];
    
    $sql = "INSERT INTO deliveries (farmer_id, weight, grade, delivery_date, recorded_by) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idssi", $farmer_id, $weight, $grade, $delivery_date, $recorded_by);
    
    if ($stmt->execute()) {
        $success = "Delivery recorded successfully!";
    } else {
        $error = "Error recording delivery: " . $conn->error;
    }
}

// Get farmers for dropdown
$farmers = $conn->query("SELECT id, first_name, last_name, membership_no FROM farmers ORDER BY last_name");

// Get deliveries
$sql = "SELECT d.id, d.delivery_date, d.weight, d.grade, 
               f.first_name, f.last_name, f.membership_no
        FROM deliveries d
        JOIN farmers f ON d.farmer_id = f.id
        ORDER BY d.delivery_date DESC";
$deliveries = $conn->query($sql);
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">Coffee Deliveries</h2>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Record New Delivery</h5>
    </div>
    <div class="card-body">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="farmer_id" class="form-label">Farmer</label>
                    <select class="form-select" id="farmer_id" name="farmer_id" required>
                        <option value="">Select Farmer</option>
                        <?php while ($farmer = $farmers->fetch_assoc()): ?>
                            <option value="<?= $farmer['id'] ?>">
                                <?= htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name'] . ' (' . $farmer['membership_no'] . ')') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="weight" class="form-label">Weight (kg)</label>
                    <input type="number" step="0.01" class="form-control" id="weight" name="weight" min="0.01" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="grade" class="form-label">Grade</label>
                    <select class="form-select" id="grade" name="grade" required>
                        <option value="">Select Grade</option>
                        <option value="AA">AA</option>
                        <option value="AB">AB</option>
                        <option value="C">C</option>
                        <option value="PB">PB</option>
                        <option value="E">E</option>
                        <option value="TT">TT</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="delivery_date" class="form-label">Delivery Date</label>
                    <input type="date" class="form-control" id="delivery_date" name="delivery_date" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <button type="submit" name="record_delivery" class="btn btn-primary">Record Delivery</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Recent Deliveries</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Farmer</th>
                        <th>Membership No</th>
                        <th>Weight (kg)</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($deliveries->num_rows > 0): ?>
                        <?php while ($delivery = $deliveries->fetch_assoc()): ?>
                            <tr>
                                <td><?= $delivery['id'] ?></td>
                                <td><?= $delivery['delivery_date'] ?></td>
                                <td><?= htmlspecialchars($delivery['first_name'] . ' ' . $delivery['last_name']) ?></td>
                                <td><?= htmlspecialchars($delivery['membership_no']) ?></td>
                                <td><?= number_format($delivery['weight'], 2) ?></td>
                                <td><?= $delivery['grade'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No deliveries recorded yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>