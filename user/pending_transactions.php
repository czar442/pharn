<?php
include '../includes/auth_check.php';
include '../includes/db.php';

// Approve transaction (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    if ($_SESSION['role'] !== 'admin') {
        $_SESSION['error'] = "Access denied. Only admins can approve transactions.";
        header('Location: pending_transactions.php');
        exit();
    }
    $approve_id = (int)$_POST['approve_id'];
    $stmt = $conn->prepare("UPDATE transactions SET approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $approve_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Transaction #$approve_id approved successfully!";
    } else {
        $_SESSION['error'] = "Failed to approve transaction: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
    header('Location: pending_transactions.php');
    exit();
}

// Get pending transactions from non-admins
$stmt = $conn->prepare("
    SELECT t.*, c.full_name AS customer_name, u.email AS submitted_by
    FROM transactions t
    JOIN customers c ON t.customer_id = c.id
    JOIN users u ON t.user_id = u.id
    WHERE t.approved = 0 AND u.role != 'admin'
    ORDER BY t.created_at DESC
");
$stmt->execute();
$transactions = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Pending Transactions - Transacash</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body {
      background: linear-gradient(135deg, #4b006e, #1e0050);
      color: white;
      min-height: 100vh;
      padding-top: 56px;
    }
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
    }
    .main-content {
      /* margin-left: 250px; */
      padding-left: 15px;
      padding-right: 15px;
    }
    .table {
      background-color: rgba(255, 255, 255, 0.1);
    }
    .table-dark {
      background-color: rgba(0, 0, 0, 0.5);
    }
    .btn-success, .btn-primary, .btn-secondary {
      transition: background-color 0.3s;
    }
    .btn-success:hover {
      background-color: #218838;
    }
    .btn-primary:hover {
      background-color: #0d6efd;
    }
    .btn-secondary:hover {
      background-color: #5a6268;
    }
    .modal-content {
      background-color: rgba(255, 255, 255, 0.95);
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
    @media (max-width: 768px) {
      .main-content {
        margin-left: 0;
        padding-left: 15px;
        padding-right: 15px;
      }
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
  <div class="col-md-9 col-lg-10 p-4 main-content">
    <h4><i class="bi bi-hourglass-split me-2"></i>Pending Transactions (User Submitted)</h4>

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

    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>From</th>
            <th>To</th>
            <th>Amount</th>
            <th>Net</th>
            <th>Submitted By</th>
            <?php if ($_SESSION['role'] === 'admin'): ?>
              <th>Action</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if ($transactions->num_rows > 0): ?>
            <?php while ($row = $transactions->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['from_currency']) ?> <?= number_format($row['from_amount'], 2) ?></td>
                <td><?= htmlspecialchars($row['to_currency']) ?></td>
                <td><?= number_format($row['from_amount'], 2) ?></td>
                <td><?= number_format($row['customer_receives'], 2) ?></td>
                <td><?= htmlspecialchars($row['submitted_by']) ?></td>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                  <td>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal<?= $row['id'] ?>">
                      <i class="bi bi-check-circle me-2"></i>Approve
                    </button>
                    <div class="modal fade" id="confirmModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $row['id'] ?>" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form method="post">
                            <input type="hidden" name="approve_id" value="<?= $row['id'] ?>">
                            <div class="modal-header">
                              <h5 class="modal-title" id="modalLabel<?= $row['id'] ?>">Confirm Approval</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              Are you sure you want to approve Transaction #<?= htmlspecialchars($row['id']) ?> for <?= htmlspecialchars($row['customer_name']) ?>?
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-primary">Yes, Approve</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="<?= $_SESSION['role'] === 'admin' ? '8' : '7' ?>" class="text-center">No pending transactions.</td></tr>
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