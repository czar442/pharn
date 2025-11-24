<?php
include '../includes/auth_check.php';
include '../includes/db.php';

// Handle delete request
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Customer deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete customer: " . $stmt->error;
    }
    $stmt->close();
    header("Location: view_customers.php");
    exit();
}

// Search logic
$search = '';
if (isset($_GET['q'])) {
    $search = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
    $stmt = $conn->prepare("SELECT * FROM customers WHERE full_name LIKE ? OR phone_number LIKE ? OR id_number LIKE ? ORDER BY created_at DESC");
    $likeSearch = "%$search%";
    $stmt->bind_param("sss", $likeSearch, $likeSearch, $likeSearch);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM customers ORDER BY created_at DESC");
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>View Customers - Transacash</title>
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
    .table {
      background-color: rgba(255, 255, 255, 0.1);
    }
    .table-dark {
      background-color: rgba(0, 0, 0, 0.5);
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
    .modal-content {
      background-color: #fff;
      color: #000;
    }
    .btn-primary, .btn-warning, .btn-danger, .btn-secondary {
      transition: background-color 0.3s;
    }
    .btn-primary:hover, .btn-warning:hover, .btn-danger:hover, .btn-secondary:hover {
      opacity: 0.9;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand" href="#">Transacash</a>
  <div class="ms-auto">
    <a href="../logout.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
  </div>
</nav>

<div class="d-flex">
  <!-- Sidebar Left -->
  <div class="col-md-3 col-lg-2">
    <?php include '../includes/sidebar.php'; ?>
  </div>

  <!-- Main Content Right -->
  <div class="col-md-9 col-lg-10 p-4">
    <h4><i class="bi bi-person-lines-fill me-2"></i>Customer List</h4>
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

    <!-- Search form -->
    <form method="get" class="row mb-3">
      <div class="col-md-5">
        <div class="input-group">
          <span class="input-group-text bg-dark border-dark text-white"><i class="bi bi-search"></i></span>
          <input type="text" name="q" class="form-control" placeholder="Search by name, phone, or ID" value="<?= htmlspecialchars($search ?? '') ?>">
        </div>
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary"><i class="bi bi-search me-2"></i>Search</button>
      </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-bordered table-sm">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Full Name</th>
            <th>Phone</th>
            <th>ID/Passport</th>
            <th>ID Image</th>
            <!-- <th>Actions</th> -->
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows === 0): ?>
            <tr>
              <td colspan="6" class="text-center">No customers found.</td>
            </tr>
          <?php else: ?>
            <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['phone_number']) ?></td>
                <td><?= htmlspecialchars($row['id_number']) ?></td>
                <td>
                  <?php if ($row['id_image']): ?>
                    <a href="<?= htmlspecialchars($row['id_image']) ?>" target="_blank" class="text-white"><i class="bi bi-image me-2"></i>View</a>
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
                <!-- <td>
                  <a href="edit_customers.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil me-2"></i>Edit</a>
                  <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>">
                    <i class="bi bi-trash me-2"></i>Delete
                  </button> -->

                  <!-- Delete Modal -->
                  <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $row['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="deleteModalLabel<?= $row['id'] ?>"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          Are you sure you want to delete customer <strong><?= htmlspecialchars($row['full_name']) ?></strong>?
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-2"></i>Cancel</button>
                          <a href="view_customers.php?delete=<?= $row['id'] ?>" class="btn btn-danger"><i class="bi bi-trash me-2"></i>Yes, Delete</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>