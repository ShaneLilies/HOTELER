<?php
/**
 * Database Initialization Script - ZAID HOTEL
 * Run this file ONCE to create the SQLite database with all tables and sample data
 * Access: http://localhost/hotel-reservation-system/init-database.php
 */

$db_path = __DIR__ . '/database/hotel_reservation.db';

// Create database directory
$db_dir = dirname($db_path);
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0777, true);
}

// Delete existing database if you want fresh start
if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
    if (file_exists($db_path)) {
        unlink($db_path);
        echo "<p style='color: orange;'>✓ Old database deleted</p>";
    }
}

try {
    $conn = new SQLite3($db_path);
    $conn->busyTimeout(5000);
    $conn->exec('PRAGMA foreign_keys = ON;');
    
    echo "<!DOCTYPE html><html><head><title>Database Setup - ZAID HOTEL</title>";
    echo "<style>body{font-family:Arial;padding:40px;background:#07203f;color:#ebded4;} .box{background:#02000d;padding:30px;border-radius:10px;max-width:800px;margin:0 auto;box-shadow:0 2px 10px rgba(0,0,0,0.3);border:2px solid #a65e46;} .success{color:#d9aa90;} .error{color:#a65e46;} pre{background:#07203f;padding:15px;border-radius:5px;overflow-x:auto;color:#ebded4;} .btn{background:#a65e46;color:#ebded4;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin:5px;} .btn:hover{background:#d9aa90;color:#02000d;}</style>";
    echo "</head><body><div class='box'>";
    echo "<h1 style='color:#d9aa90;'>🏨 ZAID HOTEL - Database Setup</h1>";
    
    // Create tables
    echo "<h2>Creating Tables...</h2>";
    
    // Admin table
    $conn->exec("CREATE TABLE IF NOT EXISTS admin (
        admin_id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p class='success'>✓ Admin table created</p>";
    
    // Guest table
    $conn->exec("CREATE TABLE IF NOT EXISTS guest (
        guest_id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        phone TEXT NOT NULL,
        address TEXT,
        password TEXT NOT NULL,
        created_date DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p class='success'>✓ Guest table created</p>";
    
    // Room type table with additional fields
    $conn->exec("CREATE TABLE IF NOT EXISTS room_type (
        room_type_id INTEGER PRIMARY KEY AUTOINCREMENT,
        type_name TEXT NOT NULL,
        nightly_rate REAL NOT NULL,
        max_guests INTEGER NOT NULL,
        description TEXT,
        thumbnail TEXT,
        image1 TEXT,
        image2 TEXT,
        image3 TEXT,
        image4 TEXT,
        image5 TEXT
    )");
    echo "<p class='success'>✓ Room_type table created</p>";
    
    // Room table
    $conn->exec("CREATE TABLE IF NOT EXISTS room (
        room_id INTEGER PRIMARY KEY AUTOINCREMENT,
        room_number TEXT NOT NULL UNIQUE,
        room_type_id INTEGER NOT NULL,
        status TEXT NOT NULL DEFAULT 'Available',
        floor TEXT NOT NULL,
        FOREIGN KEY (room_type_id) REFERENCES room_type(room_type_id) ON DELETE CASCADE
    )");
    echo "<p class='success'>✓ Room table created</p>";
    
    // Reservation table
    $conn->exec("CREATE TABLE IF NOT EXISTS reservation (
        reservation_id INTEGER PRIMARY KEY AUTOINCREMENT,
        guest_id INTEGER NOT NULL,
        room_id INTEGER NOT NULL,
        check_in_date DATE NOT NULL,
        check_out_date DATE NOT NULL,
        num_guests INTEGER NOT NULL,
        total_amount REAL NOT NULL,
        status TEXT NOT NULL DEFAULT 'Confirmed',
        booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (guest_id) REFERENCES guest(guest_id) ON DELETE CASCADE,
        FOREIGN KEY (room_id) REFERENCES room(room_id) ON DELETE CASCADE
    )");
    echo "<p class='success'>✓ Reservation table created</p>";
    
    // Billing table
    $conn->exec("CREATE TABLE IF NOT EXISTS billing (
        bill_id INTEGER PRIMARY KEY AUTOINCREMENT,
        reservation_id INTEGER NOT NULL,
        room_charge REAL NOT NULL,
        tax_amount REAL NOT NULL,
        total_amount REAL NOT NULL,
        payment_status TEXT NOT NULL DEFAULT 'Pending',
        bill_date DATE NOT NULL,
        FOREIGN KEY (reservation_id) REFERENCES reservation(reservation_id) ON DELETE CASCADE
    )");
    echo "<p class='success'>✓ Billing table created</p>";
    
    // Insert sample data
    echo "<h2>Inserting Sample Data...</h2>";
    
    // Insert admin (password: admin123)
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT OR IGNORE INTO admin (username, password) VALUES (?, ?)");
    $stmt->bindValue(1, 'admin', SQLITE3_TEXT);
    $stmt->bindValue(2, $admin_password, SQLITE3_TEXT);
    $stmt->execute();
    echo "<p class='success'>✓ Admin account created (username: admin, password: admin123)</p>";
    
    // Insert 5 room types
    $room_types = [
        ['Standard', 800.00, 2, 'Comfortable room with essential amenities, perfect for solo travelers or couples.'],
        ['Deluxe', 1500.00, 2, 'Spacious room with premium features and elegant furnishings for a luxurious stay.'],
        ['Family Suite', 2500.00, 4, 'Large suite designed for families, featuring multiple beds and living space.'],
        ['Presidential Suite', 5000.00, 4, 'Ultimate luxury suite with exclusive amenities and breathtaking views.'],
        ['Loft', 1800.00, 3, 'Modern loft-style room with high ceilings and contemporary design.']
    ];
    
    foreach ($room_types as $rt) {
        $stmt = $conn->prepare("INSERT INTO room_type (type_name, nightly_rate, max_guests, description) VALUES (?, ?, ?, ?)");
        $stmt->bindValue(1, $rt[0], SQLITE3_TEXT);
        $stmt->bindValue(2, $rt[1], SQLITE3_FLOAT);
        $stmt->bindValue(3, $rt[2], SQLITE3_INTEGER);
        $stmt->bindValue(4, $rt[3], SQLITE3_TEXT);
        $stmt->execute();
    }
    echo "<p class='success'>✓ Room types inserted (5 types)</p>";
    
    // Insert 15 rooms (3 per type)
    $rooms = [
        // Standard - 3 rooms
        ['101', 1, 'Available', '1'],
        ['102', 1, 'Available', '1'],
        ['103', 1, 'Available', '1'],
        
        // Deluxe - 3 rooms
        ['201', 2, 'Available', '2'],
        ['202', 2, 'Available', '2'],
        ['203', 2, 'Available', '2'],
        
        // Family Suite - 3 rooms
        ['301', 3, 'Available', '3'],
        ['302', 3, 'Available', '3'],
        ['303', 3, 'Available', '3'],
        
        // Presidential Suite - 3 rooms
        ['401', 4, 'Available', '4'],
        ['402', 4, 'Available', '4'],
        ['403', 4, 'Available', '4'],
        
        // Loft - 3 rooms
        ['501', 5, 'Available', '5'],
        ['502', 5, 'Available', '5'],
        ['503', 5, 'Available', '5']
    ];
    
    foreach ($rooms as $r) {
        $stmt = $conn->prepare("INSERT INTO room (room_number, room_type_id, status, floor) VALUES (?, ?, ?, ?)");
        $stmt->bindValue(1, $r[0], SQLITE3_TEXT);
        $stmt->bindValue(2, $r[1], SQLITE3_INTEGER);
        $stmt->bindValue(3, $r[2], SQLITE3_TEXT);
        $stmt->bindValue(4, $r[3], SQLITE3_TEXT);
        $stmt->execute();
    }
    echo "<p class='success'>✓ Rooms inserted (15 rooms total - 3 per type)</p>";
    
    // Display summary
    echo "<hr><h2 style='color:#d9aa90;'>✅ Database Setup Complete!</h2>";
    echo "<p><strong>Database location:</strong> " . $db_path . "</p>";
    
    // Count records
    $admin_count = $conn->querySingle("SELECT COUNT(*) FROM admin");
    $room_type_count = $conn->querySingle("SELECT COUNT(*) FROM room_type");
    $room_count = $conn->querySingle("SELECT COUNT(*) FROM room");
    
    echo "<p>📊 <strong>Admin accounts:</strong> $admin_count</p>";
    echo "<p>📊 <strong>Room types:</strong> $room_type_count</p>";
    echo "<p>📊 <strong>Total rooms:</strong> $room_count</p>";
    
    // Verify admin password
    $stmt = $conn->prepare("SELECT password FROM admin WHERE username = 'admin'");
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($row && password_verify('admin123', $row['password'])) {
        echo "<p class='success'>✓ Admin password verified successfully!</p>";
    } else {
        echo "<p class='error'>✗ Admin password verification failed!</p>";
    }
    
    echo "<hr>";
    echo "<h3 style='color:#d9aa90;'>🚀 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>User Site: <a href='user/index.php' class='btn'>Go to ZAID HOTEL</a></li>";
    echo "<li>User Login/Register: <a href='user/login.php' class='btn'>Guest Portal</a></li>";
    echo "<li>Admin Panel: <a href='admin/login.php' class='btn'>Admin Login</a></li>";
    echo "</ol>";
    
    echo "<p><a href='?reset=true' class='btn' style='background:#a65e46;' onclick='return confirm(\"Are you sure? This will delete all data!\")'>🔄 Reset Database</a></p>";
    
    echo "</div></body></html>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
?>