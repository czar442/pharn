<?php
session_start();
require 'db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin') header("Location: index.php");

// Fetch users
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Settings - Pharmacy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
body { overflow-x: hidden; }
.sidebar { min-height: 100vh; background-color: #0d6efd; color: white; padding-top: 1rem; }
.sidebar a { color: white; text-decoration: none; display: block; padding: 0.75rem 1rem; }
.sidebar a:hover { background-color: rgba(255,255,255,0.1); }
.content { margin-left: 220px; padding: 20px; }
</style>
</head>
<body>
  <?php include 'topbar.php'; ?>

<div class="d-flex">
  <!-- Sidebar -->
 <?php include 'sidebar.php'; ?>
<div class="content-wrapper">
   <div class="content-inner">
<div class="container mt-3">
<h3>Users</h3>
<button class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
<table class="table table-bordered table-striped">
<thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Action</th></tr></thead>
<tbody>
<?php foreach($users as $u): ?>
<tr>
<td><?= $u['name'] ?></td>
<td><?= $u['email'] ?></td>
<td><?= ucfirst($u['role']) ?></td>
<td>
<button class="btn btn-sm btn-primary">Edit</button>
<button class="btn btn-sm btn-danger">Delete</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Add User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<form method="post" action="users_add.php">
<div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name"></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
<div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control" name="password"></div>
<div class="mb-3"><label class="form-label">Role</label>
<select class="form-select" name="role">
<option value="admin">Admin</option>
<option value="manager">Manager</option>
</select></div>
<button class="btn btn-primary w-100" type="submit">Save</button>
</form>
</div>
</div>
</div>
</div>
</div> <!-- END .content-wrapper -->
</div>
</body>
</html>
