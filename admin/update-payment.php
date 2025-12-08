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

// Start transaction
$conn->exec('BEGIN');

try {
    // Update payment status
    $stmt = $conn->prepare("UPDATE billing SET payment_status = ? WHERE bill_id = ?");
    $stmt->bindValue(1, $status, SQLITE3_TEXT);
    $stmt->bindValue(2, $bill_id, SQLITE3_INTEGER);
    $stmt->execute();
    
    // If payment is marked as Paid, update room status to Occupied
    if ($status === 'Paid') {
        // Get reservation_id from billing
        $bill_stmt = $conn->prepare("SELECT reservation_id FROM billing WHERE bill_id = ?");
        $bill_stmt->bindValue(1, $bill_id, SQLITE3_INTEGER);
        $bill_result = $bill_stmt->execute();
        $bill_data = $bill_result->fetchArray(SQLITE3_ASSOC);
        
        if ($bill_data) {
            // Get room_id from reservation
            $res_stmt = $conn->prepare("SELECT room_id FROM reservation WHERE reservation_id = ?");
            $res_stmt->bindValue(1, $bill_data['reservation_id'], SQLITE3_INTEGER);
            $res_result = $res_stmt->execute();
            $res_data = $res_result->fetchArray(SQLITE3_ASSOC);
            
            if ($res_data) {
                // Mark room as Occupied
                $room_stmt = $conn->prepare("UPDATE room SET status = 'Occupied' WHERE room_id = ?");
                $room_stmt->bindValue(1, $res_data['room_id'], SQLITE3_INTEGER);
                $room_stmt->execute();
            }
        }
    }
    
    // Commit transaction
    $conn->exec('COMMIT');
    $_SESSION['success'] = "Payment status updated to: " . $status . ($status === 'Paid' ? ' (Room marked as Occupied)' : '');
    
} catch (Exception $e) {
    // Rollback on error
    $conn->exec('ROLLBACK');
    $_SESSION['error'] = "Failed to update payment status: " . $e->getMessage();
}

header("Location: billing.php");
exit();
?>