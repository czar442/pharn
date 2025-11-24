<?php include '../includes/auth_check.php'; ?>
<?php include '../includes/db.php';

// Fetch current USD/UGX rate from API
$api_url = "https://open.er-api.com/v6/latest/USD";
$response = file_get_contents($api_url);
if ($response === FALSE) {
    die("Error fetching live exchange rate.");
}
$data = json_decode($response, true);
$usd_to_ugx_live = $data['rates']['UGX'] ?? 3600; // fallback if API fails

// Fetch local settings for USD→USDT rate
$query = "SELECT usd_to_usdt FROM settings WHERE id = 1";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$settings = $result->fetch_assoc();
$usd_to_usdt = $settings['usd_to_usdt'] * 1;

// Prepare rates for JS
$rates = [
    'USD_UGX' => $usd_to_ugx_live,
    'UGX_USD' => 1 / $usd_to_ugx_live,
    'USD_USDT' => $usd_to_usdt,
    'USDT_USD' => 1 / $usd_to_usdt,
    'USDT_UGX' => $usd_to_ugx_live,
    'UGX_USDT' => 1 / $usd_to_ugx_live
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body { background: linear-gradient(135deg, #4b006e, #1e0050); color: white; min-height: 100vh; }
    .form-section { background-color: rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 30px; }
    .info-box { background-color: rgba(255, 255, 255, 0.1); padding: 20px; border-radius: 10px; margin-top: 20px; }
    label, select, input, .form-control { color: none; }
    select, input { background-color: rgba(255, 255, 255, 0.1); border: 1px solid #ccc; }
    .form-control:focus { background-color: rgba(255, 255, 255, 0.2); color: white; }
    table { color: white; }
    .table th, .table td { color: white; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand" href="#"><i class="bi bi-wallet2 me-2"></i>Transacash</a>
  <div class="ms-auto">
    <a href="../logout.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
  </div>
</nav>

<div class="d-flex">
  <div class="col-md-3 col-lg-2">
    <?php include '../includes/sidebar.php'; ?>
  </div>

  <div class="col-md-9 col-lg-10 p-4">
    <div class="form-section">
      <h4><i class="bi bi-person-lines-fill me-2"></i>Customer Transaction</h4>

      <form method="post" action="transaction_save.php" class="row g-3" id="transactionForm">

        <!-- Select Customer -->
        <div class="col-md-6">
          <label for="customer_id">Search Customer</label>
          <select class="form-select selectpicker" name="customer_id" id="customer_id" data-live-search="true" required>
            <option value="">Choose customer</option>
            <?php
              $customers = $conn->query("SELECT id, full_name AS name, phone_number AS phone FROM customers ORDER BY name ASC");
              while($row = $customers->fetch_assoc()):
            ?>
              <option value="<?= $row['id'] ?>"><?= $row['name'] ?> - <?= $row['phone'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <!-- From / To / Amount -->
        <div class="col-md-3">
          <label>From Currency</label>
          <select class="form-select" name="from_currency" id="from_currency">
            <option value="UGX">UGX</option>
            <option value="USD">USD</option>
            <option value="USDT">USDT</option>
          </select>
        </div>

        <div class="col-md-3">
          <label>Amount</label>
          <input type="number" name="from_amount" class="form-control" id="from_amount" required>
        </div>

        <div class="col-md-3">
          <label>To Currency</label>
          <select class="form-select" name="to_currency" id="to_currency">
            <option value="USD">USD</option>
            <option value="UGX">UGX</option>
            <option value="USDT">USDT</option>
          </select>
        </div>

        <div class="col-md-3">
          <label>Customer Receives</label>
          <input type="text" name="customer_receives" class="form-control" id="customer_receives" readonly>
        </div>

        <!-- Info Box -->
        <div class="col-md-12">
          <div class="info-box">
            <p><strong>Exchange Rate:</strong> <span id="rate_display">Loading...</span></p>
            <p><strong>Fee:</strong> <span id="fee_display">0</span></p>
            <p><strong>Net Received:</strong> <span id="net_display">0</span></p>
          </div>
        </div>

        <!-- Exchange Table -->
        <div class="col-md-12">
          <div class="info-box">
            <h5>Exchange Details</h5>
            <div class="table-responsive">
              <table class="table table-bordered table-sm">
                <thead class="table-dark">
                  <tr><th>Currency Pair</th><th>Rate</th></tr>
                </thead>
                <tbody>
                  <tr><td>USD to UGX (Live)</td><td><?= number_format($usd_to_ugx_live, 2) ?></td></tr>
                  <tr><td>USD to USDT</td><td><?= number_format($usd_to_usdt, 6) ?></td></tr>
                  <tr><td>UGX to USDT</td><td><?= number_format(1 / $usd_to_ugx_live, 6) ?></td></tr>
                  <tr><td>USDT to USD</td><td><?= number_format(1 / $usd_to_usdt, 6) ?></td></tr>
                  <tr><td>USDT to UGX</td><td><?= number_format($usd_to_ugx_live, 2) ?></td></tr>
                </tbody>
              </table>
            </div>
            <p class="text-center mt-2"><small>Rates by <a href="https://www.exchangerate-api.com" style="color: #ccc;">Exchange Rate API</a></small></p>
          </div>
        </div>

        <div class="col-md-12 text-end">
          <button type="submit" class="btn btn-success">Save Transaction</button>
        </div>

      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

<script>
const rates = <?= json_encode($rates); ?>;

function calculate() {
    const from = document.getElementById("from_currency").value;
    const to = document.getElementById("to_currency").value;
    const amount = parseFloat(document.getElementById("from_amount").value) || 0;

    const rateKey = `${from}_${to}`;
    const rate = rates[rateKey] || 1;
    const converted = amount * rate;

    let feePercent = 0;

    // Fee rules
    if (from === "USD" && to === "USDT") {
        feePercent = 5.5;
    }
    if (from === "UGX" && to === "USDT") {
        feePercent = 5.5;
    }
    if (from === "USDT" && to === "USD") {
        feePercent = 3.5;
    }
    if (from === "USDT" && to === "UGX") {
        feePercent = 3.5;
    }

    const fee = (converted * feePercent) / 100;
    const net = converted - fee;

    document.getElementById("rate_display").textContent = `1 ${from} = ${rate.toFixed(6)} ${to}`;
    document.getElementById("fee_display").textContent = `${fee.toFixed(2)} ${to} (${feePercent}%)`;
    document.getElementById("net_display").textContent = `${net.toFixed(2)} ${to}`;
    document.getElementById("customer_receives").value = net.toFixed(2);
}

document.addEventListener("DOMContentLoaded", function() {
    $('.selectpicker').selectpicker();
    calculate();
});

document.getElementById("from_currency").addEventListener("change", calculate);
document.getElementById("to_currency").addEventListener("change", calculate);
document.getElementById("from_amount").addEventListener("input", calculate);
</script>
</body>
</html>