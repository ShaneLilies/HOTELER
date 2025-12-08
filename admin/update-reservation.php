<?php
require_once '../config/db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Check if parameters are provided
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    $_SESSION['error'] = "Invalid request parameters!";
    header("Location: reservations.php");
    exit();
}

$reservation_id = intval($_GET['id']);
$new_status = $_GET['status'];

// Validate status
$allowed_statuses = ['Confirmed', 'Completed', 'Cancelled'];
if (!in_array($new_status, $allowed_statuses)) {
    $_SESSION['error'] = "Invalid reservation status!";
    header("Location: reservations.php");
    exit();
}

// Fetch reservation details
$fetch_stmt = $conn->prepare("SELECT r.*, b.bill_id, b.payment_status FROM reservation r 
                               LEFT JOIN billing b ON r.reservation_id = b.reservation_id 
                               WHERE r.reservation_id = ?");
$fetch_stmt->bindValue(1, $reservation_id, SQLITE3_INTEGER);
$fetch_result = $fetch_stmt->execute();
$reservation = $fetch_result->fetchArray(SQLITE3_ASSOC);

if (!$reservation) {
    $_SESSION['error'] = "Reservation not found!";
    header("Location: reservations.php");
    exit();
}

// Start transaction
$conn->exec('BEGIN');

try {
    // Update reservation status
    $update_stmt = $conn->prepare("UPDATE reservation SET status = ? WHERE reservation_id = ?");
    $update_stmt->bindValue(1, $new_status, SQLITE3_TEXT);
    $update_stmt->bindValue(2, $reservation_id, SQLITE3_INTEGER);
    $update_stmt->execute();
    
    // Handle room status and billing based on new status
    if ($new_status === 'Completed') {
        // Mark room as Available when reservation is completed
        $room_stmt = $conn->prepare("UPDATE room SET status = 'Available' WHERE room_id = ?");
        $room_stmt->bindValue(1, $reservation['room_id'], SQLITE3_INTEGER);
        $room_stmt->execute();
        
        // If payment is still pending, mark it as paid (assuming they paid on checkout)
        if ($reservation['payment_status'] === 'Pending') {
            $billing_stmt = $conn->prepare("UPDATE billing SET payment_status = 'Paid' WHERE reservation_id = ?");
            $billing_stmt->bindValue(1, $reservation_id, SQLITE3_INTEGER);
            $billing_stmt->execute();
        }
        
    } elseif ($new_status === 'Cancelled') {
        // Mark room as Available when reservation is cancelled
        $room_stmt = $conn->prepare("UPDATE room SET status = 'Available' WHERE room_id = ?");
        $room_stmt->bindValue(1, $reservation['room_id'], SQLITE3_INTEGER);
        $room_stmt->execute();
        
        // Update billing status to cancelled
        if ($reservation['bill_id']) {
            $billing_stmt = $conn->prepare("UPDATE billing SET payment_status = 'Cancelled' WHERE reservation_id = ?");
            $billing_stmt->bindValue(1, $reservation_id, SQLITE3_INTEGER);
            $billing_stmt->execute();
        }
    }
    
    // Commit transaction
    $conn->exec('COMMIT');
    
    $_SESSION['success'] = "Reservation status updated to: " . $new_status;
    
} catch (Exception $e) {
    // Rollback on error
    $conn->exec('ROLLBACK');
    $_SESSION['error'] = "Failed to update reservation: " . $e->getMessage();
}

// Redirect back
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'view-reservation.php') !== false) {
    header("Location: view-reservation.php?id=" . $reservation_id);
} else {
    header("Location: reservations.php");
}
exit();
?>