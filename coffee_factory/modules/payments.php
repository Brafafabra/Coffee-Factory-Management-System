<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../config.php';

// Define payment rates per grade
$rates = [
    'AA' => 350,
    'AB' => 320,
    'C' => 300,
    'PB' => 310,
    'E' => 340,
    'TT' => 290
];

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $delivery_id = $_POST['delivery_id'];
    $payment_date = $_POST['payment_date'];
    $processed_by = $_SESSION['user_id'];
    
    // Get delivery details
    $stmt = $conn->prepare("SELECT farmer_id, weight, grade FROM deliveries WHERE id = ?");
    $stmt->bind_param("i", $delivery_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $delivery = $result->fetch_assoc();
        $farmer_id = $delivery['farmer_id'];
        $weight = $delivery['weight'];
        $grade = $delivery['grade'];
        $amount = $weight * $rates[$grade];
        
        // Insert payment
        $stmt = $conn->prepare("INSERT INTO payments (farmer_id, delivery_id, amount, payment_date, processed_by) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iidsi", $farmer_id, $delivery_id, $amount, $payment_date, $processed_by);
        
        if ($stmt->execute()) {
            $success = "Payment processed successfully! Amount: KES " . number_format($amount, 2);
        } else {
            $error = "Error processing payment: " . $conn->error;
        }
    } else {
        $error = "Delivery not found";
    }
}

// Get unpaid deliveries
$unpaid_deliveries = $conn->query("
    SELECT d.id, d.delivery_date, d.weight, d.grade, 
           f.first_name, f.last_name, f.membership_no
    FROM deliveries d
    JOIN farmers f ON d.farmer_id = f.id
    LEFT JOIN payments p ON d.id = p.delivery_id
    WHERE p.id IS NULL
    ORDER BY d.delivery_date
");

// Get payment history
$payments = $conn->query("
    SELECT p.id, p.payment_date, p.amount, 
           d.weight, d.grade,
           f.first_name, f.last_name, f.membership_no
    FROM payments p
    JOIN deliveries d ON p.delivery_id = d.id
    JOIN farmers f ON p.farmer_id = f.id
    ORDER BY p.payment_date DESC
");
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">Payment Processing</h2>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Process Payment</h5>
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
                    <label for="delivery_id" class="form-label">Select Delivery</label>
                    <select class="form-select" id="delivery_id" name="delivery_id" required>
                        <option value="">Select Delivery</option>
                        <?php while ($delivery = $unpaid_deliveries->fetch_assoc()): ?>
                            <option value="<?= $delivery['id'] ?>" 
                                    data-weight="<?= $delivery['weight'] ?>" 
                                    data-grade="<?= $delivery['grade'] ?>">
                                <?= $delivery['delivery_date'] ?> - 
                                <?= htmlspecialchars($delivery['first_name'] . ' ' . $delivery['last_name']) ?> - 
                                <?= number_format($delivery['weight'], 2) ?> kg - 
                                Grade: <?= $delivery['grade'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="weight" class="form-label">Weight (kg)</label>
                    <input type="text" class="form-control" id="weight" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="grade" class="form-label">Grade</label>
                    <input type="text" class="form-control" id="grade" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="rate" class="form-label">Rate (KES/kg)</label>
                    <input type="text" class="form-control" id="rate" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="amount" class="form-label">Amount (KES)</label>
                    <input type="text" class="form-control" id="amount" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="payment_date" class="form-label">Payment Date</label>
                    <input type="date" class="form-control" id="payment_date" name="payment_date" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <button type="submit" name="process_payment" class="btn btn-primary">Process Payment</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Payment History</h5>
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
                        <th>Amount (KES)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($payments->num_rows > 0): ?>
                        <?php while ($payment = $payments->fetch_assoc()): ?>
                            <tr>
                                <td><?= $payment['id'] ?></td>
                                <td><?= $payment['payment_date'] ?></td>
                                <td><?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) ?></td>
                                <td><?= htmlspecialchars($payment['membership_no']) ?></td>
                                <td><?= number_format($payment['weight'], 2) ?></td>
                                <td><?= $payment['grade'] ?></td>
                                <td><?= number_format($payment['amount'], 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No payments processed yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deliverySelect = document.getElementById('delivery_id');
    const weightInput = document.getElementById('weight');
    const gradeInput = document.getElementById('grade');
    const rateInput = document.getElementById('rate');
    const amountInput = document.getElementById('amount');
    
    const rates = <?= json_encode($rates) ?>;
    
    deliverySelect.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            const weight = selectedOption.getAttribute('data-weight');
            const grade = selectedOption.getAttribute('data-grade');
            const rate = rates[grade];
            const amount = weight * rate;
            
            weightInput.value = weight;
            gradeInput.value = grade;
            rateInput.value = rate.toLocaleString();
            amountInput.value = amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        } else {
            weightInput.value = '';
            gradeInput.value = '';
            rateInput.value = '';
            amountInput.value = '';
        }
    });
});
</script>
<?php include '../includes/footer.php'; ?>