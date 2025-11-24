<?php
include '../includes/auth_check.php';
include '../includes/db.php';

if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Access denied. Admins only.";
    header('Location: transaction.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Users - Transacash</title>
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
    .table {
      background-color: rgba(255, 255, 255, 0.1);
    }
    .table-dark {
      background-color: rgba(0, 0, 0, 0.5);
    }
    .btn-primary, .btn-danger, .btn-warning, .btn-secondary {
      transition: background-color 0.3s;
    }
    .btn-primary:hover {
      background-color: #0d6efd;
    }
    .btn-danger:hover {
      background-color: #c82333;
    }
    .btn-warning:hover {
      background-color: #e0a800;
    }
    .btn-secondary:hover {
      background-color: #5a6268;
    }
    .modal-content {
      background-color: rgba(255, 255, 255, 0.95);
    }
    .alert {
      background-color: rgba(255, 255, 255, 0.2);
      color: white;
      border: none;
    }
    .alert-success {
      background-color: rgba(40, 167, 69, 0.3);
    }
    .alert-danger {
      background-color: rgba(220, 53, 69, 0.3);
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
    <h4><i class="bi bi-people-fill me-2"></i>Manage Users</h4>

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

    <a href="add_user.php" class="btn btn-primary mb-3"><i class="bi bi-person-plus me-2"></i>Add New User</a>
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $res = $conn->query("SELECT id, email, role FROM users ORDER BY id ASC");
          $i = 1;
          while ($row = $res->fetch_assoc()):
          ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td>
              <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-2"></i>Edit
              </a>
              <?php if ($row['id'] !== $_SESSION['user_id']): ?>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>">
                  <i class="bi bi-trash me-2"></i>Delete
                </button>
                <!-- Delete Confirmation Modal -->
                <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $row['id'] ?>" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="post" action="delete_user.php">
                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                        <div class="modal-header">
                          <h5 class="modal-title" id="deleteModalLabel<?= $row['id'] ?>">Confirm Deletion</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          Are you sure you want to delete user <strong><?= htmlspecialchars($row['id']) ?></strong>?
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-danger">Delete</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>