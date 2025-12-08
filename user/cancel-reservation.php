<?php
require_once '../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: my-reservations.php");
    exit();
}

$reservation_id = intval($_GET['id']);
$guest_id = $_SESSION['user_id'];

// Verify this reservation belongs to the logged-in user
$check_stmt = $conn->prepare("SELECT r.*, b.payment_status FROM reservation r LEFT JOIN billing b ON r.reservation_id = b.reservation_id WHERE r.reservation_id = ? AND r.guest_id = ?");
$check_stmt->bindValue(1, $reservation_id, SQLITE3_INTEGER);
$check_stmt->bindValue(2, $guest_id, SQLITE3_INTEGER);
$check_result = $check_stmt->execute();
$reservation = $check_result->fetchArray(SQLITE3_ASSOC);

if (!$reservation) {
    $_SESSION['error'] = "Reservation not found!";
    header("Location: my-reservations.php");
    exit();
}

// Only allow cancellation if status is Confirmed and payment is Pending
if ($reservation['status'] !== 'Confirmed' || $reservation['payment_status'] !== 'Pending') {
    $_SESSION['error'] = "This reservation cannot be cancelled!";
    header("Location: my-reservations.php");
    exit();
}

// Start transaction
$conn->exec('BEGIN');

try {
    // Update reservation status to Cancelled
    $update_res_stmt = $conn->prepare("UPDATE reservation SET status = 'Cancelled' WHERE reservation_id = ?");
    $update_res_stmt->bindValue(1, $reservation_id, SQLITE3_INTEGER);
    $update_res_stmt->execute();
    
    // Update billing status to Cancelled
    $update_bill_stmt = $conn->prepare("UPDATE billing SET payment_status = 'Cancelled' WHERE reservation_id = ?");
    $update_bill_stmt->bindValue(1, $reservation_id, SQLITE3_INTEGER);
    $update_bill_stmt->execute();
    
    // Make room available again
    $update_room_stmt = $conn->prepare("UPDATE room SET status = 'Available' WHERE room_id = ?");
    $update_room_stmt->bindValue(1, $reservation['room_id'], SQLITE3_INTEGER);
    $update_room_stmt->execute();
    
    // Commit transaction
    $conn->exec('COMMIT');
    
    $_SESSION['success'] = "Reservation cancelled successfully!";
    
} catch (Exception $e) {
    // Rollback on error
    $conn->exec('ROLLBACK');
    $_SESSION['error'] = "Failed to cancel reservation: " . $e->getMessage();
}

header("Location: my-reservations.php");
exit();
?>