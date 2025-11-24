<?php
include '../includes/auth_check.php';
include '../includes/db.php';

// Only allow admin access
if ($_SESSION['role'] !== 'admin') {
  header('Location: dashboard.php');
  exit();
}

// Calculate total expenditure dynamically
$query = "
  SELECT SUM(
    CASE
      WHEN from_currency = 'UGX' AND to_currency IN ('USD', 'USDT') THEN (from_amount * 0.00028 * 5.5) / 100
      WHEN from_currency IN ('USD', 'USDT') AND to_currency = 'UGX' THEN (from_amount * 3600 * 3.5) / 100
      ELSE 0
    END
  ) AS total
  FROM transactions
";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$total_expenditure = $result->fetch_assoc()['total'] ?? 0;

// Get individual transactions
$transactions_query = "
  SELECT t.*, c.full_name AS customer_name, c.phone_number
  FROM transactions t
  JOIN customers c ON t.customer_id = c.id
  ORDER BY t.created_at DESC
";
$transactions_result = $conn->query($transactions_query);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Expenditure Report - Transacash</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #4b006e, #1e0050);
      color: white;
    }
    .table {
      background-color: rgba(255, 255, 255, 0.1);
    }
    .table-dark {
      background-color: rgba(0, 0, 0, 0.5);
    }
    /* .card {
      background-color: rgba(255, 255, 255, 0.1);
      border: 1px solid #ccc;
    } */
    .print-btn {
      cursor: pointer;
    }
    /* Print-specific styles */
    @media print {
      body * {
        visibility: hidden;
      }
      .print-section, .print-section * {
        visibility: visible;
      }
      .print-section {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        background: white;
        color: black;
        padding: 20px;
        font-family: Arial, sans-serif;
      }
      .print-section h4 {
        color: #4b006e;
        text-align: center;
      }
      .print-section .table {
        background: none;
        border: 1px solid #ccc;
      }
      .print-section .table th, .print-section .table td {
        color: black;
      }
    }
  </style>
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand" href="#">Transacash</a>
  <div class="ms-auto">
    <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
</nav>

