<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../config.php';

$page_title = "Add New Farmer";
$page_subtitle = "Register a coffee farmer into the system";

// Initialize variables and error messages
$first_name = $last_name = $membership_no = $phone = $join_date = $address = $email = $farm_size = $location = "";
$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $membership_no = trim($_POST['membership_no']);
    $phone = trim($_POST['phone']);
    $join_date = trim($_POST['join_date']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $farm_size = trim($_POST['farm_size']);
    $location = trim($_POST['location']);

    // Validation
    if (empty($first_name)) $errors['first_name'] = "First name is required";
    if (empty($last_name)) $errors['last_name'] = "Last name is required";
    if (empty($membership_no)) $errors['membership_no'] = "Membership number is required";
    if (empty($join_date)) $errors['join_date'] = "Join date is required";
    
    // Validate membership number uniqueness
    $stmt = $conn->prepare("SELECT id FROM farmers WHERE membership_no = ?");
    $stmt->bind_param("s", $membership_no);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $errors['membership_no'] = "Membership number already exists";
    $stmt->close();

    // If no errors, insert into database
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO farmers 
            (first_name, last_name, membership_no, phone, join_date, address, email, farm_size, location) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssds", $first_name, $last_name, $membership_no, $phone, $join_date, $address, $email, $farm_size, $location);
        
        if ($stmt->execute()) {
            $success = true;
            // Reset form values on success
            $first_name = $last_name = $membership_no = $phone = $join_date = $address = $email = $farm_size = $location = "";
        } else {
            $errors['database'] = "Error saving farmer: " . $stmt->error;
        }
        $stmt->close();
    }
}

include '../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-plus fs-3 me-3"></i>
                        <div>
                            <h4 class="mb-0"><?php echo $page_title; ?></h4>
                            <p class="mb-0"><?php echo $page_subtitle; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                            <div>
                                <h5 class="mb-0">Farmer registered successfully!</h5>
                                <p class="mb-0">You can now view the farmer's profile or add another farmer.</p>
                            </div>
                            <div class="ms-auto">
                                <a href="index.php" class="btn btn-sm btn-light me-2">
                                    <i class="bi bi-list me-1"></i> View All Farmers
                                </a>
                                <button class="btn btn-sm btn-outline-light" onclick="location.reload()">
                                    <i class="bi bi-plus-circle me-1"></i> Add Another
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors['database'])): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo $errors['database']; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="row g-3 needs-validation" novalidate>
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" 
                                   id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                            <div class="invalid-feedback">
                                <?php echo isset($errors['first_name']) ? $errors['first_name'] : 'Please provide a first name'; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" 
                                   id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                            <div class="invalid-feedback">
                                <?php echo isset($errors['last_name']) ? $errors['last_name'] : 'Please provide a last name'; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="membership_no" class="form-label">Membership Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo isset($errors['membership_no']) ? 'is-invalid' : ''; ?>" 
                                   id="membership_no" name="membership_no" value="<?php echo htmlspecialchars($membership_no); ?>" required>
                            <div class="invalid-feedback">
                                <?php echo isset($errors['membership_no']) ? $errors['membership_no'] : 'Please provide a unique membership number'; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($phone); ?>" placeholder="e.g. +255 712 345 678">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email); ?>" placeholder="farmer@example.com">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="join_date" class="form-label">Join Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control <?php echo isset($errors['join_date']) ? 'is-invalid' : ''; ?>" 
                                   id="join_date" name="join_date" value="<?php echo htmlspecialchars($join_date); ?>" required>
                            <div class="invalid-feedback">
                                <?php echo isset($errors['join_date']) ? $errors['join_date'] : 'Please select a join date'; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="farm_size" class="form-label">Farm Size (acres)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="farm_size" name="farm_size" 
                                       value="<?php echo htmlspecialchars($farm_size); ?>" min="0" step="0.1">
                                <span class="input-group-text">acres</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?php echo htmlspecialchars($location); ?>" placeholder="e.g. Kilimanjaro Region">
                        </div>
                        
                        <div class="col-12">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" 
                                      rows="2" placeholder="Full address"><?php echo htmlspecialchars($address); ?></textarea>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-person-plus me-2"></i> Register Farmer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="card-footer text-center text-muted py-3">
                    <small>
                        <i class="bi bi-info-circle me-1"></i> 
                        Fields marked with <span class="text-danger">*</span> are required
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Bootstrap validation
(function () {
    'use strict'
    
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')
    
    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                
                form.classList.add('was-validated')
            }, false)
        })
})();

// Set today as default for join date
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('join_date').value = today;
});
</script>

<?php include '../includes/footer.php'; ?>