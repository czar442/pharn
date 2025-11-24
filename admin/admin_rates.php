<?php
include '../includes/auth_check.php';
include '../includes/db.php';

// Fetch current settings
$query = "SELECT usdt_to_ugx, usd_to_usdt FROM settings WHERE id = 1";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$settings = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usdt_to_ugx = floatval($_POST['usdt_to_ugx']);
    $usd_to_usdt = floatval($_POST['usd_to_usdt']);

    $updateQuery = "UPDATE settings SET usdt_to_ugx = ?, usd_to_usdt = ? WHERE id = 1";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("dd", $usdt_to_ugx, $usd_to_usdt);

    if ($stmt->execute()) {
        $success = "✅ Exchange rates updated successfully!";
        $settings['usdt_to_ugx'] = $usdt_to_ugx;
        $settings['usd_to_usdt'] = $usd_to_usdt;
    } else {
        $error = "❌ Failed to update rates: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Exchange Rate Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #25034b, #420074);
      color: white;
      min-height: 100vh;
    }
    .card {
      background-color: rgba(255, 255, 255, 0.08);
      border: none;
      border-radius: 15px;
      box-shadow: 0 0 10px rgba(255,255,255,0.2);
    }
    .form-control {
      background-color: rgba(255,255,255,0.15);
      border: 1px solid #ccc;
      color: black;
    }
    .form-control:focus {
      background-color: rgba(255,255,255,0.25);
      color: black;
    }
    table {
      color: white;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand" href="#">Transacash Admin</a>
  <div class="ms-auto">
    <a href="transaction.php" class="btn btn-outline-light btn-sm me-2">← Back to Transactions</a>
    <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
</nav>

<div class="container py-5">
  <div class="card p-4 mx-auto" style="max-width: 700px;">
    <h3 class="text-center mb-4">💱 Manage Exchange Rates</h3>

    <?php if (isset($success)): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (isset($error)): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <table class="table table-bordered table-sm text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>Currency Pair</th>
            <th>Current Rate</th>
            <th>New Rate</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>1 USD → UGX</strong></td>
            <td><?= number_format($settings['usdt_to_ugx'], 0) ?> UGX</td>
            <td>
              <input type="number" step="0.01" name="usdt_to_ugx" 
                     value="<?= htmlspecialchars($settings['usdt_to_ugx']) ?>" 
                     class="form-control text-center" required>
            </td>
          </tr>
          <tr>
            <td><strong>1 USD → USDT</strong></td>
            <td><?= number_format($settings['usd_to_usdt'], 4) ?> USDT</td>
            <td>
              <input type="number" step="0.0001" name="usd_to_usdt" 
                     value="<?= htmlspecialchars($settings['usd_to_usdt']) ?>" 
                     class="form-control text-center" required>
            </td>
          </tr>
          <tr>
            <td><strong>1 USDT → UGX</strong></td>
            <td><?= number_format($settings['usdt_to_ugx'], 0) ?> UGX</td>
            <td>— (Same as USD→UGX)</td>
          </tr>
        </tbody>
      </table>

      <div class="text-end mt-3">
        <button type="submit" class="btn btn-primary px-4">💾 Save Changes</button>
      </div>
    </form>

    <hr class="mt-4">
    <h5 class="text-center">📊 Current Rates Summary</h5>
    <ul class="list-unstyled text-center">
      <li><strong>1 USD =</strong> <?= number_format($settings['usdt_to_ugx'], 0) ?> UGX</li>
      <li><strong>1 USDT =</strong> <?= number_format($settings['usdt_to_ugx'], 0) ?> UGX</li>
      <li><strong>1 USD =</strong> <?= number_format($settings['usd_to_usdt'], 4) ?> USDT</li>
    </ul>
  </div>
</div>

</body>
</html>
