<?php
session_start();
require 'db.php';

if (!isset($_POST['cart']) || !isset($_POST['total']) || !isset($_POST['paid'])) {
    echo "Missing data";
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = json_decode($_POST['cart'], true);
$total = $_POST['total'];
$paid = $_POST['paid'];
$due = $total - $paid;

// Insert sale
$stmt = $pdo->prepare("INSERT INTO sales (user_id, total, paid, due) VALUES (?,?,?,?)");
$stmt->execute([$user_id, $total, $paid, $due]);
$sale_id = $pdo->lastInsertId();

// Insert sale items
$itemStmt = $pdo->prepare("INSERT INTO sale_items (sale_id, medicine_id, quantity, price) VALUES (?,?,?,?)");

foreach ($cart as $item) {
    $itemStmt->execute([$sale_id, $item['id'], $item['qty'], $item['price']]);
}

echo "success";
?>
