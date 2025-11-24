<?php 
include '../includes/auth_check.php'; 
include '../includes/db.php';
if ($_SESSION['role'] != 'admin') die("Access denied."); 
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add User - Transacash</title>
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
      max-width: 500px;
      margin: auto;
    }
    .form-control, .form-select {
      background-color: rgba(255, 255, 255, 0.1);
      color: white;
      border: 1px solid #ccc;
    }
    .form-control:focus, .form-select:focus {
      background-color: rgba(255, 255, 255, 0.2);
      color: white;
      border-color: #4b006e;
      box-shadow: 0 0 5px rgba(75, 0, 110, 0.5);
    }
    .btn-success {
      transition: background-color 0.3s;
    }
    .btn-success:hover {
      background-color: #218838;
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
    .invalid-feedback {
      display: none;
    }
    .is-invalid ~ .invalid-feedback {
      display: block;
    }
  </style>
</head>
<body>
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
    <div class="col-md-9 col-lg-10 p-4 d-flex justify-content-center">
      <div class="form-section">
        <h4 class="text-center mb-4"><i class="bi bi-person-plus-fill me-2"></i>Add New User</h4>
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
        <form action="add_user_save.php" method="post" id="addUserForm" class="needs-validation" novalidate>
          <div class="mb-3">
            <label for="email" class="form-label">User Email</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="Enter user email" required>
            <div class="invalid-feedback">Please enter a valid email address.</div>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">User Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter user password" required minlength="8">
            <div class="invalid-feedback">Password must be at least 8 characters long.</div>
          </div>
          <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-select" required>
              <option value="">Select Role</option>
              <option value="admin">Admin</option>
              <option value="user">User</option>
            </select>
            <div class="invalid-feedback">Please select a role.</div>
          </div>
          <button type="submit" class="btn btn-success w-100"><i class="bi bi-save me-2"></i>Add User</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Client-side form validation
    document.getElementById('addUserForm').addEventListener('submit', function(event) {
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