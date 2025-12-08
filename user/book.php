<?php
require_once '../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    $redirect_url = "book.php?" . http_build_query($_GET);
    header("Location: login.php?redirect=" . urlencode($redirect_url));
    exit();
}

$page_title = "Book Room - ZAID HOTEL";

// Check if room_type_id is provided
if (!isset($_GET['room_type_id']) || empty($_GET['room_type_id'])) {
    header("Location: rooms.php");
    exit();
}

$room_type_id = intval($_GET['room_type_id']);
$check_in = $_GET['check_in'] ?? date('Y-m-d');
$check_out = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));

// Fetch room type details
$stmt = $conn->prepare("SELECT * FROM room_type WHERE room_type_id = ?");
$stmt->bindValue(1, $room_type_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$room_type = $result->fetchArray(SQLITE3_ASSOC);

if (!$room_type) {
    header("Location: rooms.php");
    exit();
}

// Validate and fix dates
if (empty($check_in) || strtotime($check_in) < strtotime(date('Y-m-d'))) {
    $check_in = date('Y-m-d');
}
if (empty($check_out) || strtotime($check_out) <= strtotime($check_in)) {
    $check_out = date('Y-m-d', strtotime($check_in . ' +1 day'));
}

// Calculate number of nights and total
$nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
$room_charge = $nights * $room_type['nightly_rate'];
$tax_rate = 0.12; // 12% tax
$tax_amount = $room_charge * $tax_rate;
$total_amount = $room_charge + $tax_amount;

// FIXED: Check availability - only check for overlapping dates
// A room is available if:
// 1. Room status is 'Available' 
// 2. Room has NO confirmed/occupied reservations that OVERLAP with requested dates
$avail_stmt = $conn->prepare("
    SELECT r.room_id, r.room_number, r.floor
    FROM room r 
    WHERE r.room_type_id = ? 
    AND r.status IN ('Available', 'Occupied')
    AND r.room_id NOT IN (
        SELECT room_id FROM reservation 
        WHERE status IN ('Confirmed', 'Occupied')
        AND NOT (
            check_out_date <= ? OR check_in_date >= ?
        )
    )
    LIMIT 1
");
$avail_stmt->bindValue(1, $room_type_id, SQLITE3_INTEGER);
$avail_stmt->bindValue(2, $check_in, SQLITE3_TEXT);
$avail_stmt->bindValue(3, $check_out, SQLITE3_TEXT);
$avail_result = $avail_stmt->execute();
$available_room = $avail_result->fetchArray(SQLITE3_ASSOC);

// Count total available rooms for this date range
$count_stmt = $conn->prepare("
    SELECT COUNT(*) as available_count
    FROM room r 
    WHERE r.room_type_id = ? 
    AND r.status IN ('Available', 'Occupied')
    AND r.room_id NOT IN (
        SELECT room_id FROM reservation 
        WHERE status IN ('Confirmed', 'Occupied')
        AND NOT (
            check_out_date <= ? OR check_in_date >= ?
        )
    )
");
$count_stmt->bindValue(1, $room_type_id, SQLITE3_INTEGER);
$count_stmt->bindValue(2, $check_in, SQLITE3_TEXT);
$count_stmt->bindValue(3, $check_out, SQLITE3_TEXT);
$count_result = $count_stmt->execute();
$availability = $count_result->fetchArray(SQLITE3_ASSOC);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: var(--secondary-dark);">
                    <h3 class="mb-0"><i class="bi bi-calendar-check"></i> Complete Your Booking</h3>
                </div>
                <div class="card-body">
                    <!-- Room Type Details Summary -->
                    <div class="alert" style="background-color: var(--light-cream); border-left: 4px solid var(--accent-brown);">
                        <h5 style="color: var(--secondary-dark);">Room Type Details</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <p class="mb-1"><strong>Room Type:</strong> <?php echo htmlspecialchars($room_type['type_name']); ?></p>
                                <p class="mb-1"><strong>Max Guests:</strong> <?php echo $room_type['max_guests']; ?> person(s)</p>
                                <p class="mb-1"><strong>Rate per night:</strong> $<?php echo number_format($room_type['nightly_rate'], 2); ?></p>
                                <p class="mb-1">
                                    <strong>Available Rooms for selected dates:</strong> 
                                    <span class="badge bg-<?php echo $availability['available_count'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $availability['available_count']; ?> room(s)
                                    </span>
                                </p>
                                <?php if (!empty($room_type['description'])): ?>
                                <p class="mb-0 mt-2 text-muted small"><?php echo htmlspecialchars($room_type['description']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <?php if (!empty($room_type['thumbnail'])): ?>
                                    <img src="../uploads/room_images/<?php echo htmlspecialchars($room_type['thumbnail']); ?>" 
                                         class="img-thumbnail" 
                                         style="max-height: 120px; object-fit: cover;"
                                         alt="<?php echo htmlspecialchars($room_type['type_name']); ?>">
                                <?php else: ?>
                                    <div class="text-white p-3 rounded" style="background-color: var(--accent-brown);">
                                        <i class="bi bi-door-open" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!$available_room): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> 
                            <strong>Sorry!</strong> No rooms of this type are available for the selected dates 
                            (<?php echo date('M d, Y', strtotime($check_in)); ?> to <?php echo date('M d, Y', strtotime($check_out)); ?>).
                        </div>
                        <div class="text-center">
                            <a href="rooms.php" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i> Try Different Dates
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Billing Summary -->
                        <div class="card mb-4" style="background-color: var(--light-cream);">
                            <div class="card-body">
                                <h5 class="card-title" style="color: var(--secondary-dark);">Billing Summary</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Check-in Date:</td>
                                        <td class="text-end"><strong><?php echo date('M d, Y', strtotime($check_in)); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Check-out Date:</td>
                                        <td class="text-end"><strong><?php echo date('M d, Y', strtotime($check_out)); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Number of Nights:</td>
                                        <td class="text-end"><strong><?php echo $nights; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Room Charge:</td>
                                        <td class="text-end">$<?php echo number_format($room_charge, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tax (12%):</td>
                                        <td class="text-end">$<?php echo number_format($tax_amount, 2); ?></td>
                                    </tr>
                                    <tr style="background-color: var(--warm-tan);">
                                        <td><strong>Total Amount:</strong></td>
                                        <td class="text-end"><strong style="color: var(--primary-dark);">$<?php echo number_format($total_amount, 2); ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Booking Form -->
                        <form action="booking-handler.php" method="POST">
                            <input type="hidden" name="room_type_id" value="<?php echo $room_type['room_type_id']; ?>">
                            <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                            <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                            <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
                            <input type="hidden" name="room_charge" value="<?php echo $room_charge; ?>">
                            <input type="hidden" name="tax_amount" value="<?php echo $tax_amount; ?>">

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-people"></i> Number of Guests *</label>
                                <input type="number" class="form-control" name="num_guests" 
                                       min="1" max="<?php echo $room_type['max_guests']; ?>" value="1" required>
                                <small class="text-muted">Maximum: <?php echo $room_type['max_guests']; ?> guest(s) for this room type</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="bi bi-credit-card"></i> Payment Status *</label>
                                <select class="form-select" name="payment_status" required>
                                    <option value="Pending">Pay Later (Pending)</option>
                                    <option value="Paid">Pay Now (Paid)</option>
                                </select>
                                <small class="text-muted">Note: Rooms are automatically marked as occupied when payment is made.</small>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                <strong>Room Assignment:</strong> Room #<?php echo htmlspecialchars($available_room['room_number']); ?> 
                                (Floor <?php echo htmlspecialchars($available_room['floor']); ?>) will be assigned to you upon confirmation.
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Confirm Booking
                                </button>
                                <a href="rooms.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to Rooms
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>