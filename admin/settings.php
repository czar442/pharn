<?php
include '../includes/auth_check.php';
include '../includes/db.php';

// Restrict to admin
if ($_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usdt_to_ugx = (float)$_POST['usdt_to_ugx'];
    $usd_to_usdt = (float)$_POST['usd_to_usdt'];

    // Validate inputs
    if ($usdt_to_ugx <= 0 || $usd_to_usdt <= 0) {
        $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Rates must be positive numbers.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    } else {
        $stmt = $conn->prepare("UPDATE settings SET usdt_to_ugx = ?, usd_to_usdt = ? WHERE id = 1");
        $stmt->bind_param("dd", $usdt_to_ugx, $usd_to_usdt);
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success alert-dismissible fade show" role="alert">Settings updated successfully.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        } else {
            $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Failed to update settings: ' . htmlspecialchars($conn->error) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        }
        $stmt->close();
    }
}

// Fetch current settings
$stmt = $conn->prepare("SELECT usdt_to_ugx, usd_to_usdt FROM settings WHERE id = 1");
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();

if (!$settings) {
    die("Settings not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Exchange Settings - Transacash</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body {
      background: linear-gradient(135deg, #4b006e, #1e0050);
      color: white;
      min-height: 100vh;
    }
    #sidebar {
      min-height: 100vh;
      background: linear-gradient(135deg, #4b006e, #1e0050);
    }
    .form-section {
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      padding: 30px;
    }
    .form-control {
      background-color: rgba(255, 255, 255, 0.1);
      color: white;
      border: 1px solid #ccc;
    }
    .form-control:focus {
      background-color: rgba(255, 255, 255, 0.2);
      color: white;
      border-color: #4b006e;
      box-shadow: 0 0 5px rgba(75, 0, 110, 0.5);
    }
    .btn-primary {
      transition: background-color 0.3s;
    }
    .btn-primary:hover {
      background-color: #0d6efd;
    }
    .alert-success {
      background-color: rgba(40, 167, 69, 0.3);
      border: none;
      color: white;
    }
    .alert-danger {
      background-color: rgba(220, 53, 69, 0.3);
      border: none;
      color: white;
    }
    label {
      color: white;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand" href="#"><i class="bi bi-wallet2 me-2"></i>Transacash</a>
  <div class="ms-auto">
    <a href="../logout.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
  </div>
</nav>

<div class="d-flex">
  <!-- Sidebar -->
  <div class="col-md-3 col-lg-2">
    <?php include '../includes/sidebar.php'; ?>
  </div>

  <!-- Main Content -->
  <div class="col-md-9 col-lg-10 p-4">
    <h4><i class="bi bi-gear-fill me-2"></i>Exchange Rate Settings</h4>
    <?= $msg ?>
    <div class="form-section">
      <form method="post" class="row g-3">
        <div class="col-md-6">
          <label for="usdt_to_ugx" class="form-label">USDT to UGX Rate</label>
          <input type="number" name="usdt_to_ugx" id="usdt_to_ugx" class="form-control" value="<?= htmlspecialchars($settings['usdt_to_ugx']) ?>" step="0.01" min="0" required>
        </div>
        <div class="col-md-6">
          <label for="usd_to_usdt" class="form-label">USD to USDT Rate</label>
          <input type="number" name="usd_to_usdt" id="usd_to_usdt" class="form-control" value="<?= htmlspecialchars($settings['usd_to_usdt']) ?>" step="0.01" min="0" required>
        </div>
        <div class="col-12 text-end">
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>