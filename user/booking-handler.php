<?php
require_once '../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_type_id = intval($_POST['room_type_id']);
    $guest_id = $_SESSION['user_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $num_guests = intval($_POST['num_guests']);
    $total_amount = floatval($_POST['total_amount']);
    $room_charge = floatval($_POST['room_charge']);
    $tax_amount = floatval($_POST['tax_amount']);
    $payment_status = $_POST['payment_status'];

    // Validate inputs
    if (empty($room_type_id) || empty($check_in) || empty($check_out) || empty($num_guests)) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: rooms.php");
        exit();
    }

    // Validate dates
    if (strtotime($check_in) >= strtotime($check_out)) {
        $_SESSION['error'] = "Check-out date must be after check-in date!";
        header("Location: rooms.php");
        exit();
    }

    // CRITICAL: Find an available room of this type that's not booked during these dates
    $available_room_stmt = $conn->prepare("
        SELECT room_id FROM room 
        WHERE room_type_id = ? 
        AND status = 'Available'
        AND room_id NOT IN (
            SELECT room_id FROM reservation 
            WHERE status IN ('Confirmed', 'Occupied')
            AND (
                (check_in_date <= ? AND check_out_date > ?) OR
                (check_in_date < ? AND check_out_date >= ?) OR
                (check_in_date >= ? AND check_out_date <= ?)
            )
        )
        LIMIT 1
    ");
    $available_room_stmt->bindValue(1, $room_type_id, SQLITE3_INTEGER);
    $available_room_stmt->bindValue(2, $check_in, SQLITE3_TEXT);
    $available_room_stmt->bindValue(3, $check_in, SQLITE3_TEXT);
    $available_room_stmt->bindValue(4, $check_out, SQLITE3_TEXT);
    $available_room_stmt->bindValue(5, $check_out, SQLITE3_TEXT);
    $available_room_stmt->bindValue(6, $check_in, SQLITE3_TEXT);
    $available_room_stmt->bindValue(7, $check_out, SQLITE3_TEXT);
    
    $available_result = $available_room_stmt->execute();
    $available_room = $available_result->fetchArray(SQLITE3_ASSOC);

    if (!$available_room) {
        $_SESSION['error'] = "Sorry, no rooms of this type are available for the selected dates!";
        header("Location: rooms.php");
        exit();
    }

    $room_id = $available_room['room_id'];

    // Start transaction
    $conn->exec('BEGIN');


    $check_stmt = $conn->prepare("
    SELECT COUNT(*) as count FROM reservation 
    WHERE room_id = ? 
    AND status = 'Confirmed' 
    AND (
        (check_in_date <= ? AND check_out_date > ?) OR
        (check_in_date < ? AND check_out_date >= ?) OR
        (check_in_date >= ? AND check_out_date <= ?)
        )
    ");
    $check_stmt->bindValue(1, $room_id, SQLITE3_INTEGER);
    $check_stmt->bindValue(2, $check_in, SQLITE3_TEXT);
    $check_stmt->bindValue(3, $check_in, SQLITE3_TEXT);
    $check_stmt->bindValue(4, $check_out, SQLITE3_TEXT);
    $check_stmt->bindValue(5, $check_out, SQLITE3_TEXT);
    $check_stmt->bindValue(6, $check_in, SQLITE3_TEXT);
    $check_stmt->bindValue(7, $check_out, SQLITE3_TEXT);
    $check_result = $check_stmt->execute();
    $check_row = $check_result->fetchArray(SQLITE3_ASSOC);

    if ($check_row['count'] > 0) {
        $_SESSION['error'] = "This room is already booked for the selected dates!";
        header("Location: book.php?room_id=" . $room_id);
        exit();
    }
    try {
        // Insert reservation
        $res_stmt = $conn->prepare("INSERT INTO reservation (guest_id, room_id, check_in_date, check_out_date, num_guests, total_amount, status) 
                                    VALUES (?, ?, ?, ?, ?, ?, 'Confirmed')");
        $res_stmt->bindValue(1, $guest_id, SQLITE3_INTEGER);
        $res_stmt->bindValue(2, $room_id, SQLITE3_INTEGER);
        $res_stmt->bindValue(3, $check_in, SQLITE3_TEXT);
        $res_stmt->bindValue(4, $check_out, SQLITE3_TEXT);
        $res_stmt->bindValue(5, $num_guests, SQLITE3_INTEGER);
        $res_stmt->bindValue(6, $total_amount, SQLITE3_FLOAT);
        $res_stmt->execute();
        
        $reservation_id = $conn->lastInsertRowID();
        
        // Insert billing
        $bill_stmt = $conn->prepare("INSERT INTO billing (reservation_id, room_charge, tax_amount, total_amount, payment_status, bill_date) 
                                     VALUES (?, ?, ?, ?, ?, DATE('now'))");
        $bill_stmt->bindValue(1, $reservation_id, SQLITE3_INTEGER);
        $bill_stmt->bindValue(2, $room_charge, SQLITE3_FLOAT);
        $bill_stmt->bindValue(3, $tax_amount, SQLITE3_FLOAT);
        $bill_stmt->bindValue(4, $total_amount, SQLITE3_FLOAT);
        $bill_stmt->bindValue(5, $payment_status, SQLITE3_TEXT);
        $bill_stmt->execute();
        
        // If payment is already made, mark room as Occupied
        if ($payment_status === 'Paid') {
            $update_stmt = $conn->prepare("UPDATE room SET status = 'Occupied' WHERE room_id = ?");
            $update_stmt->bindValue(1, $room_id, SQLITE3_INTEGER);
            $update_stmt->execute();
        }
        
        // Commit transaction
        $conn->exec('COMMIT');
        
        // Get room details for confirmation
        $room_stmt = $conn->prepare("SELECT r.room_number, r.floor, rt.type_name, rt.nightly_rate 
                                     FROM room r 
                                     INNER JOIN room_type rt ON r.room_type_id = rt.room_type_id 
                                     WHERE r.room_id = ?");
        $room_stmt->bindValue(1, $room_id, SQLITE3_INTEGER);
        $room_result = $room_stmt->execute();
        $room = $room_result->fetchArray(SQLITE3_ASSOC);
        
        $page_title = "Booking Successful - ZAID HOTEL";
        include 'includes/header.php';
        include 'includes/navbar.php';
        ?>
        
        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0">
                        <div class="card-body text-center p-5">
                            <div class="mb-4">
                                <i class="bi bi-check-circle-fill" style="font-size: 5rem; color: var(--accent-brown);"></i>
                            </div>
                            <h1 style="color: var(--accent-brown);" class="mb-4">Booking Confirmed!</h1>
                            <p class="lead mb-4">Thank you for choosing ZAID HOTEL. Your reservation has been confirmed.</p>
                            
                            <div class="card mb-4" style="background-color: var(--light-cream);">
                                <div class="card-body text-start">
                                    <h5 class="card-title mb-3" style="color: var(--secondary-dark);">Reservation Details</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Reservation ID:</strong> #<?php echo str_pad($reservation_id, 6, '0', STR_PAD_LEFT); ?></p>
                                            <p><strong>Guest Name:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                                            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($room['type_name']); ?></p>
                                            <p><strong>Room Number:</strong> <?php echo htmlspecialchars($room['room_number']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Floor:</strong> <?php echo htmlspecialchars($room['floor']); ?></p>
                                            <p><strong>Check-in:</strong> <?php echo date('M d, Y', strtotime($check_in)); ?></p>
                                            <p><strong>Check-out:</strong> <?php echo date('M d, Y', strtotime($check_out)); ?></p>
                                            <p><strong>Number of Guests:</strong> <?php echo $num_guests; ?></p>
                                            <p><strong>Total Amount:</strong> $<?php echo number_format($total_amount, 2); ?></p>
                                            <p><strong>Payment Status:</strong> 
                                                <span class="badge bg-<?php echo $payment_status === 'Paid' ? 'success' : 'warning'; ?>">
                                                    <?php echo $payment_status; ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Your reservation has been recorded. You can view it anytime in your reservations page.
                            </div>
                            
                            <div class="d-grid gap-2 col-md-8 mx-auto">
                                <a href="my-reservations.php" class="btn btn-primary btn-lg">View My Reservations</a>
                                <a href="index.php" class="btn btn-outline-primary">Back to Home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
        include 'includes/footer.php';
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->exec('ROLLBACK');
        $_SESSION['error'] = "Booking failed: " . $e->getMessage();
        header("Location: rooms.php");
        exit();
    }
    
} else {
    header("Location: rooms.php");
    exit();
}
?>