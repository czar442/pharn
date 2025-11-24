<?php
if(session_status() === PHP_SESSION_NONE) session_start();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm fixed-top">
  <div class="container-fluid">

    <!-- Brand -->
    <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
      <i class="bi bi-capsule me-2"></i> Pharmacy System
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topbarMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu -->
    <div class="collapse navbar-collapse justify-content-end" id="topbarMenu">
      <ul class="navbar-nav align-items-center">

        <!-- User Info -->
        <li class="nav-item me-3">
          <span class="text-white fw-semibold">
            <?= $_SESSION['name']; ?>
            <span class="badge bg-warning text-dark ms-2">
              <?= ucfirst($_SESSION['role']); ?>
            </span>
          </span>
        </li>

        <!-- Logout Button -->
        <li class="nav-item">
          <a href="logout.php" class="btn btn-light btn-sm">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
        </li>

      </ul>
    </div>
  </div>
</nav>

<!-- Spacer Fix Because Navbar is Fixed -->
<style>
body {
    padding-top: 65px;
}
</style>
