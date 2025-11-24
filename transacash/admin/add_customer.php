<?php
include 'includes/auth_check.php';
include 'includes/db.php';

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['full_name']);
    $phone = $conn->real_escape_string($_POST['phone_number']);
    $id_number = $conn->real_escape_string($_POST['id_number']);

    // Handle file upload
    $file_path = "";
    if (isset($_FILES['id_image']) && $_FILES['id_image']['error'] === 0) {
        $target_dir = "Uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $file_name = time() . "_" . basename($_FILES["id_image"]["name"]);
        $file_path = $target_dir . $file_name;

        if (!move_uploaded_file($_FILES["id_image"]["tmp_name"], $file_path)) {
            $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Failed to upload ID image.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        }
    }

    // Save to database
    $stmt = $conn->prepare("INSERT INTO customers (full_name, phone_number, id_number, id_image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $id_number, $file_path);

    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success alert-dismissible fade show" role="alert">Customer added successfully.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    } else {
        $msg = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Failed to save customer: ' . htmlspecialchars($conn->error) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Customer - Transacash</title>
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
    <?php include 'includes/sidebar.php'; ?>
  </div>

  <!-- Main Content -->
  <div class="col-md-9 col-lg-10 p-4">
    <h4><i class="bi bi-person-plus-fill me-2"></i>Add New Customer</h4>
    <?= $msg ?>
    <div class="form-section">
      <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
          <label for="full_name" class="form-label">Customer Full Name</label>
          <input type="text" name="full_name" id="full_name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label for="phone_number" class="form-label">Phone Number</label>
          <input type="text" name="phone_number" id="phone_number" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label for="id_number" class="form-label">ID/Passport Number</label>
          <input type="text" name="id_number" id="id_number" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label for="id_image" class="form-label">Upload National ID (Scanned Image)</label>
          <input type="file" name="id_image" id="id_image" class="form-control" accept="image/*">
        </div>
        <div class="col-12 text-end">
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Add Customer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>