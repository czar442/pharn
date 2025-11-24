<?php
session_start();
require 'db.php';
if(!isset($_SESSION['user_id'])) header("Location: index.php");

$low_stock = $pdo->query("SELECT * FROM medicines WHERE quantity<10")->fetchAll(PDO::FETCH_ASSOC);
$expiring = $pdo->query("SELECT * FROM medicines WHERE expiry_date<=DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Alerts - Pharmacy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<<style>
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
<h3>Alerts</h3>
<h5>Low Stock</h5>
<ul class="list-group mb-3">
<?php foreach($low_stock as $m): ?>
<li class="list-group-item d-flex justify-content-between align-items-center">
<?= $m['name'] ?> (Qty: <?= $m['quantity'] ?>)
<span class="badge bg-danger">Low Stock</span>
</li>
<?php endforeach; ?>
</ul>

<h5>Expiring Soon (30 days)</h5>
<ul class="list-group">
<?php foreach($expiring as $m): ?>
<li class="list-group-item d-flex justify-content-between align-items-center">
<?= $m['name'] ?> (Expiry: <?= $m['expiry_date'] ?>)
<span class="badge bg-warning">Expiring</span>
</li>
<?php endforeach; ?>
</ul>
</div>
</div>
</body>
</html>
