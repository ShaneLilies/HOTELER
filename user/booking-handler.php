<?php
require_once '../config/db.php';
require_once '../config/settings.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_type_id = intval($_POST['room_type_id']);
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $check_in_time = $_POST['check_in_time'] ?? '14:00';
    $check_out_time = $_POST['check_out_time'] ?? '12:00';
    $num_guests = intval($_POST['num_guests']);
    $total_amount = floatval($_POST['total_amount']);
    $room_charge = floatval($_POST['room_charge']);
    $tax_amount = floatval($_POST['tax_amount']);
    $payment_status = $_POST['payment_status'];
    $is_guest_checkout = isset($_POST['is_guest_checkout']) && $_POST['is_guest_checkout'] == '1';

    // Validate inputs
    if (empty($room_type_id) || empty($check_in) || empty($check_out) || empty($num_guests)) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: rooms.php");
        exit();
    }

    // Combine date and time for check-in and check-out
    $check_in_datetime = $check_in . ' ' . $check_in_time . ':00';
    $check_out_datetime = $check_out . ' ' . $check_out_time . ':00';
    
    // Validate dates
    if (strtotime($check_in_datetime) >= strtotime($check_out_datetime)) {
        $_SESSION['error'] = "Check-out date must be after check-in date!";
        header("Location: rooms.php");
        exit();
    }

    // Handle guest checkout (create temporary guest account)
    if ($is_guest_checkout) {
        $guest_first_name = trim($_POST['guest_first_name']);
        $guest_last_name = trim($_POST['guest_last_name']);
        $guest_email = trim($_POST['guest_email']);
        $guest_phone = trim($_POST['guest_phone']);
        $guest_address = trim($_POST['guest_address'] ?? '');

        // Validate guest info
        if (empty($guest_first_name) || empty($guest_last_name) || empty($guest_email) || empty($guest_phone)) {
            $_SESSION['error'] = "Please fill in all guest information!";
            header("Location: book.php?room_type_id=" . $room_type_id);
            exit();
        }

        // Check if guest email already exists
        $check_guest = $conn->prepare("SELECT guest_id FROM guest WHERE email = ?");
        $check_guest->bindValue(1, $guest_email, SQLITE3_TEXT);
        $check_result = $check_guest->execute();
        $existing_guest = $check_result->fetchArray(SQLITE3_ASSOC);

        if ($existing_guest) {
            $guest_id = $existing_guest['guest_id'];
        } else {
            // Create guest account with random password
            $random_password = bin2hex(random_bytes(8));
            $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);

            $guest_stmt = $conn->prepare("INSERT INTO guest (first_name, last_name, email, phone, address, password) VALUES (?, ?, ?, ?, ?, ?)");
            $guest_stmt->bindValue(1, $guest_first_name, SQLITE3_TEXT);
            $guest_stmt->bindValue(2, $guest_last_name, SQLITE3_TEXT);
            $guest_stmt->bindValue(3, $guest_email, SQLITE3_TEXT);
            $guest_stmt->bindValue(4, $guest_phone, SQLITE3_TEXT);
            $guest_stmt->bindValue(5, $guest_address, SQLITE3_TEXT);
            $guest_stmt->bindValue(6, $hashed_password, SQLITE3_TEXT);
            $guest_stmt->execute();

            $guest_id = $conn->lastInsertRowID();
        }
    } else {
        // Use logged-in user
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header("Location: login.php");
            exit();
        }
        $guest_id = $_SESSION['user_id'];
    }

    // Find available room
    $available_room_stmt = $conn->prepare("
        SELECT room_id FROM room 
        WHERE room_type_id = ? 
        AND status IN ('Available', 'Occupied')
        AND room_id NOT IN (
            SELECT room_id FROM reservation 
            WHERE status IN ('Confirmed', 'Occupied')
            AND NOT (check_out_date <= ? OR check_in_date >= ?)
        )
        LIMIT 1
    ");
    $available_room_stmt->bindValue(1, $room_type_id, SQLITE3_INTEGER);
    $available_room_stmt->bindValue(2, $check_in_datetime, SQLITE3_TEXT);
    $available_room_stmt->bindValue(3, $check_out_datetime, SQLITE3_TEXT);
    $available_result = $available_room_stmt->execute();
    $available_room = $available_result->fetchArray(SQLITE3_ASSOC);

    if (!$available_room) {
        $_SESSION['error'] = "Sorry, no rooms available for the selected dates!";
        header("Location: rooms.php");
        exit();
    }

    $room_id = $available_room['room_id'];

    // Start transaction
    $conn->exec('BEGIN');

    try {
        // Insert reservation with explicit booking_date in Philippine timezone
        $booking_datetime = get_current_datetime();
        $res_stmt = $conn->prepare("INSERT INTO reservation (guest_id, room_id, check_in_date, check_out_date, num_guests, total_amount, status, booking_date) 
                                    VALUES (?, ?, ?, ?, ?, ?, 'Confirmed', ?)");
        $res_stmt->bindValue(1, $guest_id, SQLITE3_INTEGER);
        $res_stmt->bindValue(2, $room_id, SQLITE3_INTEGER);
        $res_stmt->bindValue(3, $check_in_datetime, SQLITE3_TEXT);
        $res_stmt->bindValue(4, $check_out_datetime, SQLITE3_TEXT);
        $res_stmt->bindValue(5, $num_guests, SQLITE3_INTEGER);
        $res_stmt->bindValue(6, $total_amount, SQLITE3_FLOAT);
        $res_stmt->bindValue(7, $booking_datetime, SQLITE3_TEXT);
        $res_stmt->execute();
        
        $reservation_id = $conn->lastInsertRowID();
        
        // Insert billing with explicit bill_date in Philippine timezone
        $bill_datetime = get_current_datetime();
        $bill_stmt = $conn->prepare("INSERT INTO billing (reservation_id, room_charge, tax_amount, total_amount, payment_status, bill_date) 
                                     VALUES (?, ?, ?, ?, ?, ?)");
        $bill_stmt->bindValue(1, $reservation_id, SQLITE3_INTEGER);
        $bill_stmt->bindValue(2, $room_charge, SQLITE3_FLOAT);
        $bill_stmt->bindValue(3, $tax_amount, SQLITE3_FLOAT);
        $bill_stmt->bindValue(4, $total_amount, SQLITE3_FLOAT);
        $bill_stmt->bindValue(5, $payment_status, SQLITE3_TEXT);
        $bill_stmt->bindValue(6, $bill_datetime, SQLITE3_TEXT);
        $bill_stmt->execute();
        
        // If payment is made, mark room as Occupied
        if ($payment_status === 'Paid') {
            $update_stmt = $conn->prepare("UPDATE room SET status = 'Occupied' WHERE room_id = ?");
            $update_stmt->bindValue(1, $room_id, SQLITE3_INTEGER);
            $update_stmt->execute();
        }
        
        // Commit transaction
        $conn->exec('COMMIT');
        
        // Get room details
        $room_stmt = $conn->prepare("SELECT r.room_number, r.floor, rt.type_name 
                                     FROM room r 
                                     INNER JOIN room_type rt ON r.room_type_id = rt.room_type_id 
                                     WHERE r.room_id = ?");
        $room_stmt->bindValue(1, $room_id, SQLITE3_INTEGER);
        $room_result = $room_stmt->execute();
        $room = $room_result->fetchArray(SQLITE3_ASSOC);

        // Get guest details
        $guest_stmt = $conn->prepare("SELECT first_name, last_name, email FROM guest WHERE guest_id = ?");
        $guest_stmt->bindValue(1, $guest_id, SQLITE3_INTEGER);
        $guest_result = $guest_stmt->execute();
        $guest = $guest_result->fetchArray(SQLITE3_ASSOC);
        
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
                                            <p><strong>Guest Name:</strong> <?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($guest['email']); ?></p>
                                            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($room['type_name']); ?></p>
                                            <p><strong>Room Number:</strong> <?php echo htmlspecialchars($room['room_number']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Floor:</strong> <?php echo htmlspecialchars($room['floor']); ?></p>
                                            <p><strong>Check-in:</strong> <?php echo date('M d, Y h:i A', strtotime($check_in_datetime)); ?></p>
                                            <p><strong>Check-out:</strong> <?php echo date('M d, Y h:i A', strtotime($check_out_datetime)); ?></p>
                                            <p><strong>Guests:</strong> <?php echo $num_guests; ?></p>
                                            <p><strong>Total:</strong> ₱<?php echo number_format($total_amount, 2); ?></p>
                                            <p><strong>Payment:</strong> 
                                                <span class="badge bg-<?php echo $payment_status === 'Paid' ? 'success' : 'warning'; ?>">
                                                    <?php echo $payment_status; ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($is_guest_checkout): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                <strong>Guest Booking:</strong> A confirmation has been sent to <?php echo htmlspecialchars($guest['email']); ?>. 
                                You can show your reservation ID at check-in.
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                Your reservation is saved. View it anytime in your reservations page.
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2 col-md-8 mx-auto">
                                <?php if (!$is_guest_checkout): ?>
                                <a href="my-reservations.php" class="btn btn-primary btn-lg">View My Reservations</a>
                                <?php endif; ?>
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