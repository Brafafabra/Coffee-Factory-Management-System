<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid farmer ID";
    header("Location: farmers.php");
    exit();
}

$farmer_id = (int)$_GET['id'];

// Get farmer details
$stmt = $conn->prepare("SELECT * FROM farmers WHERE id = ?");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Farmer not found";
    header("Location: farmers.php");
    exit();
}

$farmer = $result->fetch_assoc();

// Check for success/error messages
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

$page_title = "Farmer: " . htmlspecialchars($farmer['first_name'] . " " . $farmer['last_name']);
$page_subtitle = "Details and activity history";
$page_actions = '
    <a href="edit_farmer.php?id=' . $farmer_id . '" class="btn btn-warning">
        <i class="bi bi-pencil"></i> Edit
    </a>
    <a href="farmers.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Farmers
    </a>
';

// Get farmer's deliveries
$deliveries = $conn->query("
    SELECT d.id, d.delivery_date, d.weight, d.grade
    FROM deliveries d
    WHERE d.farmer_id = $farmer_id
    ORDER BY d.delivery_date DESC
    LIMIT 10
");

// Get farmer's payments
$payments = $conn->query("
    SELECT p.id, p.payment_date, p.amount, d.weight, d.grade
    FROM payments p
    JOIN deliveries d ON p.delivery_id = d.id
    WHERE p.farmer_id = $farmer_id
    ORDER BY p.payment_date DESC
    LIMIT 10
");

include '../includes/header.php';
?>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?= $success_message ?></div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?= $error_message ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Farmer Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Full Name</h6>
                    <p><?= htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name']) ?></p>
                </div>
                <div class="mb-3">
                    <h6>Membership Number</h6>
                    <p><?= htmlspecialchars($farmer['membership_no']) ?></p>
                </div>
                <div class="mb-3">
                    <h6>Phone Number</h6>
                    <p><?= !empty($farmer['phone']) ? htmlspecialchars($farmer['phone']) : 'N/A' ?></p>
                </div>
                <div class="mb-3">
                    <h6>Join Date</h6>
                    <p><?= date('F j, Y', strtotime($farmer['join_date'])) ?></p>
                </div>
                <div class="mt-4">
                    <a href="edit_farmer.php?id=<?= $farmer_id ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit Information
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Recent Deliveries</h5>
            </div>
            <div class="card-body">
                <?php if ($deliveries->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Weight (kg)</th>
                                    <th>Grade</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($delivery = $deliveries->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date('M j, Y', strtotime($delivery['delivery_date'])) ?></td>
                                        <td><?= number_format($delivery['weight'], 2) ?></td>
                                        <td><?= $delivery['grade'] ?></td>
                                        <td>
                                            <a href="delivery_details.php?id=<?= $delivery['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="deliveries.php?farmer_id=<?= $farmer_id ?>" class="btn btn-sm btn-outline-primary">
                        View All Deliveries
                    </a>
                <?php else: ?>
                    <div class="alert alert-info mb-0">No deliveries recorded for this farmer.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Recent Payments</h5>
            </div>
            <div class="card-body">
                <?php if ($payments->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount (KES)</th>
                                    <th>Weight (kg)</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($payment = $payments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date('M j, Y', strtotime($payment['payment_date'])) ?></td>
                                        <td><?= number_format($payment['amount'], 2) ?></td>
                                        <td><?= number_format($payment['weight'], 2) ?></td>
                                        <td><?= $payment['grade'] ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="payments.php?farmer_id=<?= $farmer_id ?>" class="btn btn-sm btn-outline-primary">
                        View All Payments
                    </a>
                <?php else: ?>
                    <div class="alert alert-info mb-0">No payments recorded for this farmer.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>