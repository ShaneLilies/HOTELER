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

// Pending payments
$pending_amount = $conn->querySingle("SELECT SUM(total_amount) FROM billing WHERE payment_status = 'Pending'");
$pending_amount = $pending_amount ? $pending_amount : 0;

// Today's check-ins
$todays_checkins = $conn->querySingle("SELECT COUNT(*) FROM reservation WHERE check_in_date = DATE('now') AND status = 'Confirmed'");

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

<style>
:root {
    --primary-dark: #02000d;
    --secondary-dark: #07203f;
    --light-cream: #ebded4;
    --warm-tan: #d9aa90;
    --accent-brown: #a65e46;
}

.stat-card {
    border-radius: 15px;
    padding: 25px;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(166, 94, 70, 0.3);
}

.stat-card .icon-wrapper {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.stat-card .stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 10px 0;
}

.stat-card .stat-label {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.card-primary {
    background: linear-gradient(135deg, var(--accent-brown), var(--warm-tan));
    color: white;
}

.card-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.card-warning {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.card-info {
    background: linear-gradient(135deg, var(--secondary-dark), #0d6efd);
    color: white;
}

.dashboard-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    overflow: hidden;
}

.dashboard-card .card-header {
    background: linear-gradient(135deg, var(--accent-brown), var(--warm-tan));
    color: white;
    border: none;
    padding: 20px;
    font-weight: 600;
}

.quick-stat {
    background: var(--light-cream);
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.quick-stat:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(166, 94, 70, 0.2);
}

.quick-stat i {
    font-size: 2.5rem;
    color: var(--accent-brown);
    margin-bottom: 10px;
}

.quick-stat h3 {
    color: var(--secondary-dark);
    font-weight: 700;
    margin: 10px 0;
}

.quick-stat p {
    color: var(--primary-dark);
    margin: 0;
    font-size: 0.9rem;
}

.table-hover tbody tr:hover {
    background-color: var(--light-cream);
}

.badge-status {
    padding: 6px 12px;
    font-size: 0.85rem;
    font-weight: 600;
}
</style>

<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert" style="background: linear-gradient(135deg, var(--accent-brown), var(--warm-tan)); color: white; border: none; border-radius: 15px;">
                <h4 class="alert-heading mb-2">
                    <i class="bi bi-sun"></i> Good <?php echo date('A') == 'AM' ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening'); ?>, 
                    <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!
                </h4>
                <p class="mb-0">Welcome back to ZAID HOTEL Management System. Here's your overview for today.</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card card-primary">
                <div class="icon-wrapper" style="background: rgba(255,255,255,0.2);">
                    <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
                </div>
                <div class="stat-label">Total Reservations</div>
                <div class="stat-value"><?php echo $total_reservations; ?></div>
                <small style="opacity: 0.9;">All time bookings</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card card-success">
                <div class="icon-wrapper" style="background: rgba(255,255,255,0.2);">
                    <i class="bi bi-house-check" style="font-size: 2rem;"></i>
                </div>
                <div class="stat-label">Available Rooms</div>
                <div class="stat-value"><?php echo $available_rooms; ?></div>
                <small style="opacity: 0.9;">Ready for booking</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card card-warning">
                <div class="icon-wrapper" style="background: rgba(255,255,255,0.2);">
                    <i class="bi bi-house-fill" style="font-size: 2rem;"></i>
                </div>
                <div class="stat-label">Occupied Rooms</div>
                <div class="stat-value"><?php echo $occupied_rooms; ?></div>
                <small style="opacity: 0.9;">Currently in use</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card card-info">
                <div class="icon-wrapper" style="background: rgba(255,255,255,0.2);">
                    <i class="bi bi-cash-stack" style="font-size: 2rem;"></i>
                </div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">₱<?php echo number_format($total_revenue, 0); ?></div>
                <small style="opacity: 0.9;">Paid bookings</small>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="quick-stat">
                <i class="bi bi-people"></i>
                <h3><?php echo $total_guests; ?></h3>
                <p class="text-muted mb-0">Total Guests</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="quick-stat">
                <i class="bi bi-tools"></i>
                <h3><?php echo $maintenance_rooms; ?></h3>
                <p class="text-muted mb-0">Maintenance</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="quick-stat">
                <i class="bi bi-clock-history"></i>
                <h3>₱<?php echo number_format($pending_amount, 0); ?></h3>
                <p class="text-muted mb-0">Pending Payments</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="quick-stat">
                <i class="bi bi-calendar-event"></i>
                <h3><?php echo $todays_checkins; ?></h3>
                <p class="text-muted mb-0">Today's Check-ins</p>
            </div>
        </div>
    </div>

    <!-- Recent Reservations -->
    <div class="dashboard-card card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list"></i> Recent Reservations</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background-color: var(--light-cream); color: var(--secondary-dark);">
                        <tr>
                            <th>ID</th>
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
                                <td><strong>#<?php echo str_pad($res['reservation_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($res['first_name'] . ' ' . $res['last_name']); ?></td>
                                <td>
                                    <span class="badge" style="background-color: var(--accent-brown);">
                                        <?php echo htmlspecialchars($res['type_name'] . ' - ' . $res['room_number']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($res['check_in_date'])); ?><br>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($res['check_in_date'])); ?></small>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($res['check_out_date'])); ?><br>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($res['check_out_date'])); ?></small>
                                </td>
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
                                    <span class="badge badge-status <?php echo $badge_class; ?>"><?php echo $res['status']; ?></span>
                                </td>
                                <td><strong style="color: var(--accent-brown);">₱<?php echo number_format($res['total_amount'], 2); ?></strong></td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($res['booking_date'])); ?><br>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($res['booking_date'])); ?></small>
                                </td>
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
                <a href="reservations.php" class="btn" style="background: linear-gradient(135deg, var(--accent-brown), var(--warm-tan)); color: white;">
                    <i class="bi bi-arrow-right-circle"></i> View All Reservations
                </a>
            </div>
        </div>
    </div>
</div>

    </div><!-- #content -->
</div><!-- .wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>