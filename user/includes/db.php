<?php
$conn = new mysqli("localhost", "root", "", "transacash");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
