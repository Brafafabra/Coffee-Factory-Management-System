<?php
require_once '../includes/auth.php';
require_once '../includes/db_connect.php';
require_once '../config.php';

// Add new farmer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_farmer'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $membership_no = $_POST['membership_no'];
    $phone = $_POST['phone'];
    
    $sql = "INSERT INTO farmers (first_name, last_name, membership_no, phone, join_date) 
            VALUES (?, ?, ?, ?, CURDATE())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $first_name, $last_name, $membership_no, $phone);
    
    if ($stmt->execute()) {
        $success = "Farmer added successfully!";
    } else {
        $error = "Error adding farmer: " . $conn->error;
    }
}

// Search farmers
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM farmers 
        WHERE first_name LIKE ? OR last_name LIKE ? OR membership_no LIKE ?
        ORDER BY last_name, first_name";
$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">Farmer Management</h2>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Add New Farmer</h5>
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
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="membership_no" class="form-label">Membership Number</label>
                    <input type="text" class="form-control" id="membership_no" name="membership_no" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone">
                </div>
            </div>
            <button type="submit" name="add_farmer" class="btn btn-primary">Add Farmer</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Farmer List</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <form method="GET" class="d-flex">
                <input type="text" class="form-control me-2" name="search" placeholder="Search farmers..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-outline-primary">Search</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Membership No</th>
                        <th>Phone</th>
                        <th>Join Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                <td><?= htmlspecialchars($row['membership_no']) ?></td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td><?= $row['join_date'] ?></td>
                                <td>
                                          <a href="farmer_details.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info" title="View Details">
            <i class="bi bi-eye-fill me-1"></i> View
        </a>
        <a href="edit_farmer.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
            <i class="bi bi-pencil-fill me-1"></i> Edit
        </a>
        <button class="btn btn-sm btn-danger delete-farmer" title="Delete" data-id="<?= $row['id'] ?>">
    <i class="bi bi-trash-fill me-1"></i> Delete
</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No farmers found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>