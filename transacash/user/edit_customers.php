<?php
include '../includes/auth_check.php';
include '../includes/db.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Customer ID is required.";
    header("Location: view_customers.php");
    exit();
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
$stmt->close();

if (!$customer) {
    $_SESSION['error'] = "Customer not found.";
    header("Location: view_customers.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $id_number = filter_input(INPUT_POST, 'id_number', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (!$full_name || !$phone_number || !$id_number) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: edit_customer.php?id=$id");
        exit();
    }

    // Handle image upload
    $targetFile = $customer['id_image'];
    if ($_FILES['id_image']['name']) {
        $targetDir = "Uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        $fileName = basename($_FILES["id_image"]["name"]);
        $targetFile = $targetDir . time() . "_" . $fileName;

        if (!move_uploaded_file($_FILES["id_image"]["tmp_name"], $targetFile)) {
            $_SESSION['error'] = "Failed to upload image.";
            header("Location: edit_customer.php?id=$id");
            exit();
        }
    }

    // Update customer using prepared statement
    $stmt = $conn->prepare("UPDATE customers SET full_name = ?, phone_number = ?, id_number = ?, id_image = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $full_name, $phone_number, $id_number, $targetFile, $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Customer updated successfully!";
        header("Location: view_customers.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update customer: " . $stmt->error;
        header("Location: edit_customer.php?id=$id");
        exit();
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Customer - Transacash</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body {
      background: linear-gradient(135deg, #4b006e, #1e0050);
      color: white;
    }
    #sidebar {
      min-height: 100vh;
      background: linear-gradient(135deg, #4b006e, #1e0050);
    }
    .form-section {
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      padding: 30px;
      max-width: 400px;
      margin: auto;
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
    .invalid-feedback {
      display: none;
    }
    .is-invalid ~ .invalid-feedback {
      display: block;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand" href="#">Transacash</a>
  <div class="ms-auto">
    <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
</nav>

<div class="d-flex">
  <!-- Sidebar -->
  <div class="col-md-3 col-lg-2">
    <?php include '../includes/sidebar.php'; ?>
  </div>

  <!-- Form Content -->
  <div class="col-md-9 col-lg-10 p-4">
    <div class="form-section">
      <h4><i class="bi bi-person-gear me-2"></i> Edit Customer</h4>
      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($_SESSION['success']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($_SESSION['error']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>
      <form method="post" enctype="multipart/form-data" id="editCustomerForm">
        <div class="mb-3">
          <label for="full_name">Full Name</label>
          <input type="text" name="full_name" id="full_name" class="form-control" required value="<?= htmlspecialchars($customer['full_name']) ?>">
          <div class="invalid-feedback">Please enter a valid full name.</div>
        </div>
        <div class="mb-3">
          <label for="phone_number">Phone Number</label>
          <input type="text" name="phone_number" id="phone_number" class="form-control" required value="<?= htmlspecialchars($customer['phone_number']) ?>">
          <div class="invalid-feedback">Please enter a valid phone number.</div>
        </div>
        <div class="mb-3">
          <label for="id_number">ID/Passport Number</label>
          <input type="text" name="id_number" id="id_number" class="form-control" required value="<?= htmlspecialchars($customer['id_number']) ?>">
          <div class="invalid-feedback">Please enter a valid ID/passport number.</div>
        </div>
        <div class="mb-3">
          <label for="id_image">Replace National ID Image (optional)</label>
          <?php if ($customer['id_image']): ?>
            <div class="mb-2">
              <a href="<?= htmlspecialchars($customer['id_image']) ?>" target="_blank" class="text-white"><i class="bi bi-image me-2"></i>View Current Image</a>
            </div>
          <?php endif; ?>
          <input type="file" name="id_image" id="id_image" class="form-control" accept="image/*">
        </div>
        <div class="d-flex justify-content-end gap-2">
          <button type="submit" class="btn btn-success"><i class="bi bi-save me-2"></i>Update Customer</button>
          <a href="view_customers.php" class="btn btn-secondary"><i class="bi bi-x-circle me-2"></i>Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('editCustomerForm').addEventListener('submit', function(event) {
    const form = this;
    if (!form.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
    }
    form.classList.add('was-validated');
  });
</script>
</body>
</html>