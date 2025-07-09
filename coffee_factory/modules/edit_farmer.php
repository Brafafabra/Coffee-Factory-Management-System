<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../config.php';

// Check if farmer ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid farmer ID";
    header("Location: farmers.php");
    exit();
}

$farmer_id = (int)$_GET['id'];

// Fetch farmer details
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

$page_title = "Edit Farmer: " . htmlspecialchars($farmer['first_name']) . " " . htmlspecialchars($farmer['last_name']);
$page_subtitle = "Update farmer information";
$page_actions = '
    <a href="farmer_details.php?id=' . $farmer_id . '" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Details
    </a>
';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $membership_no = trim($_POST['membership_no']);
    $phone = trim($_POST['phone']);
    
    // Validate input
    $errors = [];

    if (empty($first_name)) {
        $errors['first_name'] = "First name is required";
    } elseif (strlen($first_name) > 50) {
        $errors['first_name'] = "First name must be 50 characters or less";
    }

    if (empty($last_name)) {
        $errors['last_name'] = "Last name is required";
    } elseif (strlen($last_name) > 50) {
        $errors['last_name'] = "Last name must be 50 characters or less";
    }

    if (empty($membership_no)) {
        $errors['membership_no'] = "Membership number is required";
    } elseif (strlen($membership_no) > 20) {
        $errors['membership_no'] = "Membership number must be 20 characters or less";
    } else {
        $check_stmt = $conn->prepare("SELECT id FROM farmers WHERE membership_no = ? AND id != ?");
        $check_stmt->bind_param("si", $membership_no, $farmer_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors['membership_no'] = "This membership number is already registered";
        }
    }

    if (!empty($phone) && strlen($phone) > 15) {
        $errors['phone'] = "Phone number must be 15 characters or less";
    }

    if (empty($errors)) {
        $update_stmt = $conn->prepare("UPDATE farmers SET first_name = ?, last_name = ?, membership_no = ?, phone = ? WHERE id = ?");
        $update_stmt->bind_param("ssssi", $first_name, $last_name, $membership_no, $phone, $farmer_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_message'] = "Farmer updated successfully!";
            header("Location: farmer_details.php?id=" . $farmer_id);
            exit();
        } else {
            $error = "Error updating farmer: " . $conn->error;
        }
    }
}

include '../includes/header.php';

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Edit Farmer Details</h5>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" class="needs-validation" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First Name *</label>
                    <input type="text" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                           id="first_name" name="first_name" 
                           value="<?php echo htmlspecialchars($farmer['first_name']); ?>" required maxlength="50">
                    <?php if (isset($errors['first_name'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['first_name']; ?></div>
                    <?php else: ?>
                        <div class="invalid-feedback">Please provide a first name.</div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last Name *</label>
                    <input type="text" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                           id="last_name" name="last_name" 
                           value="<?php echo htmlspecialchars($farmer['last_name']); ?>" required maxlength="50">
                    <?php if (isset($errors['last_name'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['last_name']; ?></div>
                    <?php else: ?>
                        <div class="invalid-feedback">Please provide a last name.</div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="membership_no" class="form-label">Membership Number *</label>
                    <input type="text" class="form-control <?php echo isset($errors['membership_no']) ? 'is-invalid' : ''; ?>" 
                           id="membership_no" name="membership_no" 
                           value="<?php echo htmlspecialchars($farmer['membership_no']); ?>" required maxlength="20">
                    <?php if (isset($errors['membership_no'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['membership_no']; ?></div>
                    <?php else: ?>
                        <div class="invalid-feedback">Please provide a membership number.</div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                           id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($farmer['phone']); ?>" maxlength="15">
                    <?php if (isset($errors['phone'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Join Date</label>
                    <input type="text" class="form-control" value="<?php echo date('F j, Y', strtotime($farmer['join_date'])); ?>" readonly>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Save Changes
                    </button>
                    <a href="farmer_details.php?id=<?php echo $farmer_id; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
