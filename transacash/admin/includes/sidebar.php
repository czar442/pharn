<?php
// sidebar.php
// Get the current page to highlight the active link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<head>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<nav class="flex-column flex-shrink-0 p-3 bg-light" style="width: 250px; height: 100vh; background: linear-gradient(135deg, #4b006e, #1e0050);">
  <a href="<?= $_SESSION['role'] == 'admin' ? 'dashboard.php' : 'transaction.php' ?>" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
    <i class="bi bi-wallet2 me-2"></i>
    <span class="fs-4 fw-bold">Transacash</span>
  </a>
  <hr class="border-light">
  <ul class="nav nav-pills flex-column mb-auto">
    <?php if ($_SESSION['role'] == 'admin'): ?>
      <li class="nav-item">
        <a href="dashboard.php" class="nav-link text-white <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
          <i class="bi bi-house-door me-2"></i> Dashboard
        </a>
      </li>
    <?php endif; ?>
    <li class="nav-item">
      <a href="transaction.php" class="nav-link text-white <?= $current_page == 'transaction.php' ? 'active' : '' ?>">
        <i class="bi bi-currency-exchange me-2"></i> New Transaction
      </a>
    </li>
    <li class="nav-item">
      <a href="history.php" class="nav-link text-white <?= $current_page == 'history.php' ? 'active' : '' ?>">
        <i class="bi bi-clock-history me-2"></i> Transaction History
      </a>
    </li>
    <li class="nav-item">
      <a href="add_customer.php" class="nav-link text-white <?= $current_page == 'add_customer.php' ? 'active' : '' ?>">
        <i class="bi bi-person-plus me-2"></i> Add Customer
      </a>
    </li>
    <li class="nav-item">
      <a href="view_customers.php" class="nav-link text-white <?= $current_page == 'view_customers.php' ? 'active' : '' ?>">
        <i class="bi bi-person-lines-fill me-2"></i> View Customers
      </a>
    </li>
    <?php if ($_SESSION['role'] == 'admin'): ?>
      <li class="nav-item">
        <a href="expenditure.php" class="nav-link text-white <?= $current_page == 'expenditure.php' ? 'active' : '' ?>">
          <i class="bi bi-piggy-bank me-2"></i> Expenditure
        </a>
      </li>
      <li class="nav-item">
        <a href="users.php" class="nav-link text-white <?= $current_page == 'users.php' ? 'active' : '' ?>">
          <i class="bi bi-people me-2"></i> Manage Users
        </a>
      </li>
      <li class="nav-item">
        <a href="settings.php" class="nav-link text-white <?= $current_page == 'settings.php' ? 'active' : '' ?>">
          <i class="bi bi-gear me-2"></i> Exchange Settings
        </a>
      </li>
      <!-- <li class="nav-item">
        <a href="pending_transactions.php" class="nav-link text-white <?= $current_page == 'pending_transactions.php' ? 'active' : '' ?>">
          <i class="bi bi-hourglass-split me-2"></i> Approvals
        </a>
      </li> -->
      <li class="nav-item">
        <a href="exchange_rates.php" class="nav-link text-white <?= $current_page == 'exchange_rates.php' ? 'active' : '' ?>">
          <i class="bi bi-currency-dollar me-2"></i> Exchange Rates
        </a>
      </li>
    <?php endif; ?>
    <li class="nav-item">
      <a href="logout.php" class="nav-link text-white">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
      </a>
    </li>
  </ul>
</nav>

<style>
  .nav-link {
    transition: background-color 0.3s, color 0.3s;
  }
  .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.2);
    color: #fff !important;
  }
  .nav-link.active {
    background-color: #4b006e !important;
    color: #fff !important;
    font-weight: bold;
  }
  .nav-link i {
    vertical-align: middle;
  }
</style>