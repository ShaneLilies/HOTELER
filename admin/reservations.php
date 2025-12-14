<?php
require_once '../config/db.php';
$page_title = "All Reservations";
include 'includes/header.php';
include 'includes/sidebar.php';

// Fetch all reservations with guest and room details
$query = "SELECT r.*, 
                 g.first_name, g.last_name, g.email, g.phone,
                 rm.room_number, rm.floor,
                 rt.type_name, rt.nightly_rate
          FROM reservation r
          INNER JOIN guest g ON r.guest_id = g.guest_id
          INNER JOIN room rm ON r.room_id = rm.room_id
          INNER JOIN room_type rt ON rm.room_type_id = rt.room_type_id
          ORDER BY r.booking_date DESC";

$result = $conn->query($query);
$reservations = db_fetch_all($result);
?>

<style>
.timestamp-cell {
    font-size: 0.85rem;
    color: #666;
}

.timestamp-date {
    display: block;
    font-weight: 600;
    color: var(--secondary-dark);
}

.timestamp-time {
    display: block;
    color: var(--accent-brown);
}
</style>

<div class="container-fluid">
    <?php
    $success = $_SESSION['success'] ?? '';
    $error = $_SESSION['error'] ?? '';
    unset($_SESSION['success'], $_SESSION['error']);
    
    if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-calendar-check"></i> All Reservations</h5>
            <div>
                <span class="badge bg-success">Confirmed</span>
                <span class="badge bg-primary">Completed</span>
                <span class="badge bg-danger">Cancelled</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Reservation ID</th>
                            <th>Guest Name</th>
                            <th>Contact</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Guests</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th><i class="bi bi-clock-history"></i> Booked On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reservations)): ?>
                            <?php foreach ($reservations as $res): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($res['reservation_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($res['first_name'] . ' ' . $res['last_name']); ?></td>
                                <td>
                                    <small>
                                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($res['email']); ?><br>
                                        <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($res['phone']); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo htmlspecialchars($res['type_name']); ?>
                                    </span><br>
                                    <small>Room <?php echo htmlspecialchars($res['room_number']); ?> • Floor <?php echo htmlspecialchars($res['floor']); ?></small>
                                </td>
                                <td class="timestamp-cell">
                                    <span class="timestamp-date">
                                        <i class="bi bi-calendar3"></i> <?php echo date('M d, Y', strtotime($res['check_in_date'])); ?>
                                    </span>
                                    <span class="timestamp-time">
                                        <i class="bi bi-clock"></i> <?php echo date('h:i A', strtotime($res['check_in_date'])); ?>
                                    </span>
                                </td>
                                <td class="timestamp-cell">
                                    <span class="timestamp-date">
                                        <i class="bi bi-calendar3"></i> <?php echo date('M d, Y', strtotime($res['check_out_date'])); ?>
                                    </span>
                                    <span class="timestamp-time">
                                        <i class="bi bi-clock"></i> <?php echo date('h:i A', strtotime($res['check_out_date'])); ?>
                                    </span>
                                </td>
                                <td class="text-center"><?php echo $res['num_guests']; ?></td>
                                <td><strong>₱<?php echo number_format($res['total_amount'], 2); ?></strong></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch($res['status']) {
                                        case 'Confirmed':
                                            $status_class = 'bg-success';
                                            break;
                                        case 'Completed':
                                            $status_class = 'bg-primary';
                                            break;
                                        case 'Cancelled':
                                            $status_class = 'bg-danger';
                                            break;
                                        default:
                                            $status_class = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($res['status']); ?>
                                    </span>
                                </td>
                                <td class="timestamp-cell">
                                    <?php 
                                    $booking_time = strtotime($res['booking_date']);
                                    ?>
                                    <span class="timestamp-date">
                                        <i class="bi bi-calendar3"></i> <?php echo date('M d, Y', $booking_time); ?>
                                    </span>
                                    <span class="timestamp-time">
                                        <i class="bi bi-clock"></i> <?php echo date('h:i A', $booking_time); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <?php if ($res['status'] === 'Confirmed'): ?>
                                        <a href="update-reservation.php?id=<?php echo $res['reservation_id']; ?>&status=Completed" 
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('Mark as Completed?')"
                                           title="Mark as Completed">
                                            <i class="bi bi-check-circle"></i>
                                        </a>
                                        <a href="update-reservation.php?id=<?php echo $res['reservation_id']; ?>&status=Cancelled" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Cancel this reservation?')"
                                           title="Cancel Reservation">
                                            <i class="bi bi-x-circle"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="view-reservation.php?id=<?php echo $res['reservation_id']; ?>" 
                                           class="btn btn-sm btn-primary"
                                           title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center">No reservations found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($reservations)): ?>
            <div class="mt-3">
                <p class="text-muted">
                    <i class="bi bi-info-circle"></i> 
                    Total Reservations: <strong><?php echo count($reservations); ?></strong>
                    <span class="ms-3">
                        <i class="bi bi-clock-history"></i> Sorted by latest bookings first
                    </span>
                </p>
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