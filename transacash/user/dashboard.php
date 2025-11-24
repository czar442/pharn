<?php
include '../includes/auth_check.php';
include '../includes/db.php';

// Check user role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    die("Access denied. This page is for admins only.");
}

// Total users
$result = $conn->query("SELECT COUNT(*) as total_users FROM users");
$total_users = $result->fetch_assoc()['total_users'] ?? 0;

// Total transactions
$result = $conn->query("SELECT COUNT(*) as total_transactions FROM transactions");
$total_transactions = $result->fetch_assoc()['total_transactions'] ?? 0;

// Transactions per day (last 7 days)
$query = "
  SELECT DATE(created_at) as date, COUNT(*) as count 
  FROM transactions 
  WHERE created_at >= CURDATE() - INTERVAL 6 DAY
  GROUP BY DATE(created_at)
  ORDER BY DATE(created_at)";
$result = $conn->query($query);

$transaction_dates = [];
$transaction_counts = [];
while ($row = $result->fetch_assoc()) {
    $transaction_dates[] = $row['date'];
    $transaction_counts[] = (int)$row['count'];
}

// User roles distribution
$result = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$roles = [];
$role_counts = [];
while ($row = $result->fetch_assoc()) {
    $roles[] = $row['role'];
    $role_counts[] = (int)$row['count'];
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Dashboard - Transacash</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
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
    /* .card {
      background-color: rgba(255, 255, 255, 0.1);
      border: 1px solid #ccc;
      border-radius: 10px;
      transition: transform 0.3s;
    } */
    .card:hover {
      transform: translateY(-5px);
    }
    .card i {
      font-size: 2rem;
      color: #4b006e;
    }
    .chart-container {
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      padding: 20px;
    }
    .btn-danger {
      transition: background-color 0.3s;
    }
    .btn-danger:hover {
      background-color: #c82333;
    }
  </style>
<body>
  <!-- Top Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Transacash</a>
      <div class="d-flex">
        <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
      </div>
    </div>
  </nav>

  <div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-grow-1 p-4">
      <h3>Welcome to the Users Dashboard</h3>

      <div class="row">
        <div class="col-md-6 mb-4">
          <div class="card p-3 shadow">
            <h5>Total Users</h5>
            <h2><?= $total_users ?></h2>
          </div>
        </div>
        <div class="col-md-6 mb-4">
          <div class="card p-3 shadow">
            <h5>Total Transactions</h5>
            <h2><?= $total_transactions ?></h2>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-4">
          <div class="card p-3 shadow">
            <h5>Transactions (Last 7 Days)</h5>
            <canvas id="transactionsLineChart"></canvas>
          </div>
        </div>

        <div class="col-md-6 mb-4">
          <div class="card p-3 shadow">
            <h5>User Roles Distribution</h5>
            <canvas id="userRolesPieChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
  const transactionDates = <?= json_encode($transaction_dates) ?>;
  const transactionCounts = <?= json_encode($transaction_counts) ?>;

  const ctxLine = document.getElementById('transactionsLineChart').getContext('2d');
  new Chart(ctxLine, {
    type: 'line',
    data: {
      labels: transactionDates,
      datasets: [{
        label: 'Transactions',
        data: transactionCounts,
        borderColor: 'rgba(54, 162, 235, 1)',
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        fill: true,
        tension: 0.3,
        pointRadius: 5,
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  });

  const userRoles = <?= json_encode($roles) ?>;
  const userRoleCounts = <?= json_encode($role_counts) ?>;

  const ctxPie = document.getElementById('userRolesPieChart').getContext('2d');
  new Chart(ctxPie, {
    type: 'pie',
    data: {
      labels: userRoles,
      datasets: [{
        data: userRoleCounts,
        backgroundColor: ['#007bff', '#28a745', '#dc3545', '#ffc107']
      }]
    },
    options: { responsive: true }
  });
</script>

</body>
</html>
