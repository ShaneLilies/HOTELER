<?php
require_once '../config/db.php';
$page_title = "Guest Management";
include 'includes/header.php';
include 'includes/sidebar.php';

// Fetch all guests with reservation count
$query = "SELECT g.*, 
                 COUNT(r.reservation_id) as total_reservations,
                 SUM(CASE WHEN r.status = 'Confirmed' THEN 1 ELSE 0 END) as active_reservations
          FROM guest g
          LEFT JOIN reservation r ON g.guest_id = r.guest_id
          GROUP BY g.guest_id
          ORDER BY g.created_date DESC";

$result = $conn->query($query);
$guests = db_fetch_all($result);
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-people"></i> All Registered Guests</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Guest ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Total Reservations</th>
                            <th>Active Bookings</th>
                            <th>Registered Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($guests)): ?>
                            <?php foreach ($guests as $guest): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($guest['guest_id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($guest['email']); ?></td>
                                <td><?php echo htmlspecialchars($guest['phone']); ?></td>
                                <td><?php echo !empty($guest['address']) ? htmlspecialchars($guest['address']) : '<em class="text-muted">N/A</em>'; ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?php echo $guest['total_reservations']; ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($guest['active_reservations'] > 0): ?>
                                        <span class="badge bg-success"><?php echo $guest['active_reservations']; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">0</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($guest['created_date'])); ?></td>
                                <td>
                                    <a href="guest-details.php?id=<?php echo $guest['guest_id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No guests registered yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($guests)): ?>
            <div class="mt-3">
                <p class="text-muted">
                    <i class="bi bi-info-circle"></i> 
                    Total Guests: <strong><?php echo count($guests); ?></strong>
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