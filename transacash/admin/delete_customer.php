<?php
include 'auth_check.php';
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Optionally delete the image file
    $res = $conn->query("SELECT id_image FROM customers WHERE id = $id");
    if ($res && $row = $res->fetch_assoc()) {
        if (file_exists($row['id_image'])) {
            unlink($row['id_image']); // delete the file
        }
    }

    $conn->query("DELETE FROM customers WHERE id = $id");
}

header("Location: view_customers.php");
exit();
?>
