<?php
require_once '../config/db.php';
$page_title = "Billing Management";
include 'includes/header.php';
include 'includes/sidebar.php';

// Fetch all billing records with reservation and guest details
$query = "SELECT b.*, r.reservation_id, r.check_in_date, r.check_out_date, r.status as reservation_status,
                 g.first_name, g.last_name, g.email,
                 rm.room_number, rt.type_name
          FROM billing b
          INNER JOIN reservation r ON b.reservation_id = r.reservation_id
          INNER JOIN guest g ON r.guest_id = g.guest_id
          INNER JOIN room rm ON r.room_id = rm.room_id
          INNER JOIN room_type rt ON rm.room_type_id = rt.room_type_id
          ORDER BY b.bill_date DESC";

$result = $conn->query($query);
$billings = db_fetch_all($result);

// Calculate totals
$total_revenue = 0;
$total_pending = 0;
$total_paid = 0;

foreach ($billings as $bill) {
    if ($bill['payment_status'] === 'Paid') {
        $total_paid += $bill['total_amount'];
    } elseif ($bill['payment_status'] === 'Pending') {
        $total_pending += $bill['total_amount'];
    }
    $total_revenue += $bill['total_amount'];
}
?>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php
        $success = $_SESSION['success'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['success'], $_SESSION['error']);
        
        if (!empty($success)): ?>
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="text-uppercase">Total Revenue</h6>
                    <h2 class="mb-0">$<?php echo number_format($total_revenue, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h6 class="text-uppercase">Paid</h6>
                    <h2 class="mb-0">$<?php echo number_format($total_paid, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h6 class="text-uppercase">Pending</h6>
                    <h2 class="mb-0">$<?php echo number_format($total_pending, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing Records -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-receipt"></i> All Billing Records</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Bill ID</th>
                            <th>Guest Name</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Room Charge</th>
                            <th>Tax</th>
                            <th>Total Amount</th>
                            <th>Payment Status</th>
                            <th>Bill Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($billings)): ?>
                            <?php foreach ($billings as $bill): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($bill['bill_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($bill['first_name'] . ' ' . $bill['last_name']); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo htmlspecialchars($bill['type_name'] . ' - ' . $bill['room_number']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($bill['check_in_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($bill['check_out_date'])); ?></td>
                                <td>$<?php echo number_format($bill['room_charge'], 2); ?></td>
                                <td>$<?php echo number_format($bill['tax_amount'], 2); ?></td>
                                <td><strong>$<?php echo number_format($bill['total_amount'], 2); ?></strong></td>
                                <td>
                                    <?php if ($bill['payment_status'] === 'Paid'): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php elseif ($bill['payment_status'] === 'Pending'): ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($bill['bill_date'])); ?></td>
                                <td>
                                    <?php if ($bill['payment_status'] === 'Pending'): ?>
                                    <a href="update-payment.php?bill_id=<?php echo $bill['bill_id']; ?>&status=Paid" 
                                       class="btn btn-sm btn-success"
                                       onclick="return confirm('Mark this bill as Paid?')">
                                        <i class="bi bi-check-circle"></i> Mark Paid
                                    </a>
                                    <?php endif; ?>
                                    <a href="view-bill.php?bill_id=<?php echo $bill['bill_id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center">No billing records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($billings)): ?>
            <div class="mt-3">
                <p class="text-muted">
                    <i class="bi bi-info-circle"></i> 
                    Total Records: <strong><?php echo count($billings); ?></strong>
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