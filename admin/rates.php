<?php include '../includes/auth_check.php'; ?>
<?php include '../includes/db.php';

$query = "SELECT usdt_to_ugx, usd_to_usdt FROM settings WHERE id = 1";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$settings = $result->fetch_assoc();

// Define rates for JavaScript
$rates = [
    'UGX_USD' => 1 / $settings['usdt_to_ugx'], // Inverse for UGX to USD
    'UGX_USDT' => 1 / $settings['usdt_to_ugx'], // Inverse for UGX to USDT
    'USD_UGX' => $settings['usdt_to_ugx'],
    'USDT_UGX' => $settings['usdt_to_ugx'],
    'USD_USDT' => $settings['usd_to_usdt'],
    'USDT_USD' => 1 / $settings['usd_to_usdt'] // Inverse for USDT to USD
];

?>

<!DOCTYPE html>
<html>
<head>
  <title>New Transaction - Transacash</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #4b006e, #1e0050);
      color: white;
    }
    .form-section {
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      padding: 30px;
    }
    .info-box {
      background-color: rgba(255, 255, 255, 0.1);
      padding: 20px;
      border-radius: 10px;
      margin-top: 20px;
    }
    label, select, input, .form-control {
      color: white;
    }
    select, input {
      background-color: rgba(255, 255, 255, 0.1);
      border: 1px solid #ccc;
    }
    .form-control:focus {
      background-color: rgba(255, 255, 255, 0.2);
      color: white;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
    <a class="navbar-brand" href="#">Transacash</a>
    <div class="ms-auto">
      <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </nav>

  <div class="d-flex">
    
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>


<div class="col-md-12">
          <div class="info-box">
            <h5>Exchange Details</h5>
            <div class="table-responsive">
              <table class="table table-bordered table-sm">
                <thead class="table-dark">
                  <tr>
                    <th>Currency Pair</th>
                    <th>Rate</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>USDT to UGX</td>
                    <td><?= number_format($settings['usdt_to_ugx'], 2) ?></td>
                  </tr>
                  <tr>
                    <td>USD to USDT</td>
                    <td><?= number_format($settings['usd_to_usdt'], 2) ?></td>
                  </tr>
                </tbody>
              </table>
            </div>