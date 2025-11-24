<?php
session_start();
require 'db.php';

if($_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit;
}

if(isset($_POST['name'], $_POST['category'], $_POST['price'], $_POST['quantity'], $_POST['expiry_date'])){
    $stmt = $pdo->prepare("INSERT INTO medicines (name, category, price, quantity, expiry_date, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$_POST['name'], $_POST['category'], $_POST['price'], $_POST['quantity'], $_POST['expiry_date']]);
    $success = "Medicine successfully added!";
}
?>

<!doctype html>
<html lang="en">
<head>
<title>Add Medicine</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'sidebar.php'; ?>

<div class="content">
    <h3 class="mb-4">Add New Medicine / Stock</h3>

    <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

    <div class="card p-3 shadow">
        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label>Category</label>
                    <input type="text" name="category" class="form-control" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Quantity</label>
                    <input type="number" name="quantity" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control" required>
                </div>
            </div>

            <button class="btn btn-primary w-100">Save Medicine</button>
        </form>
    </div>
</div>
</body>
</html>
a