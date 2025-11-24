<?php
include '../includes/auth_check.php';
include '../includes/db.php';

// Fetch exchange rates from settings
$stmt = $conn->prepare("SELECT usdt_to_ugx, usd_to_usdt FROM settings WHERE id = 1");
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();

if (!$settings) {
    die("Exchange rates not found in settings.");
}

// Define exchange rates
$rates = [
    'UGX_USD' => 1 / $settings['usdt_to_ugx'],
    'UGX_USDT' => 1 / $settings['usdt_to_ugx'],
    'USD_UGX' => $settings['usdt_to_ugx'],
    'USDT_UGX' => $settings['usdt_to_ugx'],
    'USD_USDT' => $settings['usd_to_usdt'],
    'USDT_USD' => 1 / $settings['usd_to_usdt']
];

// Handle search
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
    .table-dark {
      background-color: rgba(0, 0, 0, 0.5);
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
    .print-btn {
      cursor: pointer;
      transition: background-color 0.3s;
    }
    .print-btn:hover {
      background-color: #0d6efd;
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
  <a class="navbar-brand" href="#"><i class="bi bi-wallet2 me-2"></i>Transacash</a>
  <div class="ms-auto">
    <a href="../logout.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
  </div>
</nav>

<div class="d-flex">
  <!-- Sidebar -->
  <div class="col-md-3 col-lg-2">
    <?php include '../includes/sidebar.php'; ?>
  </div>

  <!-- Main Content -->
  <div class="col-md-9 col-lg-10 p-4">
    <h4><i class="bi bi-clock-history me-2"></i>Transaction History</h4>
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Search Form -->
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

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
          <tr>
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
          <?php if ($result->num_rows === 0): ?>
            <tr>
              <td colspan="10" class="text-center">No transactions found.</td>
            </tr>
          <?php else: ?>
            <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
              <?php
                // Calculate exchange rate
                $rate_key = $row['from_currency'] . '_' . $row['to_currency'];
                $exchange_rate = isset($rates[$rate_key]) ? $rates[$rate_key] : 1;

                // Calculate fee
                $fee_percent = 0;
                if ($row['from_currency'] === 'UGX' && ($row['to_currency'] === 'USD' || $row['to_currency'] === 'USDT')) {
                    $fee_percent = 5.5;
                } elseif (($row['from_currency'] === 'USD' || $row['from_currency'] === 'USDT') && $row['to_currency'] === 'UGX') {
                    $fee_percent = 3.5;
                }
                $converted = $row['from_amount'] * $exchange_rate;
                $fee = ($converted * $fee_percent) / 100;

                // Net amount (customer_receives)
                $net_amount = $row['customer_receives'];
              ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?><br><small><?= htmlspecialchars($row['phone_number']) ?></small></td>
                <td><?= number_format($row['from_amount'], 2) . ' ' . htmlspecialchars($row['from_currency']) ?></td>
                <td><?= htmlspecialchars($row['to_currency']) ?></td>
                <td><?= number_format($row['from_amount'], 2) ?></td>
                <td><?= number_format($exchange_rate, 6) ?></td>
                <td><?= number_format($fee, 2) ?></td>
                <td><?= number_format($net_amount, 2) . ' ' . htmlspecialchars($row['to_currency']) ?></td>
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
                              "fee" => number_format($fee, 2),
                              "net_amount" => number_format($net_amount, 2),
                              "date" => date("d M Y H:i", strtotime($row['created_at']))
                          ]) ?>'>
                    <i class="bi bi-printer me-2"></i>Print
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  console.log('Script loaded'); // Debug: Confirm script runs
  document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded'); // Debug: Confirm DOM is ready
    const buttons = document.querySelectorAll('.print-btn');
    console.log('Found buttons:', buttons.length); // Debug: Count buttons
    buttons.forEach((button, index) => {
      button.addEventListener('click', function() {
        console.log('Button clicked:', index); // Debug: Confirm click
        try {
          const transaction = JSON.parse(this.getAttribute('data-transaction'));
          console.log('Transaction data:', transaction); // Debug: Log transaction data
          
          // Create print window
          const printWindow = window.open('', '_blank', 'width=800,height=600');
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
                    <td>${transaction.fee}</td>
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