<?php
include '../includes/auth_check.php';
include '../includes/db.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $from_currency = filter_input(INPUT_POST, 'from_currency', FILTER_SANITIZE_STRING);
    $from_amount = filter_input(INPUT_POST, 'from_amount', FILTER_VALIDATE_FLOAT);
    $to_currency = filter_input(INPUT_POST, 'to_currency', FILTER_SANITIZE_STRING);
    $customer_receives = filter_input(INPUT_POST, 'customer_receives', FILTER_VALIDATE_FLOAT);

    // Validate inputs
    if (!$customer_id || !$from_currency || !$from_amount || !$to_currency || !$customer_receives) {
        // Store error message in session and redirect
        session_start();
        $_SESSION['error'] = "All fields are required and must be valid.";
        header("Location: transaction_form.php");
        exit();
    }

    // Validate currency values
    $valid_currencies = ['UGX', 'USD', 'USDT'];
    if (!in_array($from_currency, $valid_currencies) || !in_array($to_currency, $valid_currencies)) {
        session_start();
        $_SESSION['error'] = "Invalid currency selected.";
        header("Location: transaction_form.php");
        exit();
    }

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("
        INSERT INTO transactions (customer_id, from_currency, from_amount, to_currency, to_amount, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    if (!$stmt) {
        session_start();
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: transaction_form.php");
        exit();
    }

    // Bind parameters and execute
    $stmt->bind_param("isdsd", $customer_id, $from_currency, $from_amount, $to_currency, $customer_receives);
    if ($stmt->execute()) {
        // Success: Store success message and redirect
        session_start();
        $_SESSION['success'] = "Transaction saved successfully!";
        header("Location: transaction.php");
        exit();
    } else {
        // Error: Store error message and redirect
        session_start();
        $_SESSION['error'] = "Failed to save transaction: " . $stmt->error;
        header("Location: transaction_form.php");
        exit();
    }

    // Close statement
    $stmt->close();
} else {
    // If not a POST request, redirect to the form
    header("Location: transaction_form.php");
    exit();
}

// Close database connection
$conn->close();
?>