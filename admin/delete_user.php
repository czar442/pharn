<?php
include '../includes/auth_check.php';
include '../includes/db.php';

if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Access denied. Admins only.";
    header('Location: transaction.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];

    // Prevent self-deletion
    if ($user_id === $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete your own account.";
        header('Location: users.php');
        exit();
    }

    // Verify user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $_SESSION['error'] = "User not found.";
        header('Location: users.php');
        exit();
    }
    $stmt->close();

    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete user: " . $conn->error;
    }
    $stmt->close();
    $conn->close();
    header('Location: users.php');
    exit();
} else {
    $_SESSION['error'] = "Invalid request method.";
    header('Location: users.php');
    exit();
}
?>