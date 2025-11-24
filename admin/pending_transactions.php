<?php
include '../includes/auth_check.php';
include '../includes/db.php';

// Only allow admin access
if ($_SESSION['role'] !== 'admin') {
  $_SESSION['error'] = "Access denied. This page is for admins only.";
  header('Location: transaction.php');
  exit();
}

// Approve transaction if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
  $approve_id = (int) $_POST['approve_id'];
  $stmt = $conn->prepare("UPDATE transactions SET approved = 1 WHERE id = ?");
  $stmt->bind_param("i", $approve_id);
  $stmt->execute();
  $stmt->close();
  $_SESSION['success'] = "Transaction #$approve_id approved successfully!";
  header('Location: pending_transactions.php');
  exit();
}

// Get pending transactions submitted by non-admin users
$query = "
  SELECT t.*, c.full_name AS customer_name, u.id AS submitted_by
  FROM transactions t
  JOIN customers c ON t.customer_id = c.id
  JOIN users u ON t.user_id = u.id
  WHERE t.approved = 0 AND u.role != 'admin'
  ORDER BY t.created_at DESC
";
$transactions = $conn->query($query);
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
    .btn-success, .btn-primary, .btn-secondary, .btn-info {
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
    .btn-info:hover {
      background-color: #138496;
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
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand" href="#"><i class="bi bi-wallet2 me-2"></i>Transacash</a>
  <div class="ms-auto">
    <a href="logout.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
  </div>
</nav>

<div class="d-flex">
  <!-- Sidebar Left -->
  <div class="col-md-3 col-lg-2">
    <?php include '../includes/sidebar.php'; ?>
  </div>

  <!-- Main Content Right -->
  <div class="col-md-9 col-lg-10 p-4">
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
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($transactions->num_rows > 0): ?>
            <?php while ($row = $transactions->fetch_assoc()): ?>
              <tr data-transaction='<?= json_encode([
                "id" => $row['id'],
                "customer_name" => htmlspecialchars($row['customer_name']),
                "from_currency" => htmlspecialchars($row['from_currency']),
                "from_amount" => number_format($row['from_amount'], 2),
                "to_currency" => htmlspecialchars($row['to_currency']),
                "customer_receives" => number_format($row['customer_receives'], 2),
                "submitted_by" => $row['submitted_by'],
                "created_at" => $row['created_at']
              ]) ?>'>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['from_currency']) ?> <?= number_format($row['from_amount'], 2) ?></td>
                <td><?= htmlspecialchars($row['to_currency']) ?></td>
                <td><?= number_format($row['from_amount'], 2) ?></td>
                <td><?= number_format($row['customer_receives'], 2) ?></td>
                <td><?= htmlspecialchars($row['submitted_by']) ?></td>
                <td>
                  <!-- Approve Button triggers modal -->
                  <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal<?= $row['id'] ?>">
                    <i class="bi bi-check-circle me-2"></i>Approve
                  </button>
                  <!-- Print Button -->
                  <button type="button" class="btn btn-info btn-sm print-transaction" data-transaction-id="<?= $row['id'] ?>">
                    <i class="bi bi-printer me-2"></i>Print
                  </button>

                  <!-- Modal -->
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
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center">No pending transactions.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
  $('.print-transaction').on('click', function() {
    try {
      const transactionId = $(this).data('transaction-id');
      const row = $(this).closest('tr');
      const transactionData = JSON.parse(row.attr('data-transaction'));

      const printWindow = window.open('', '_blank');
      printWindow.document.write(`
        <html>
          <head>
            <title>Transaction #${transactionData.id} - Transacash</title>
            <style>
              body { font-family: Arial, sans-serif; margin: 20px; }
              .container { max-width: 600px; margin: auto; }
              h2 { text-align: center; color: #4b006e; }
              .details { border: 1px solid #ccc; padding: 20px; border-radius: 10px; }
              .details p { margin: 10px 0; }
              .logo { text-align: center; margin-bottom: 20px; }
            </style>
          </head>
          <body>
            <div class="container">
              <div class="logo">
                <h1 style="color: #4b006e;">Transacash</h1>
              </div>
              <h2>Transaction Receipt</h2>
              <div class="details">
                <p><strong>Transaction ID:</strong> ${transactionData.id}</p>
                <p><strong>Customer:</strong> ${transactionData.customer_name}</p>
                <p><strong>From:</strong> ${transactionData.from_currency} ${transactionData.from_amount}</p>
                <p><strong>To:</strong> ${transactionData.to_currency}</p>
                <p><strong>Amount:</strong> ${transactionData.from_amount}</p>
                <p><strong>Net Received:</strong> ${transactionData.customer_receives}</p>
                <p><strong>Submitted By:</strong> ${transactionData.submitted_by}</p>
                <p><strong>Date:</strong> ${new Date(transactionData.created_at).toLocaleString()}</p>
              </div>
            </div>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.print();
    } catch (error) {
      console.error('Error parsing transaction data:', error);
      alert('Failed to print transaction. Check console for details.');
    }
  });
});
</script>
</body>
</html>