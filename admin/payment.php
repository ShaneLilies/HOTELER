<?php
require_once '../config/db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Check if parameters are provided
if (!isset($_GET['bill_id']) || !isset($_GET['status'])) {
    header("Location: billing.php");
    exit();
}

$bill_id = intval($_GET['bill_id']);
$status = $_GET['status'];

// Validate status
$allowed_statuses = ['Paid', 'Pending', 'Cancelled'];
if (!in_array($status, $allowed_statuses)) {
    $_SESSION['error'] = "Invalid payment status!";
    header("Location: billing.php");
    exit();
}

// Update payment status
$stmt = $conn->prepare("UPDATE billing SET payment_status = ? WHERE bill_id = ?");
$stmt->bindValue(1, $status, SQLITE3_TEXT);
$stmt->bindValue(2, $bill_id, SQLITE3_INTEGER);

if ($stmt->execute()) {
    $_SESSION['success'] = "Payment status updated to: " . $status;
} else {
    $_SESSION['error'] = "Failed to update payment status!";
}

header("Location: billing.php");
exit();
?>