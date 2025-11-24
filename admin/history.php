<?php
include '../includes/auth_check.php';
include '../includes/db.php';

// Fetch exchange rates
$stmt = $conn->prepare("SELECT usdt_to_ugx, usd_to_usdt FROM settings WHERE id = 1");
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();

if (!$settings) {
    die("Exchange rates not found in settings.");
}

$usdt_to_ugx = (float)$settings['usdt_to_ugx'];
$usd_to_usdt = (float)$settings['usd_to_usdt'];

$rates = [
    'UGX_USD' => 1 / $usdt_to_ugx,
    'UGX_USDT' => 1 / $usdt_to_ugx,
    'USD_UGX' => $usdt_to_ugx,
    'USDT_UGX' => $usdt_to_ugx,
    'USD_USDT' => $usd_to_usdt,
    'USDT_USD' => 1 / $usd_to_usdt
];

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$sql = "SELECT t.*, c.full_name AS customer_name, c.phone_number 
        FROM transactions t 
        JOIN customers c ON t.customer_id = c.id";

if ($search !== '') {
    $sql .= " WHERE c.full_name LIKE ? OR c.phone_number LIKE ?";
    $stmt = $conn->prepare($sql);
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Transaction History - Transacash</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body {
      background: linear-gradient(135deg, #4b006e, #1e0050);
      color: white;
      min-height: 100vh;
    }
    #sidebar {
      min-height: 100vh;
      background: linear-gradient(135deg, #4b006e, #1e0050);
    }
    .table {
      background-color: rgba(255, 255, 255, 0.1);
    }
    .form-control {
      background-color: rgba(255, 255, 255, 0.1);
      color: white;
      border: 1px solid #ccc;
    }
    .form-control:focus {
      background-color: rgba(255, 255, 255, 0.2);
      color: white;
      border-color: #4b006e;
      box-shadow: 0 0 5px rgba(75, 0, 110, 0.5);
    }
  </style>
</head>
<body>

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
    <h4><i class="bi bi-clock-history me-2"></i>Transaction History</h4>

    <form method="get" class="row mb-3">
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text bg-dark border-dark text-white"><i class="bi bi-search"></i></span>
          <input type="text" name="q" class="form-control" placeholder="Search by name or phone" value="<?= htmlspecialchars($search) ?>">
        </div>
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary"><i class="bi bi-search me-2"></i>Search</button>
      </div>
    </form>

    <div class="d-flex justify-content-end mb-3">
      <button id="print-selected" class="btn btn-success btn-sm">
        <i class="bi bi-printer-fill me-2"></i>Print Selected
      </button>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th><input type="checkbox" id="select-all"></th>
            <th>#</th>
            <th>Customer</th>
            <th>From</th>
            <th>To</th>
            <th>Amount</th>
            <th>Rate</th>
            <th>Fee</th>
            <th>Net</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $total_fees = 0; 
          if ($result->num_rows === 0): ?>
            <tr><td colspan="11" class="text-center">No transactions found.</td></tr>
          <?php else: 
            $i = 1; 
            while ($row = $result->fetch_assoc()): 
              $rate_key = $row['from_currency'] . '_' . $row['to_currency'];
              $exchange_rate = isset($rates[$rate_key]) ? $rates[$rate_key] : 1;

              $fee_percent = 0;
              if ($row['from_currency'] === 'UGX' && ($row['to_currency'] === 'USD' || $row['to_currency'] === 'USDT')) {
                  $fee_percent = 5.5;
              } elseif (($row['from_currency'] === 'USD' || $row['from_currency'] === 'USDT') && $row['to_currency'] === 'UGX') {
                  $fee_percent = 3.5;
              }
              $converted = $row['from_amount'] * $exchange_rate;
              $fee = ($converted * $fee_percent) / 100;
              $net_amount = $converted - $fee;

              $total_fees += $fee;
          ?>
          <tr>
            <td>
              <input type="checkbox" class="select-transaction" value="<?= $row['id'] ?>"
                data-transaction='<?= json_encode([
                    "id" => $row["id"],
                    "customer_name" => htmlspecialchars($row["customer_name"]),
                    "phone_number" => htmlspecialchars($row["phone_number"]),
                    "from_amount" => number_format($row["from_amount"], 2),
                    "from_currency" => htmlspecialchars($row["from_currency"]),
                    "to_currency" => htmlspecialchars($row["to_currency"]),
                    "exchange_rate" => number_format($exchange_rate, 6),
                    "fee" => number_format($fee, 2),
                    "net_amount" => number_format($net_amount, 2),
                    "date" => date("d M Y H:i", strtotime($row["created_at"]))
                ]) ?>'>
            </td>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?><br><small><?= htmlspecialchars($row['phone_number']) ?></small></td>
            <td><?= number_format($row['from_amount'], 2) . ' ' . htmlspecialchars($row['from_currency']) ?></td>
            <td><?= htmlspecialchars($row['to_currency']) ?></td>
            <td><?= number_format($row['from_amount'], 2) ?></td>
            <td><?= number_format($exchange_rate, 6) ?></td>
            <td><?= number_format($fee, 2) . ' ' . htmlspecialchars($row['to_currency']) ?></td>
            <td><?= number_format($net_amount, 2) . ' ' . htmlspecialchars($row['to_currency']) ?></td>
            <td><?= date("d M Y H:i", strtotime($row['created_at'])) ?></td>
            <td>
              <button class="btn btn-sm btn-info single-print" data-transaction='<?= json_encode([
                "id" => $row["id"],
                "customer_name" => htmlspecialchars($row["customer_name"]),
                "phone_number" => htmlspecialchars($row["phone_number"]),
                "from_amount" => number_format($row["from_amount"], 2),
                "from_currency" => htmlspecialchars($row["from_currency"]),
                "to_currency" => htmlspecialchars($row["to_currency"]),
                "exchange_rate" => number_format($exchange_rate, 6),
                "fee" => number_format($fee, 2),
                "net_amount" => number_format($net_amount, 2),
                "date" => date("d M Y H:i", strtotime($row["created_at"]))
              ]) ?>'>
                <i class="bi bi-printer"></i> Print
              </button>
            </td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>

        <!-- ✅ Added total fees row -->
        <?php if ($total_fees > 0): ?>
        <tfoot class="table-dark">
          <tr>
            <th colspan="7" class="text-end">Total Fees:</th>
            <th colspan="4"><?= number_format($total_fees, 2) ?></th>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const { jsPDF } = window.jspdf;

  document.getElementById('select-all')?.addEventListener('change', function() {
    document.querySelectorAll('.select-transaction').forEach(cb => cb.checked = this.checked);
  });

  document.getElementById('print-selected').addEventListener('click', function() {
    const selected = document.querySelectorAll('.select-transaction:checked');
    if (selected.length === 0) {
      alert('Please select at least one transaction to print.');
      return;
    }

    const doc = new jsPDF();
    let y = 20;
    doc.setFontSize(14);
    doc.text("Transacash Transaction Receipts", 60, y);
    y += 10;
    doc.setFontSize(10);

    selected.forEach((cb, i) => {
      const tx = JSON.parse(cb.getAttribute('data-transaction'));
      doc.text(`Transaction #${tx.id}`, 20, y); y += 6;
      doc.text(`Customer: ${tx.customer_name} (${tx.phone_number})`, 20, y); y += 6;
      doc.text(`From: ${tx.from_amount} ${tx.from_currency} → ${tx.to_currency}`, 20, y); y += 6;
      doc.text(`Rate: ${tx.exchange_rate}`, 20, y); y += 6;
      doc.text(`Fee: ${tx.fee} ${tx.to_currency}`, 20, y); y += 6;
      doc.text(`Net: ${tx.net_amount} ${tx.to_currency}`, 20, y); y += 6;
      doc.text(`Date: ${tx.date}`, 20, y); y += 10;
      doc.line(20, y, 190, y); y += 10;

      if (y > 270 && i < selected.length - 1) {
        doc.addPage(); y = 20;
      }
    });

    doc.save(`transactions_${new Date().toISOString().split('T')[0]}.pdf`);
  });

  document.querySelectorAll('.single-print').forEach(btn => {
    btn.addEventListener('click', function() {
      const tx = JSON.parse(this.getAttribute('data-transaction'));
      const doc = new jsPDF();
      doc.setFontSize(14);
      doc.text("Transacash Transaction Receipt", 55, 20);
      doc.setFontSize(10);
      let y = 40;
      const lines = [
        `Transaction #${tx.id}`,
        `Customer: ${tx.customer_name} (${tx.phone_number})`,
        `From: ${tx.from_amount} ${tx.from_currency} → ${tx.to_currency}`,
        `Rate: ${tx.exchange_rate}`,
        `Fee: ${tx.fee} ${tx.to_currency}`,
        `Net Received: ${tx.net_amount} ${tx.to_currency}`,
        `Date: ${tx.date}`
      ];
      lines.forEach(line => { doc.text(line, 20, y); y += 10; });
      doc.save(`transaction_${tx.id}.pdf`);
    });
  });
});
</script>
</body>
</html>
