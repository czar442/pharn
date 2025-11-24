<?php
session_start();
require 'db.php';

if(isset($_POST['email'], $_POST['password'])){
    $email = $_POST['email'];
    $pass = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND password=SHA2(?,256)");
    $stmt->execute([$email,$pass]);
    $user = $stmt->fetch();
    if($user){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Pharmacy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { overflow-x: hidden; }
.sidebar { min-height: 100vh; background-color: #0d6efd; color: white; padding-top: 1rem; }
.sidebar a { color: white; text-decoration: none; display: block; padding: 0.75rem 1rem; }
.sidebar a:hover { background-color: rgba(255,255,255,0.1); }
.content { margin-left: 220px; padding: 20px; }
</style>
</head>
<body class="bg-light">

<div class="container vh-100 d-flex justify-content-center align-items-center">
    <div class="card p-4 shadow" style="width: 400px;">
        <h3 class="text-center mb-3">Pharmacy Login</h3>
        <?php if(isset($error)) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</div>
</body>
</html>
