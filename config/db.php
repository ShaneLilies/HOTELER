<?php
// Database Configuration for SQLite3
$db_path = __DIR__ . '/../database/hotel_reservation.db';

// Create database directory if it doesn't exist
$db_dir = dirname($db_path);
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0777, true);
}

// Create SQLite3 connection
try {
    $conn = new SQLite3($db_path);
    $conn->busyTimeout(5000);
    $conn->exec('PRAGMA foreign_keys = ON;');
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper function for prepared statements
function db_query($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        return false;
    }
    
    foreach ($params as $index => $value) {
        $stmt->bindValue($index + 1, $value);
    }
    
    return $stmt->execute();
}

// Helper function to fetch results
function db_fetch($result) {
    return $result->fetchArray(SQLITE3_ASSOC);
}

// Helper function to fetch all results
function db_fetch_all($result) {
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}
?>