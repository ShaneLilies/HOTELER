<?php
require_once '../config/db.php';
session_start();

// Check if room_type_id is provided
if (!isset($_GET['room_type_id']) || empty($_GET['room_type_id'])) {
    header("Location: rooms.php");
    exit();
}

$page_title = "Book Room - ZAID HOTEL";
$room_type_id = intval($_GET['room_type_id']);
$check_in = $_GET['check_in'] ?? date('Y-m-d');
$check_out = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
$check_in_time = $_GET['check_in_time'] ?? '14:00';
$check_out_time = $_GET['check_out_time'] ?? '12:00';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

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

// Calculate billing
$nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
$room_charge = $nights * $room_type['nightly_rate'];
$tax_rate = 0.12;
$tax_amount = $room_charge * $tax_rate;
$total_amount = $room_charge + $tax_amount;

// Check availability
$avail_stmt = $conn->prepare("
    SELECT r.room_id, r.room_number, r.floor
    FROM room r 
    WHERE r.room_type_id = ? 
    AND r.status IN ('Available', 'Occupied')
    AND r.room_id NOT IN (
        SELECT room_id FROM reservation 
        WHERE status IN ('Confirmed', 'Occupied')
        AND NOT (check_out_date <= ? OR check_in_date >= ?)
    )
    LIMIT 1
");
$avail_stmt->bindValue(1, $room_type_id, SQLITE3_INTEGER);
$avail_stmt->bindValue(2, $check_in, SQLITE3_TEXT);
$avail_stmt->bindValue(3, $check_out, SQLITE3_TEXT);
$avail_result = $avail_stmt->execute();
$available_room = $avail_result->fetchArray(SQLITE3_ASSOC);

// Count available rooms
$count_stmt = $conn->prepare("
    SELECT COUNT(*) as available_count
    FROM room r 
    WHERE r.room_type_id = ? 
    AND r.status IN ('Available', 'Occupied')
    AND r.room_id NOT IN (
        SELECT room_id FROM reservation 
        WHERE status IN ('Confirmed', 'Occupied')
        AND NOT (check_out_date <= ? OR check_in_date >= ?)
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

<style>
.guest-info-section {
    background-color: var(--light-cream);
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid var(--accent-brown);
}

.toggle-btn {
    cursor: pointer;
    transition: all 0.3s ease;
}

.toggle-btn:hover {
    background-color: var(--warm-tan) !important;
}
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php if (!$is_logged_in): ?>
            <div class="alert alert-info mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-info-circle"></i>
                        <strong>Quick Booking:</strong> You can book without an account, or 
                        <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="alert-link">
                            <strong>login</strong>
                        </a> for faster checkout.
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: var(--secondary-dark);">
                    <h3 class="mb-0"><i class="bi bi-calendar-check"></i> Complete Your Booking</h3>
                </div>
                <div class="card-body">
                    <!-- Room Type Details -->
                    <div class="alert" style="background-color: var(--light-cream); border-left: 4px solid var(--accent-brown);">
                        <h5 style="color: var(--secondary-dark);">Room Type Details</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <p class="mb-1"><strong>Room Type:</strong> <?php echo htmlspecialchars($room_type['type_name']); ?></p>
                                <p class="mb-1"><strong>Max Guests:</strong> <?php echo $room_type['max_guests']; ?> person(s)</p>
                                <p class="mb-1"><strong>Rate per night:</strong> ₱<?php echo number_format($room_type['nightly_rate'], 2); ?></p>
                                <p class="mb-1">
                                    <strong>Available:</strong> 
                                    <span class="badge bg-<?php echo $availability['available_count'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $availability['available_count']; ?> room(s)
                                    </span>
                                </p>
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
                            <strong>Sorry!</strong> No rooms available for selected dates.
                        </div>
                        <div class="text-center">
                            <a href="rooms.php" class="btn btn-primary">Try Different Dates</a>
                        </div>
                    <?php else: ?>
                        <!-- Billing Summary -->
                        <div class="card mb-4" style="background-color: var(--light-cream);">
                            <div class="card-body">
                                <h5 class="card-title" style="color: var(--secondary-dark);">Billing Summary</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <td>Check-in:</td>
                                        <td class="text-end"><strong><?php echo date('M d, Y h:i A', strtotime($check_in . ' ' . $check_in_time)); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Check-out:</td>
                                        <td class="text-end"><strong><?php echo date('M d, Y h:i A', strtotime($check_out . ' ' . $check_out_time)); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Nights:</td>
                                        <td class="text-end"><strong><?php echo $nights; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Room Charge:</td>
                                        <td class="text-end">₱<?php echo number_format($room_charge, 2); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tax (12%):</td>
                                        <td class="text-end">₱<?php echo number_format($tax_amount, 2); ?></td>
                                    </tr>
                                    <tr style="background-color: var(--warm-tan);">
                                        <td><strong>Total:</strong></td>
                                        <td class="text-end"><strong>₱<?php echo number_format($total_amount, 2); ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Booking Form -->
                        <form action="booking-handler.php" method="POST">
                            <input type="hidden" name="room_type_id" value="<?php echo $room_type['room_type_id']; ?>">
                            <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                            <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                            <input type="hidden" name="check_in_time" value="<?php echo htmlspecialchars($check_in_time); ?>">
                            <input type="hidden" name="check_out_time" value="<?php echo htmlspecialchars($check_out_time); ?>">
                            <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
                            <input type="hidden" name="room_charge" value="<?php echo $room_charge; ?>">
                            <input type="hidden" name="tax_amount" value="<?php echo $tax_amount; ?>">
                            <input type="hidden" name="is_guest_checkout" value="<?php echo $is_logged_in ? '0' : '1'; ?>">

                            <div class="row">
                                <div class="col-lg-6">
                                    <h5 style="color: var(--secondary-dark);" class="mb-3">
                                        <i class="bi bi-calendar2-check"></i> Booking Details
                                    </h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-people"></i> Number of Guests *</label>
                                        <input type="number" class="form-control" name="num_guests" 
                                               min="1" max="<?php echo $room_type['max_guests']; ?>" value="1" required>
                                        <small class="text-muted">Maximum: <?php echo $room_type['max_guests']; ?> guest(s)</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label"><i class="bi bi-credit-card"></i> Payment Status *</label>
                                        <select class="form-select" name="payment_status" required>
                                            <option value="Pending">Pay Later (Pending)</option>
                                            <option value="Paid">Pay Now (Paid)</option>
                                        </select>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> 
                                        <strong>Room:</strong> #<?php echo htmlspecialchars($available_room['room_number']); ?> 
                                        (Floor <?php echo htmlspecialchars($available_room['floor']); ?>)
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <?php if (!$is_logged_in): ?>
                                    <div class="guest-info-section">
                                        <h5 style="color: var(--secondary-dark);" class="mb-3">
                                            <i class="bi bi-person-fill"></i> Guest Information
                                        </h5>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">First Name *</label>
                                                <input type="text" class="form-control" name="guest_first_name" 
                                                       placeholder="John" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Last Name *</label>
                                                <input type="text" class="form-control" name="guest_last_name" 
                                                       placeholder="Doe" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Email *</label>
                                            <input type="email" class="form-control" name="guest_email" 
                                                   placeholder="john@example.com" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Phone *</label>
                                            <input type="tel" class="form-control" name="guest_phone" 
                                                   placeholder="+1 234 567 8900" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Address (Optional)</label>
                                            <textarea class="form-control" name="guest_address" rows="2" 
                                                      placeholder="Your address"></textarea>
                                        </div>

                                        <small class="text-muted">
                                            <i class="bi bi-shield-check"></i> Your information is secure and will only be used for this booking.
                                        </small>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-success">
                                        <i class="bi bi-person-check"></i>
                                        <strong>Logged in as:</strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                                        <br><small><?php echo htmlspecialchars($_SESSION['user_email']); ?></small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Confirm Booking - ₱<?php echo number_format($total_amount, 2); ?>
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