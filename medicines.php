<?php
session_start();
require 'db.php';
if(!isset($_SESSION['user_id'])) header("Location: index.php");

// Fetch medicines
$medicines = $pdo->query("SELECT * FROM medicines")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Medicines - Pharmacy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
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
<h3>Medicines</h3>
<button class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#addMedicineModal">Add Medicine</button>
<table class="table table-striped table-bordered" id="medicinesTable">
<thead><tr><th>Name</th><th>Brand</th><th>Category</th><th>Quantity</th><th>Price</th><th>Expiry</th><th>Stock</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($medicines as $m): ?>
<tr>
<td><?= $m['name'] ?></td>
<td><?= $m['brand'] ?></td>
<td><?= $m['category'] ?></td>
<td><?= $m['quantity'] ?></td>
<td>$<?= $m['price'] ?></td>
<td><?= $m['expiry_date'] ?></td>
<td>
<?php if($m['quantity']<10) echo '<span class="badge bg-danger">Low</span>';
else echo '<span class="badge bg-success">In Stock</span>'; ?>
</td>
<td>
<button class="btn btn-sm btn-primary">Edit</button>
<button class="btn btn-sm btn-danger">Delete</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- Add Medicine Modal -->
<div class="modal fade" id="addMedicineModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Medicine</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="medicines_add.php">
          <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name"></div>
          <div class="mb-3"><label class="form-label">Brand</label><input class="form-control" name="brand"></div>
          <div class="mb-3"><label class="form-label">Category</label><input class="form-control" name="category"></div>
          <div class="mb-3"><label class="form-label">Quantity</label><input type="number" class="form-control" name="quantity"></div>
          <div class="mb-3"><label class="form-label">Price</label><input type="number" class="form-control" name="price" step="0.01"></div>
          <div class="mb-3"><label class="form-label">Expiry Date</label><input type="date" class="form-control" name="expiry_date"></div>
          <div class="mb-3"><label class="form-label">Barcode</label><input class="form-control" name="barcode"></div>
          <button class="btn btn-primary w-100" type="submit">Save</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>$(document).ready(function(){$('#medicinesTable').DataTable();});</script>
</div> <!-- END .content-wrapper -->
</div>
</body>
</html>
