<?php
if(session_status() === PHP_SESSION_NONE) session_start();
?>

<style>
/* Sidebar styling */
.sidebar {
    width: 220px;
    min-height: 100vh;
    background-color: #0d6efd;
    color: white;
    padding-top: 1rem;
    position: fixed;
    top: 65px; /* height of fixed navbar */
    left: 0;
    overflow-y: auto;
}

.sidebar a {
    color: white;
    text-decoration: none;
    display: block;
    padding: 0.75rem 1rem;
    font-size: 15px;
}

.sidebar a:hover {
    background-color: rgba(255,255,255,0.15);
    padding-left: 1.2rem;
    transition: 0.2s;
}

.sidebar .logout-btn {
    margin-top: 20px;
    width: 85%;
}

/* Main Content Wrapper */
/* MAIN CONTENT WRAPPER */
.content-wrapper {
    margin-left: 260px;       /* Leaves space for sidebar on large screens */
    padding: 30px;
    min-height: 100vh;
    max-width: 1100px; 
    background: #f2f3f5;      /* Soft grey background */
    display: flex;
    justify-content: center;  /* Centers content horizontally */
}

.content-inner {
    width: 1500px;
    max-width: 1500px;        /* Adjust this to control width */
    background: #ffffff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
}

/* On small screens: collapse sidebar */
@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
        padding: 15px;
    }
}



/* Responsive */
@media (max-width: 991px) {
  .sidebar {
    transform: translateX(-100%);
    transition: all 0.3s;
  }

  .sidebar.show {
    transform: translateX(0);
  }

  .content-wrapper {
    margin-left: 0 !important;
  }

  #menuToggleBtn {
    display: inline-block !important;
  }
}

.content-wrapper {
    margin-left: 220px;
    padding: 20px;
    transition: margin-left .3s;
}
</style>

<!-- Sidebar -->
<div id="sidebarMenu" class="sidebar">
    <h5 class="text-center fw-bold pb-3 border-bottom">Menu</h5>

    <a href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <a href="medicines.php"><i class="bi bi-capsule-pill me-2"></i> Medicines</a>
    <a href="suppliers.php"><i class="bi bi-truck me-2"></i> Suppliers</a>
    <a href="customers.php"><i class="bi bi-people me-2"></i> Customers</a>
    <a href="pos.php"><i class="bi bi-cart-check me-2"></i> POS</a>
    <a href="sales.php"><i class="bi bi-receipt me-2"></i> Sales</a>
    <a href="expenses.php"><i class="bi bi-cash-coin me-2"></i> Expenses</a>
    <a href="reports.php"><i class="bi bi-graph-up-arrow me-2"></i> Reports</a>
    <a href="alerts.php"><i class="bi bi-bell me-2"></i> Alerts</a>

    <?php if($_SESSION['role']=='admin'): ?>
    <a href="settings.php"><i class="bi bi-gear-fill me-2"></i> Settings</a>
    <?php endif; ?>

    <a href="logout.php" class="btn btn-outline-light logout-btn d-block mx-auto">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<!-- Sidebar Toggle Button for Mobile -->
<button id="menuToggleBtn" class="btn btn-primary d-lg-none position-fixed mt-2 ms-2" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>

<script>
function toggleSidebar() {
    document.getElementById('sidebarMenu').classList.toggle('show');
}
</script>
