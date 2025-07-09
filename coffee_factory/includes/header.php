<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee Factory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body style="background-image: url('images/coffee.jpg'); background-size: cover; background-repeat: no-repeat;">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container" style="max-width: 1200px; background-color: light-blue; text-color: white; text
        -decoration: bold; text-decoration: uppercase;" >
            <a class="navbar-brand" href="<?= BASE_URL ?>dashboard.php">Mbilini Coffee Factory</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav" >
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>modules/farmers.php">Farmers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>modules/deliveries.php">Deliveries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>modules/payments.php">Payments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>modules/inventory.php">Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>modules/reports.php">Reports</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <span class="navbar-text me-3">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?> (<?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'guest')) ?>)
                    </span>
                    <a href="<?= BASE_URL ?>logout.php" class="btn btn-danger">
    <i class="fas fa-sign-out-alt"></i> Logout
</a>

                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4">