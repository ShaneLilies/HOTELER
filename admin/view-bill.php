<?php
require_once '../config/db.php';
$page_title = "View Bill";
include 'includes/header.php';
include 'includes/sidebar.php';

if (!isset($_GET['bill_id']) || empty($_GET['bill_id'])) {
    header("Location: billing.php");
    exit();
}

$bill_id = intval($_GET['bill_id']);

// Fetch complete bill details
$query = "SELECT b.*, 
                 r.reservation_id, r.check_in_date, r.check_out_date, r.num_guests, r.status as reservation_status,
                 g.first_name, g.last_name, g.email, g.phone, g.address,
                 rm.room_number, rm.floor,
                 rt.type_name, rt.nightly_rate
          FROM billing b
          INNER JOIN reservation r ON b.reservation_id = r.reservation_id
          INNER JOIN guest g ON r.guest_id = g.guest_id
          INNER JOIN room rm ON r.room_id = rm.room_id
          INNER JOIN room_type rt ON rm.room_type_id = rt.room_type_id
          WHERE b.bill_id = ?";

$stmt = $conn->prepare($query);
$stmt->bindValue(1, $bill_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$bill = $result->fetchArray(SQLITE3_ASSOC);

if (!$bill) {
    header("Location: billing.php");
    exit();
}

$nights = (strtotime($bill['check_out_date']) - strtotime($bill['check_in_date'])) / (60 * 60 * 24);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="color: var(--secondary-dark);">
            <i class="bi bi-receipt"></i> Bill #<?php echo str_pad($bill_id, 6, '0', STR_PAD_LEFT); ?>
        </h2>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Bill
            </button>
            <a href="billing.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Billing
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm" id="invoice">
                <!-- Hotel Header -->
                <div class="card-header text-white text-center" style="background-color: var(--secondary-dark); padding: 30px;">
                    <h2 class="mb-0">ZAID HOTEL</h2>
                    <p class="mb-0">123 Luxury Avenue, Downtown City</p>
                    <p class="mb-0">Phone: +1 (555) 123-4567 | Email: info@zaidhotel.com</p>
                </div>

                <div class="card-body" style="padding: 40px;">
                    <!-- Bill Info Bar -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 style="color: var(--secondary-dark);">Bill Information</h5>
                            <p class="mb-1"><strong>Bill ID:</strong> #<?php echo str_pad($bill_id, 6, '0', STR_PAD_LEFT); ?></p>
                            <p class="mb-1"><strong>Bill Date:</strong> <?php echo date('F d, Y', strtotime($bill['bill_date'])); ?></p>
                            <p class="mb-1"><strong>Payment Status:</strong> 
                                <?php
                                $status_class = '';
                                switch($bill['payment_status']) {
                                    case 'Paid': $status_class = 'bg-success'; break;
                                    case 'Pending': $status_class = 'bg-warning'; break;
                                    case 'Cancelled': $status_class = 'bg-danger'; break;
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo $bill['payment_status']; ?></span>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h5 style="color: var(--secondary-dark);">Guest Information</h5>
                            <p class="mb-1"><strong><?php echo htmlspecialchars($bill['first_name'] . ' ' . $bill['last_name']); ?></strong></p>
                            <p class="mb-1"><?php echo htmlspecialchars($bill['email']); ?></p>
                            <p class="mb-1"><?php echo htmlspecialchars($bill['phone']); ?></p>
                            <?php if (!empty($bill['address'])): ?>
                            <p class="mb-1 small text-muted"><?php echo htmlspecialchars($bill['address']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <!-- Reservation Details -->
                    <h5 style="color: var(--secondary-dark);" class="mb-3">Reservation Details</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Reservation ID:</strong></td>
                                    <td>#<?php echo str_pad($bill['reservation_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Room Type:</strong></td>
                                    <td><?php echo htmlspecialchars($bill['type_name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Room Number:</strong></td>
                                    <td><?php echo htmlspecialchars($bill['room_number']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Floor:</strong></td>
                                    <td><?php echo htmlspecialchars($bill['floor']); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Check-in Date:</strong></td>
                                    <td><?php echo date('F d, Y h:i A', strtotime($bill['check_in_date'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Check-out Date:</strong></td>
                                    <td><?php echo date('F d, Y h:i A', strtotime($bill['check_out_date'])); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Number of Nights:</strong></td>
                                    <td><?php echo $nights; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Number of Guests:</strong></td>
                                    <td><?php echo $bill['num_guests']; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Billing Breakdown -->
                    <h5 style="color: var(--secondary-dark);" class="mb-3">Billing Breakdown</h5>
                    <table class="table table-bordered">
                        <thead style="background-color: var(--light-cream);">
                            <tr>
                                <th>Description</th>
                                <th class="text-center">Rate</th>
                                <th class="text-center">Nights</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo htmlspecialchars($bill['type_name']); ?> - Room <?php echo htmlspecialchars($bill['room_number']); ?></td>
                                <td class="text-center">₱<?php echo number_format($bill['nightly_rate'], 2); ?></td>
                                <td class="text-center"><?php echo $nights; ?></td>
                                <td class="text-end">₱<?php echo number_format($bill['room_charge'], 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end">₱<?php echo number_format($bill['room_charge'], 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Tax (12%):</strong></td>
                                <td class="text-end">₱<?php echo number_format($bill['tax_amount'], 2); ?></td>
                            </tr>
                            <tr style="background-color: var(--warm-tan); font-size: 1.1rem;">
                                <td colspan="3" class="text-end"><strong>TOTAL AMOUNT:</strong></td>
                                <td class="text-end"><strong>₱<?php echo number_format($bill['total_amount'], 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Payment Status Info -->
                    <div class="alert <?php echo $bill['payment_status'] === 'Paid' ? 'alert-success' : 'alert-warning'; ?> mt-4">
                        <strong>Payment Status: <?php echo $bill['payment_status']; ?></strong>
                        <?php if ($bill['payment_status'] === 'Paid'): ?>
                            <p class="mb-0 mt-2">Payment received. Thank you for your business!</p>
                        <?php elseif ($bill['payment_status'] === 'Pending'): ?>
                            <p class="mb-0 mt-2">Payment is pending. Please complete payment at the front desk.</p>
                        <?php else: ?>
                            <p class="mb-0 mt-2">This reservation has been cancelled.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Footer Note -->
                    <div class="text-center mt-4 pt-4" style="border-top: 2px solid var(--light-cream);">
                        <p class="text-muted small mb-1">Thank you for choosing ZAID HOTEL</p>
                        <p class="text-muted small mb-0">For any inquiries, please contact us at info@zaidhotel.com or call +1 (555) 123-4567</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons (Not printed) -->
            <?php if ($bill['payment_status'] === 'Pending'): ?>
            <div class="card mt-3 no-print">
                <div class="card-body">
                    <h5 class="card-title">Update Payment Status</h5>
                    <p>Mark this bill as paid when payment is received.</p>
                    <a href="update-payment.php?bill_id=<?php echo $bill_id; ?>&status=Paid" 
                       class="btn btn-success"
                       onclick="return confirm('Mark this bill as Paid?')">
                        <i class="bi bi-check-circle"></i> Mark as Paid
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    #sidebar, .navbar-top, .card-header button, .btn {
        display: none !important;
    }
    
    #content {
        padding: 0 !important;
    }
    
    .card {
        box-shadow: none !important;
        border: none !important;
    }
}
</style>

    </div><!-- #content -->
</div><!-- .wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>