<div class="d-flex">
  <!-- Sidebar -->
  <?php include '../includes/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="container-fluid p-4">
    <h4>Expenditure Report</h4>
    <div class="card p-4 mb-4">
      <h5>Total Expenditure</h5>
      <p class="display-6"><?= number_format($total_expenditure, 2) ?> UGX</p>
    </div>

    <h5>Transaction Details</h5>
    <div class="table-responsive">
      <table class="table table-bordered table-hover table-sm">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>From</th>
            <th>To</th>
            <th>Amount</th>
            <th>Expenditure (Fee)</th>
            <th>Net</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($transactions_result->num_rows > 0): ?>
            <?php $i = 1; while ($row = $transactions_result->fetch_assoc()): ?>
              <?php
                // Calculate expenditure (fee) dynamically
                $rates = [
                    'UGX_USD' => 0.00028,
                    'UGX_USDT' => 0.00028,
                    'USD_UGX' => 3600,
                    'USDT_UGX' => 3600,
                    'USD_USDT' => 1,
                    'USDT_USD' => 1
                ];
                $rate_key = $row['from_currency'] . '_' . $row['to_currency'];
                $exchange_rate = isset($rates[$rate_key]) ? $rates[$rate_key] : 1;
                $fee_percent = 0;
                if ($row['from_currency'] === 'UGX' && ($row['to_currency'] === 'USD' || $row['to_currency'] === 'USDT')) {
                    $fee_percent = 5.5;
                } elseif (($row['from_currency'] === 'USD' || $row['from_currency'] === 'USDT') && $row['to_currency'] === 'UGX') {
                    $fee_percent = 3.5;
                }
                $converted = $row['from_amount'] * $exchange_rate;
                $expenditure = ($converted * $fee_percent) / 100;
              ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?><br><small><?= htmlspecialchars($row['phone_number']) ?></small></td>
                <td><?= number_format($row['from_amount'], 2) . ' ' . htmlspecialchars($row['from_currency']) ?></td>
                <td><?= htmlspecialchars($row['to_currency']) ?></td>
                <td><?= number_format($row['from_amount'], 2) ?></td>
                <td><?= number_format($expenditure, 2) ?></td>
                <td><?= number_format($row['customer_receives'], 2) . ' ' . htmlspecialchars($row['to_currency']) ?></td>
                <td><?= date("d M Y H:i", strtotime($row['created_at'])) ?></td>
                <td>
                  <button class="btn btn-sm btn-info print-btn" 
                          data-transaction='<?= json_encode([
                              "id" => $i-1,
                              "customer_name" => htmlspecialchars($row['customer_name']),
                              "phone_number" => htmlspecialchars($row['phone_number']),
                              "from_amount" => number_format($row['from_amount'], 2),
                              "from_currency" => htmlspecialchars($row['from_currency']),
                              "to_currency" => htmlspecialchars($row['to_currency']),
                              "exchange_rate" => number_format($exchange_rate, 6),
                              "expenditure" => number_format($expenditure, 2),
                              "net_amount" => number_format($row['customer_receives'], 2),
                              "to_currency" => htmlspecialchars($row['to_currency']),
                              "date" => date("d M Y H:i", strtotime($row['created_at']))
                          ]) ?>'>
                    Print
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="9" class="text-center">No transactions found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Script loaded');
    const buttons = document.querySelectorAll('.print-btn');
    console.log('Found print buttons:', buttons.length);
    buttons.forEach((button, index) => {
      button.addEventListener('click', function() {
        console.log('Print button clicked:', index);
        try {
          const transaction = JSON.parse(this.getAttribute('data-transaction'));
          console.log('Transaction data:', transaction);
          
          const printWindow = window.open('', '_blank');
          if (!printWindow) {
            console.error('Failed to open print window');
            alert('Unable to open print window. Please allow pop-ups for this site.');
            return;
          }
          printWindow.document.write(`
            <html>
            <head>
              <title>Transaction Receipt - Transacash</title>
              <style>
                body {
                  font-family: Arial, sans-serif;
                  margin: 20px;
                  color: black;
                }
                .print-section {
                  max-width: 600px;
                  margin: auto;
                  padding: 20px;
                  border: 1px solid #ccc;
                  border-radius: 10px;
                }
                .print-section h4 {
                  color: #4b006e;
                  text-align: center;
                }
                .print-section table {
                  width: 100%;
                  border-collapse: collapse;
                  margin-top: 20px;
                }
                .print-section th, .print-section td {
                  padding: 8px;
                  border: 1px solid #ccc;
                  text-align: left;
                }
                .print-section th {
                  background-color: #f8f9fa;
                }
              </style>
            </head>
            <body>
              <div class="print-section">
                <h4>Transacash Transaction Receipt</h4>
                <table>
                  <tr>
                    <th>Transaction #</th>
                    <td>${transaction.id}</td>
                  </tr>
                  <tr>
                    <th>Customer</th>
                    <td>${transaction.customer_name} (${transaction.phone_number})</td>
                  </tr>
                  <tr>
                    <th>From</th>
                    <td>${transaction.from_amount} ${transaction.from_currency}</td>
                  </tr>
                  <tr>
                    <th>To</th>
                    <td>${transaction.to_currency}</td>
                  </tr>
                  <tr>
                    <th>Amount</th>
                    <td>${transaction.from_amount}</td>
                  </tr>
                  <tr>
                    <th>Exchange Rate</th>
                    <td>${transaction.exchange_rate}</td>
                  </tr>
                  <tr>
                    <th>Fee</th>
                    <td>${transaction.expenditure}</td>
                  </tr>
                  <tr>
                    <th>Net Received</th>
                    <td>${transaction.net_amount} ${transaction.to_currency}</td>
                  </tr>
                  <tr>
                    <th>Date</th>
                    <td>${transaction.date}</td>
                  </tr>
                </table>
              </div>
              <script>
                window.onload = function() {
                  window.print();
                  window.onafterprint = function() {
                    window.close();
                  };
                };
              </script>
            </body>
            </html>
          `);
          printWindow.document.close();
        } catch (error) {
          console.error('Error parsing transaction data:', error);
          alert('Failed to print transaction. Check console for details.');
        }
      });
    });
  });
</script>
</body>
</html>