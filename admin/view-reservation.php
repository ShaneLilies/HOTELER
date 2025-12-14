<?php
require_once '../config/db.php';
$page_title = "View Reservation";
include 'includes/header.php';
include 'includes/sidebar.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: reservations.php");
    exit();
}

$reservation_id = intval($_GET['id']);

// Fetch complete reservation details
$query = "SELECT r.*, 
                 g.first_name, g.last_name, g.email, g.phone, g.address,
                 rm.room_number, rm.floor, rm.status as room_status,
                 rt.type_name, rt.nightly_rate, rt.max_guests,
                 b.bill_id, b.room_charge, b.tax_amount, b.total_amount as bill_total, 
                 b.payment_status, b.bill_date
          FROM reservation r
          INNER JOIN guest g ON r.guest_id = g.guest_id
          INNER JOIN room rm ON r.room_id = rm.room_id
          INNER JOIN room_type rt ON rm.room_type_id = rt.room_type_id
          LEFT JOIN billing b ON r.reservation_id = b.reservation_id
          WHERE r.reservation_id = ?";

$stmt = $conn->prepare($query);
$stmt->bindValue(1, $reservation_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$reservation = $result->fetchArray(SQLITE3_ASSOC);

if (!$reservation) {
    header("Location: reservations.php");
    exit();
}

$nights = (strtotime($reservation['check_out_date']) - strtotime($reservation['check_in_date'])) / (60 * 60 * 24);
?>

<style>
.timestamp-highlight {
    background: linear-gradient(135deg, var(--light-cream), var(--warm-tan));
    padding: 15px 20px;
    border-radius: 10px;
    border-left: 4px solid var(--accent-brown);
    margin-bottom: 20px;
}

.timestamp-highlight strong {
    color: var(--secondary-dark);
}

.timestamp-highlight .time-value {
    color: var(--accent-brown);
    font-size: 1.1rem;
    font-weight: 600;
}
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="color: var(--secondary-dark);">
            <i class="bi bi-file-text"></i> Reservation Details
        </h2>
        <a href="reservations.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <!-- TIMESTAMP HIGHLIGHT -->
    <div class="timestamp-highlight">
        <div class="row align-items-center">
            <div class="col-md-8">
                <i class="bi bi-clock-history" style="font-size: 1.5rem; color: var(--accent-brown);"></i>
                <strong>Booking Timestamp:</strong>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="time-value">
                    <?php 
                    $booking_time = strtotime($reservation['booking_date']);
                    echo date('F d, Y', $booking_time) . ' at ' . date('h:i:s A', $booking_time); 
                    ?>
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Reservation Info -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Reservation Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Reservation ID:</strong></td>
                            <td>#<?php echo str_pad($reservation['reservation_id'], 6, '0', STR_PAD_LEFT); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <?php
                                $badge_class = '';
                                switch($reservation['status']) {
                                    case 'Confirmed': $badge_class = 'bg-success'; break;
                                    case 'Completed': $badge_class = 'bg-primary'; break;
                                    case 'Cancelled': $badge_class = 'bg-danger'; break;
                                }
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo $reservation['status']; ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Booking Date:</strong></td>
                            <td><?php echo date('M d, Y', $booking_time); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Booking Time:</strong></td>
                            <td><?php echo date('h:i:s A', $booking_time); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Check-in:</strong></td>
                            <td><?php echo date('l, F d, Y h:i A', strtotime($reservation['check_in_date'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Check-out:</strong></td>
                            <td><?php echo date('l, F d, Y h:i A', strtotime($reservation['check_out_date'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Duration:</strong></td>
                            <td><?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Number of Guests:</strong></td>
                            <td><?php echo $reservation['num_guests']; ?></td>
                        </tr>
                    </table>
                    
                    <?php if ($reservation['status'] === 'Confirmed'): ?>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="update-reservation.php?id=<?php echo $reservation_id; ?>&status=Completed" 
                           class="btn btn-success"
                           onclick="return confirm('Mark this reservation as Completed?')">
                            <i class="bi bi-check-circle"></i> Mark as Completed
                        </a>
                        <a href="update-reservation.php?id=<?php echo $reservation_id; ?>&status=Cancelled" 
                           class="btn btn-danger"
                           onclick="return confirm('Cancel this reservation?')">
                            <i class="bi bi-x-circle"></i> Cancel Reservation
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Guest Info -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Guest Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Guest ID:</strong></td>
                            <td>#<?php echo str_pad($reservation['guest_id'], 4, '0', STR_PAD_LEFT); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Full Name:</strong></td>
                            <td><?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td><?php echo htmlspecialchars($reservation['email']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td><?php echo htmlspecialchars($reservation['phone']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Address:</strong></td>
                            <td><?php echo !empty($reservation['address']) ? htmlspecialchars($reservation['address']) : '<em class="text-muted">Not provided</em>'; ?></td>
                        </tr>
                    </table>
                    <a href="guest-details.php?id=<?php echo $reservation['guest_id']; ?>" class="btn btn-outline-info">
                        <i class="bi bi-eye"></i> View Guest Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Room Info -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-door-open"></i> Room Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Room Type:</strong></td>
                            <td><?php echo htmlspecialchars($reservation['type_name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Room Number:</strong></td>
                            <td><?php echo htmlspecialchars($reservation['room_number']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Floor:</strong></td>
                            <td><?php echo htmlspecialchars($reservation['floor']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Room Status:</strong></td>
                            <td><span class="badge bg-secondary"><?php echo $reservation['room_status']; ?></span></td>
                        </tr>
                        <tr>
                            <td><strong>Nightly Rate:</strong></td>
                            <td>₱<?php echo number_format($reservation['nightly_rate'], 2); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Max Guests:</strong></td>
                            <td><?php echo $reservation['max_guests']; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Billing Info -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Billing Information</h5>
                </div>
                <div class="card-body">
                    <?php if ($reservation['bill_id']): ?>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Bill ID:</strong></td>
                            <td>#<?php echo str_pad($reservation['bill_id'], 6, '0', STR_PAD_LEFT); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Bill Date:</strong></td>
                            <td><?php echo date('M d, Y', strtotime($reservation['bill_date'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Room Charge:</strong></td>
                            <td>₱<?php echo number_format($reservation['room_charge'], 2); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tax (12%):</strong></td>
                            <td>₱<?php echo number_format($reservation['tax_amount'], 2); ?></td>
                        </tr>
                        <tr class="table-success">
                            <td><strong>Total Amount:</strong></td>
                            <td><strong>₱<?php echo number_format($reservation['bill_total'], 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong>Payment Status:</strong></td>
                            <td>
                                <?php
                                $pay_badge = '';
                                switch($reservation['payment_status']) {
                                    case 'Paid': $pay_badge = 'bg-success'; break;
                                    case 'Pending': $pay_badge = 'bg-warning'; break;
                                    case 'Cancelled': $pay_badge = 'bg-danger'; break;
                                }
                                ?>
                                <span class="badge <?php echo $pay_badge; ?>"><?php echo $reservation['payment_status']; ?></span>
                            </td>
                        </tr>
                    </table>
                    
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="view-bill.php?bill_id=<?php echo $reservation['bill_id']; ?>" class="btn btn-outline-success">
                            <i class="bi bi-file-earmark-text"></i> View Full Bill
                        </a>
                        <?php if ($reservation['payment_status'] === 'Pending'): ?>
                        <a href="update-payment.php?bill_id=<?php echo $reservation['bill_id']; ?>&status=Paid" 
                           class="btn btn-success"
                           onclick="return confirm('Mark this bill as Paid?')">
                            <i class="bi bi-check-circle"></i> Mark as Paid
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">No billing information available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

    </div><!-- #content -->
</div><!-- .wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>