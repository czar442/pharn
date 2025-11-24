<?php
session_start();
require 'db.php';
if(!isset($_SESSION['user_id'])) header("Location: index.php");

$id = $_GET['id'] ?? null;
if(!$id){
    die("Sale ID not specified.");
}

// Fetch sale details
$stmt = $pdo->prepare("
    SELECT s.*, c.name as customer, c.email, c.phone 
    FROM sales s 
    LEFT JOIN customers c ON s.customer_id = c.id 
    WHERE s.id = ?
");
$stmt->execute([$id]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$sale) die("Sale not found.");

// Fetch sale items
$itemStmt = $pdo->prepare("
    SELECT si.*, m.name as medicine_name 
    FROM sale_items si 
    LEFT JOIN medicines m ON si.medicine_id = m.id 
    WHERE si.sale_id = ?
");
$itemStmt->execute([$id]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>View Sale - Invoice <?= htmlspecialchars($sale['invoice_id']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'topbar.php'; ?>
<div class="d-flex">
  <!-- Sidebar -->
<?php include 'sidebar.php'; ?>
    <div class="content-wrapper">
 <div class="content-inner">
<div class="container mt-4">
    <h3>Sale Details - Invoice #<?= htmlspecialchars($sale['invoice_id']) ?></h3>
    <div class="mb-3">
        <strong>Customer:</strong> <?= htmlspecialchars($sale['customer']) ?><br>
        <strong>Email:</strong> <?= htmlspecialchars($sale['email']) ?><br>
        <strong>Phone:</strong> <?= htmlspecialchars($sale['phone']) ?><br>
        <strong>Date:</strong> <?= htmlspecialchars($sale['created_at']) ?><br>
        <strong>Total:</strong> $<?= number_format($sale['total'], 2) ?><br>
        <strong>Paid:</strong> $<?= number_format($sale['paid'], 2) ?><br>
        <strong>Due:</strong> $<?= number_format($sale['due'], 2) ?><br>
    </div>

    <h5>Items</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Medicine</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['medicine_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>$<?= number_format($item['price'], 2) ?></td>
                <td>$<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="sales.php" class="btn btn-secondary mt-3">Back to Sales</a>
</div>
</div>
</div>
</body>
</html>
