<?php
session_start();
require 'db.php';
if(!isset($_SESSION['user_id'])) header("Location: index.php");

// Fetch sales
$sales = $pdo->query("
    SELECT s.id, s.invoice_id, c.name as customer, s.total, s.paid, s.due, s.created_at 
    FROM sales s 
    LEFT JOIN customers c ON s.customer_id=c.id 
    ORDER BY s.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sales - Pharmacy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<style>
body { overflow-x: hidden; }
.sidebar { min-height: 100vh; background-color: #0d6efd; color: white; padding-top: 1rem; }
.sidebar a { color: white; text-decoration: none; display: block; padding: 0.75rem 1rem; }
.sidebar a:hover { background-color: rgba(255,255,255,0.1); }
.content { margin-left: 220px; padding: 20px; }
</style>
</head>
<body>
<?php include 'topbar.php'; ?>
<div class="d-flex">
  <?php include 'sidebar.php'; ?>
  <div class="content-wrapper">
 <div class="content-inner">
    <div class="container mt-3">
      <h3>Sales</h3>
      <table class="table table-bordered table-striped" id="salesTable">
        <thead>
          <tr>
            <th>Invoice ID</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Due</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($sales as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['invoice_id']) ?></td>
            <td><?= htmlspecialchars($s['customer']) ?></td>
            <td>$<?= number_format($s['total'], 2) ?></td>
            <td>$<?= number_format($s['paid'], 2) ?></td>
            <td>$<?= number_format($s['due'], 2) ?></td>
            <td><?= htmlspecialchars($s['created_at']) ?></td>
            <td>
              <a href="view_sale.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary">
                View Details
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
    $('#salesTable').DataTable();
});
</script>
</div>
</div>
</body>
</html>
