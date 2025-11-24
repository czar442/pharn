<?php
include '../includes/auth_check.php';
include '../includes/db.php';

if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Access denied. Admins only.";
    header('Location: transaction.php');
    exit();
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
    $_SESSION['error'] = "Invalid user ID.";
    header('Location: users.php');
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT id, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    $_SESSION['error'] = "User not found.";
    header('Location: users.php');
    exit();
}
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['id']);
    $email = trim($_POST['email']);
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
    $password = !empty($_POST['password']) ? $_POST['password'] : null;

    // Validate inputs
    if (empty($username) || empty($email)) {
        $_SESSION['error'] = "Username and email are required.";
        header("Location: edit_user.php?id=$user_id");
        exit();
    }

    // Check for email uniqueness (excluding current user)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Email already exists.";
        header("Location: edit_user.php?id=$user_id");
        exit();
    }
    $stmt->close();

    // Update user
    if ($password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $username, $email, $role, $hashed_password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $role, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "User updated successfully.";
        header('Location: users.php');
    } else {
        $_SESSION['error'] = "Failed to update user: " . $conn->error;
        header("Location: edit_user.php?id=$user_id");
    }
    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit User - Transacash</title>
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
    .form-control, .form-select {
      background-color: rgba(255, 255, 255, 0.1);
      border: 1px solid #ccc;
      color: white;
    }
    .form-control:focus, .form-select:focus {
      background-color: rgba(255, 255, 255, 0.2);
      color: white;
      border-color: #4b006e;
      box-shadow: 0 0 5px rgba(75, 0, 110, 0.5);
    }
    .btn-primary, .btn-secondary {
      transition: background-color 0.3s;
    }
    .btn-primary:hover {
      background-color: #0d6efd;
    }
    .btn-secondary:hover {
      background-color: #5a6268;
    }
    .alert {
      background-color: rgba(255, 255, 255, 0.2);
      color: white;
      border: none;
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
    <h4><i class="bi bi-person-gear me-2"></i>Edit User</h4>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="form-section">
      <form method="post" class="row g-3">
        <div class="col-md-6">
          <label for="username" class="form-label">Username</label>
          <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($user['id']) ?>" required>
        </div>
        <div class="col-md-6">
          <label for="email" class="form-label">Email</label>
          <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="col-md-6">
          <label for="role" class="form-label">Role</label>
          <select name="role" id="role" class="form-select" required>
            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
          </select>
        </div>
        <div class="col-md-6">
          <label for="password" class="form-label">New Password (optional)</label>
          <input type="password" name="password" id="password" class="form-control" placeholder="Enter new password">
        </div>
        <div class="col-12 text-end">
          <a href="users.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Cancel</a>
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