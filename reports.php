<?php
session_start();
require 'db.php';
if(!isset($_SESSION['user_id'])) header("Location: index.php");

// Optional date filters
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');

// Fetch sales and expenses in date range
$stmt = $pdo->prepare("SELECT * FROM sales WHERE DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$from,$to]);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $pdo->prepare("SELECT * FROM expenses WHERE DATE(created_at) BETWEEN ? AND ?");
$stmt2->execute([$from,$to]);
$expenses = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports - Pharmacy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>
<div class="content-wrapper">
   <div class="content-inner">
<div class="container mt-3">
<h3>Reports</h3>
<form class="row g-2 mb-3">
<div class="col"><input type="date" class="form-control" name="from" value="<?= $from ?>"></div>
<div class="col"><input type="date" class="form-control" name="to" value="<?= $to ?>"></div>
<div class="col"><button class="btn btn-primary">Filter</button></div>
</form>

<div class="row">
<div class="col-md-6"><h5>Sales</h5>
<table class="table table-bordered">
<thead><tr><th>Invoice</th><th>Total</th><th>Paid</th><th>Due</th></tr></thead>
<tbody>
<?php foreach($sales as $s): ?>
<tr><td><?= $s['invoice_id'] ?></td><td>$<?= $s['total'] ?></td><td>$<?= $s['paid'] ?></td><td>$<?= $s['due'] ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="col-md-6"><h5>Expenses</h5>
<table class="table table-bordered">
<thead><tr><th>Title</th><th>Amount</th></tr></thead>
<tbody>
<?php foreach($expenses as $e): ?>
<tr><td><?= $e['title'] ?></td><td>$<?= $e['amount'] ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<div class="mt-4">
<canvas id="reportChart"></canvas>
</div>
</div>

<script>
const ctx = document.getElementById('reportChart').getContext('2d');
const reportChart = new Chart(ctx,{
    type:'bar',
    data:{
        labels:['Sales','Expenses'],
        datasets:[{label:'Amount $',data:[<?= array_sum(array_column($sales,'total')) ?>, <?= array_sum(array_column($expenses,'amount')) ?>],backgroundColor:['rgba(54,162,235,0.7)','rgba(255,99,132,0.7)']}]
    },
    options:{responsive:true}
});
</script>
</div> <!-- END .content-wrapper -->
</div>
</body>
</html>
