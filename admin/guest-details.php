<?php
require_once '../config/db.php';
$page_title = "Guest Details";
include 'includes/header.php';
include 'includes/sidebar.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: guests.php");
    exit();
}

$guest_id = intval($_GET['id']);

// Fetch guest details
$guest_stmt = $conn->prepare("SELECT * FROM guest WHERE guest_id = ?");
$guest_stmt->bindValue(1, $guest_id, SQLITE3_INTEGER);
$guest_result = $guest_stmt->execute();
$guest = $guest_result->fetchArray(SQLITE3_ASSOC);

if (!$guest) {
    header("Location: guests.php");
    exit();
}

// Get guest statistics
$total_reservations = $conn->querySingle("SELECT COUNT(*) FROM reservation WHERE guest_id = $guest_id");
$active_reservations = $conn->querySingle("SELECT COUNT(*) FROM reservation WHERE guest_id = $guest_id AND status = 'Confirmed'");
$completed_reservations = $conn->querySingle("SELECT COUNT(*) FROM reservation WHERE guest_id = $guest_id AND status = 'Completed'");
$cancelled_reservations = $conn->querySingle("SELECT COUNT(*) FROM reservation WHERE guest_id = $guest_id AND status = 'Cancelled'");

$total_spent = $conn->querySingle("
    SELECT SUM(b.total_amount) 
    FROM billing b 
    INNER JOIN reservation r ON b.reservation_id = r.reservation_id 
    WHERE r.guest_id = $guest_id AND b.payment_status = 'Paid'
");
$total_spent = $total_spent ? $total_spent : 0;

// Fetch all reservations for this guest
$reservations_query = "SELECT r.*, 
                              rm.room_number, rm.floor,
                              rt.type_name,
                              b.payment_status, b.total_amount as bill_amount
                       FROM reservation r
                       INNER JOIN room rm ON r.room_id = rm.room_id
                       INNER JOIN room_type rt ON rm.room_type_id = rt.room_type_id
                       LEFT JOIN billing b ON r.reservation_id = b.reservation_id
                       WHERE r.guest_id = ?
                       ORDER BY r.booking_date DESC";

$res_stmt = $conn->prepare($reservations_query);
$res_stmt->bindValue(1, $guest_id, SQLITE3_INTEGER);
$res_result = $res_stmt->execute();
$reservations = [];
while ($row = $res_result->fetchArray(SQLITE3_ASSOC)) {
    $reservations[] = $row;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="color: var(--secondary-dark);">
            <i class="bi bi-person-circle"></i> Guest Profile
        </h2>
        <a href="guests.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Guests List
        </a>
    </div>

    <div class="row">
        <!-- Guest Information Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-person-circle" style="font-size: 6rem; color: var(--accent-brown);"></i>
                    </div>
                    <h4 style="color: var(--secondary-dark);">
                        <?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?>
                    </h4>
                    <p class="text-muted mb-1">
                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($guest['email']); ?>
                    </p>
                    <p class="text-muted mb-3">
                        <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($guest['phone']); ?>
                    </p>
                    
                    <?php if (!empty($guest['address'])): ?>
                    <p class="text-muted small">
                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($guest['address']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <small class="text-muted">
                        <i class="bi bi-calendar-check"></i> Member since<br>
                        <?php echo date('F d, Y', strtotime($guest['created_date'])); ?>
                    </small>
                    
                    <p class="mb-0 mt-2 small text-muted">
                        Guest ID: #<?php echo str_pad($guest_id, 4, '0', STR_PAD_LEFT); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="col-md-8 mb-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1">Total Reservations</h6>
                                    <h2 class="mb-0"><?php echo $total_reservations; ?></h2>
                                </div>
                                <i class="bi bi-calendar-check" style="font-size: 3rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1">Active Bookings</h6>
                                    <h2 class="mb-0"><?php echo $active_reservations; ?></h2>
                                </div>
                                <i class="bi bi-clock" style="font-size: 3rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1">Completed Stays</h6>
                                    <h2 class="mb-0"><?php echo $completed_reservations; ?></h2>
                                </div>
                                <i class="bi bi-check-circle" style="font-size: 3rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1">Total Spent</h6>
                                    <h2 class="mb-0">$<?php echo number_format($total_spent, 2); ?></h2>
                                </div>
                                <i class="bi bi-cash-stack" style="font-size: 3rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($cancelled_reservations > 0): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong><?php echo $cancelled_reservations; ?></strong> cancelled reservation(s)
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reservation History -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Reservation History</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($reservations)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Reservation ID</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Amount</th>
                            <th>Booked On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $res): ?>
                        <tr>
                            <td><strong>#<?php echo str_pad($res['reservation_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo htmlspecialchars($res['type_name']); ?>
                                </span><br>
                                <small class="text-muted">Room <?php echo htmlspecialchars($res['room_number']); ?></small>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($res['check_in_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($res['check_out_date'])); ?></td>
                            <td>
                                <?php
                                $status_badge = '';
                                switch($res['status']) {
                                    case 'Confirmed': $status_badge = 'bg-success'; break;
                                    case 'Completed': $status_badge = 'bg-primary'; break;
                                    case 'Cancelled': $status_badge = 'bg-danger'; break;
                                }
                                ?>
                                <span class="badge <?php echo $status_badge; ?>"><?php echo $res['status']; ?></span>
                            </td>
                            <td>
                                <?php
                                $pay_badge = '';
                                switch($res['payment_status']) {
                                    case 'Paid': $pay_badge = 'bg-success'; break;
                                    case 'Pending': $pay_badge = 'bg-warning'; break;
                                    case 'Cancelled': $pay_badge = 'bg-danger'; break;
                                }
                                ?>
                                <span class="badge <?php echo $pay_badge; ?>"><?php echo $res['payment_status']; ?></span>
                            </td>
                            <td><strong>$<?php echo number_format($res['total_amount'], 2); ?></strong></td>
                            <td><?php echo date('M d, Y', strtotime($res['booking_date'])); ?></td>
                            <td>
                                <a href="view-reservation.php?id=<?php echo $res['reservation_id']; ?>" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: var(--warm-tan);"></i>
                <p class="text-muted mt-3">No reservations found for this guest.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

    </div><!-- #content -->
</div><!-- .wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>