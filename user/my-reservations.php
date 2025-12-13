<?php
require_once '../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "My Reservations - ZAID HOTEL";
include 'includes/header.php';
include 'includes/navbar.php';

$guest_id = $_SESSION['user_id'];

// Fetch all reservations for this guest
$query = "SELECT r.*, 
                 rm.room_number, rm.floor,
                 rt.type_name, rt.nightly_rate,
                 b.payment_status, b.total_amount as bill_amount
          FROM reservation r
          INNER JOIN room rm ON r.room_id = rm.room_id
          INNER JOIN room_type rt ON rm.room_type_id = rt.room_type_id
          LEFT JOIN billing b ON r.reservation_id = b.reservation_id
          WHERE r.guest_id = ?
          ORDER BY r.booking_date DESC";

$stmt = $conn->prepare($query);
$stmt->bindValue(1, $guest_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$reservations = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $reservations[] = $row;
}
?>

<style>
.timestamp-badge {
    background: var(--light-cream);
    color: var(--secondary-dark);
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
</style>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4" style="color: var(--secondary-dark);">
                <i class="bi bi-calendar-check"></i> My Reservations
            </h1>
            
            <?php if (empty($reservations)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: var(--warm-tan);"></i>
                        <h3 class="mt-3" style="color: var(--secondary-dark);">No Reservations Yet</h3>
                        <p class="text-muted">You haven't made any reservations at ZAID HOTEL.</p>
                        <a href="rooms.php" class="btn btn-primary mt-3">Browse Available Rooms</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($reservations as $reservation): ?>
                        <?php
                        $status_color = '';
                        $status_icon = '';
                        switch($reservation['status']) {
                            case 'Confirmed':
                                $status_color = 'success';
                                $status_icon = 'check-circle';
                                break;
                            case 'Completed':
                                $status_color = 'primary';
                                $status_icon = 'check-all';
                                break;
                            case 'Cancelled':
                                $status_color = 'danger';
                                $status_icon = 'x-circle';
                                break;
                        }
                        
                        $payment_color = '';
                        switch($reservation['payment_status']) {
                            case 'Paid':
                                $payment_color = 'success';
                                break;
                            case 'Pending':
                                $payment_color = 'warning';
                                break;
                            case 'Cancelled':
                                $payment_color = 'danger';
                                break;
                        }
                        ?>
                        
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header d-flex justify-content-between align-items-center" 
                                     style="background-color: var(--secondary-dark); color: var(--light-cream);">
                                    <span><strong>Reservation #<?php echo str_pad($reservation['reservation_id'], 6, '0', STR_PAD_LEFT); ?></strong></span>
                                    <span class="badge bg-<?php echo $status_color; ?>">
                                        <i class="bi bi-<?php echo $status_icon; ?>"></i> <?php echo $reservation['status']; ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title" style="color: var(--accent-brown);">
                                        <?php echo htmlspecialchars($reservation['type_name']); ?>
                                    </h5>
                                    
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <p class="mb-2">
                                                <i class="bi bi-door-open" style="color: var(--accent-brown);"></i>
                                                <strong>Room:</strong> <?php echo htmlspecialchars($reservation['room_number']); ?>
                                            </p>
                                            <p class="mb-2">
                                                <i class="bi bi-building" style="color: var(--accent-brown);"></i>
                                                <strong>Floor:</strong> <?php echo htmlspecialchars($reservation['floor']); ?>
                                            </p>
                                            <p class="mb-2">
                                                <i class="bi bi-people" style="color: var(--accent-brown);"></i>
                                                <strong>Guests:</strong> <?php echo $reservation['num_guests']; ?>
                                            </p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-2">
                                                <i class="bi bi-calendar-event" style="color: var(--accent-brown);"></i>
                                                <strong>Check-in:</strong><br>
                                                <?php echo date('M d, Y', strtotime($reservation['check_in_date'])); ?>
                                            </p>
                                            <p class="mb-2">
                                                <i class="bi bi-calendar-x" style="color: var(--accent-brown);"></i>
                                                <strong>Check-out:</strong><br>
                                                <?php echo date('M d, Y', strtotime($reservation['check_out_date'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <strong style="color: var(--secondary-dark);">Total Amount:</strong>
                                            <h4 style="color: var(--accent-brown);" class="mb-0">
                                                $<?php echo number_format($reservation['total_amount'], 2); ?>
                                            </h4>
                                        </div>
                                        <div>
                                            <span class="badge bg-<?php echo $payment_color; ?>" style="font-size: 0.9rem;">
                                                <?php echo $reservation['payment_status']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- TIMESTAMP -->
                                    <div class="mb-3">
                                        <span class="timestamp-badge">
                                            <i class="bi bi-clock-history"></i>
                                            <strong>Booked:</strong> 
                                            <?php 
                                            $booking_time = strtotime($reservation['booking_date']);
                                            echo date('M d, Y', $booking_time) . ' at ' . date('h:i A', $booking_time); 
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <?php if ($reservation['status'] === 'Confirmed' && $reservation['payment_status'] === 'Pending'): ?>
                                            <a href="cancel-reservation.php?id=<?php echo $reservation['reservation_id']; ?>" 
                                               class="btn btn-outline-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                                <i class="bi bi-x-circle"></i> Cancel Reservation
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>