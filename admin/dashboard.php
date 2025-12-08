<?php
require_once '../config/db.php';
$page_title = "Dashboard";
include 'includes/header.php';
include 'includes/sidebar.php';

// Get statistics
$total_reservations = $conn->querySingle("SELECT COUNT(*) FROM reservation");
$available_rooms = $conn->querySingle("SELECT COUNT(*) FROM room WHERE status = 'Available'");
$occupied_rooms = $conn->querySingle("SELECT COUNT(*) FROM room WHERE status = 'Occupied'");
$maintenance_rooms = $conn->querySingle("SELECT COUNT(*) FROM room WHERE status = 'Maintenance'");
$total_guests = $conn->querySingle("SELECT COUNT(*) FROM guest");

// Calculate total revenue
$total_revenue = $conn->querySingle("SELECT SUM(total_amount) FROM billing WHERE payment_status = 'Paid'");
$total_revenue = $total_revenue ? $total_revenue : 0;

// Get recent reservations
$recent_query = "SELECT r.*, 
                        g.first_name, g.last_name,
                        rm.room_number,
                        rt.type_name
                 FROM reservation r
                 INNER JOIN guest g ON r.guest_id = g.guest_id
                 INNER JOIN room rm ON r.room_id = rm.room_id
                 INNER JOIN room_type rt ON rm.room_type_id = rt.room_type_id
                 ORDER BY r.booking_date DESC
                 LIMIT 5";
$recent_result = $conn->query($recent_query);
$recent_reservations = db_fetch_all($recent_result);
?>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Reservations</h6>
                            <h2 class="mb-0"><?php echo $total_reservations; ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-calendar-check" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Available Rooms</h6>
                            <h2 class="mb-0"><?php echo $available_rooms; ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-house-check" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Occupied Rooms</h6>
                            <h2 class="mb-0"><?php echo $occupied_rooms; ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-house-fill" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Revenue</h6>
                            <h2 class="mb-0">$<?php echo number_format($total_revenue, 2); ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-cash-stack" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-people text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-3"><?php echo $total_guests; ?></h3>
                    <p class="text-muted mb-0">Total Guests</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-tools text-secondary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-3"><?php echo $maintenance_rooms; ?></h3>
                    <p class="text-muted mb-0">Maintenance</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-door-open text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-3"><?php echo $available_rooms + $occupied_rooms + $maintenance_rooms; ?></h3>
                    <p class="text-muted mb-0">Total Rooms</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reservations -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list"></i> Recent Reservations</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Reservation ID</th>
                            <th>Guest Name</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Booked On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_reservations)): ?>
                            <?php foreach ($recent_reservations as $res): ?>
                            <tr>
                                <td>#<?php echo str_pad($res['reservation_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($res['first_name'] . ' ' . $res['last_name']); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo htmlspecialchars($res['type_name'] . ' - ' . $res['room_number']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($res['check_in_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($res['check_out_date'])); ?></td>
                                <td>
                                    <?php
                                    $badge_class = '';
                                    switch($res['status']) {
                                        case 'Confirmed': $badge_class = 'bg-success'; break;
                                        case 'Completed': $badge_class = 'bg-primary'; break;
                                        case 'Cancelled': $badge_class = 'bg-danger'; break;
                                        default: $badge_class = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo $res['status']; ?></span>
                                </td>
                                <td><strong>$<?php echo number_format($res['total_amount'], 2); ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($res['booking_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No reservations yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-3">
                <a href="reservations.php" class="btn btn-primary">View All Reservations</a>
            </div>
        </div>
    </div>
</div>

    </div><!-- #content -->
</div><!-- .wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>