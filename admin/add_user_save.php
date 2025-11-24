<?php
include '../includes/auth_check.php';
include '../includes/db.php';

// Restrict to admin
if ($_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        session_start();
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: add_user.php");
        exit();
    }

    if (!$password || strlen($password) < 8) {
        session_start();
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: add_user.php");
        exit();
    }

    if (!in_array($role, ['admin', 'user'])) {
        session_start();
        $_SESSION['error'] = "Please select a valid role.";
        header("Location: add_user.php");
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        session_start();
        $_SESSION['error'] = "Email already exists.";
        header("Location: add_user.php");
        exit();
    }
    $stmt->close();

    // Insert user into the database without hashing
    $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    if (!$stmt) {
        session_start();
        $_SESSION['error'] = "Database error: " . htmlspecialchars($conn->error);
        header("Location: add_user.php");
        exit();
    }

    $stmt->bind_param("sss", $email, $password, $role);
    if ($stmt->execute()) {
        session_start();
        $_SESSION['success'] = "User added successfully!";
        header("Location: add_user.php");
        exit();
    } else {
        session_start();
        $_SESSION['error'] = "Failed to add user: " . htmlspecialchars($stmt->error);
        header("Location: add_user.php");
        exit();
    }

    $stmt->close();
} else {
    session_start();
    $_SESSION['error'] = "Invalid request method.";
    header("Location: add_user.php");
    exit();
}

// Close database connection
$conn->close();
?>