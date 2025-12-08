<?php
require_once '../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: all-rooms.php");
    exit();
}

$room_id = intval($_GET['id']);

// Delete room from database using prepared statement
$delete_stmt = $conn->prepare("DELETE FROM room WHERE room_id = ?");
$delete_stmt->bindValue(1, $room_id, SQLITE3_INTEGER);

if ($delete_stmt->execute()) {
    $_SESSION['success'] = "Room deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete room!";
}

header("Location: all-rooms.php");
exit();
?>