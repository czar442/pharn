<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $query = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    if ($user = $result->fetch_assoc()) {
        // Check password (if you used hashing, replace this line)
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] === 'user') {
                header("Location: user/dashboard.php");
            } else {
                $_SESSION['error'] = "Unknown role.";
                header("Location: index.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Incorrect password.";
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "User not found.";
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
