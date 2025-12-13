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

<style>
.stat-card-billing {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
}

.stat-card-billing:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.card-header {
    background: linear-gradient(135deg, var(--secondary-dark), var(--accent-brown));
    color: white;
    border: none;
    padding: 20px;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background-color: var(--secondary-dark);
    color: white;
    border: none;
    font-weight: 600;
    padding: 15px;
}

.table tbody td {
    vertical-align: middle;
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
}

.table tbody tr:hover {
    background-color: var(--light-cream);
}

.badge {
    padding: 8px 12px;
    font-weight: 600;
    border-radius: 8px;
}

.btn-action {
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.alert {
    border: none;
    border-radius: 10px;
    border-left: 4px solid;
}
</style>

<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php
        $success = $_SESSION['success'] ?? '';
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['success'], $_SESSION['error']);
        
        if (!empty($success)): ?>
        <div class="col-12 mb-3">
            <div class="alert alert-success alert-dismissible fade show" style="border-left-color: #28a745;">
                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
        <div class="col-12 mb-3">
            <div class="alert alert-danger alert-dismissible fade show" style="border-left-color: #dc3545;">
                <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="col-md-4">
            <div class="card stat-card-billing text-white" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Revenue</h6>
                            <h2 class="mb-0">₱<?php echo number_format($total_revenue, 2); ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-cash-stack" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card-billing text-white" style="background: linear-gradient(135deg, #007bff, #0056b3);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Paid</h6>
                            <h2 class="mb-0">₱<?php echo number_format($total_paid, 2); ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-check-circle" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card-billing text-white" style="background: linear-gradient(135deg, #ffc107, #ff9800);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Pending</h6>
                            <h2 class="mb-0">₱<?php echo number_format($total_pending, 2); ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-clock-history" style="font-size: 3rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing Records -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-receipt"></i> All Billing Records</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
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
                            <th>Bill Date & Time</th>
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
                                    <span class="badge" style="background-color: var(--accent-brown);">
                                        <?php echo htmlspecialchars($bill['type_name'] . ' - ' . $bill['room_number']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($bill['check_in_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($bill['check_out_date'])); ?></td>
                                <td>₱<?php echo number_format($bill['room_charge'], 2); ?></td>
                                <td>₱<?php echo number_format($bill['tax_amount'], 2); ?></td>
                                <td><strong>₱<?php echo number_format($bill['total_amount'], 2); ?></strong></td>
                                <td>
                                    <?php if ($bill['payment_status'] === 'Paid'): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php elseif ($bill['payment_status'] === 'Pending'): ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($bill['bill_date'])); ?><br>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($bill['bill_date'])); ?></small>
                                </td>
                                <td>
                                    <?php if ($bill['payment_status'] === 'Pending'): ?>
                                    <a href="update-payment.php?bill_id=<?php echo $bill['bill_id']; ?>&status=Paid" 
                                       class="btn btn-sm btn-success btn-action"
                                       onclick="return confirm('Mark this bill as Paid?')">
                                        <i class="bi bi-check-circle"></i> Mark Paid
                                    </a>
                                    <?php endif; ?>
                                    <a href="view-bill.php?bill_id=<?php echo $bill['bill_id']; ?>" 
                                       class="btn btn-sm btn-primary btn-action">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--warm-tan);"></i>
                                    <p class="text-muted mt-2">No billing records found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($billings)): ?>
            <div class="p-4 bg-light">
                <p class="text-muted mb-0">
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