<?php
require_once '../config/db.php';
require_once '../config/settings.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$page_title = "My Profile - ZAID HOTEL";
include 'includes/header.php';
include 'includes/navbar.php';

$guest_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM guest WHERE guest_id = ?");
$stmt->bindValue(1, $guest_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if (empty($first_name) || empty($last_name) || empty($phone)) {
        $error = "First name, last name, and phone are required!";
    } else {
        $update_stmt = $conn->prepare("UPDATE guest SET first_name = ?, last_name = ?, phone = ?, address = ? WHERE guest_id = ?");
        $update_stmt->bindValue(1, $first_name, SQLITE3_TEXT);
        $update_stmt->bindValue(2, $last_name, SQLITE3_TEXT);
        $update_stmt->bindValue(3, $phone, SQLITE3_TEXT);
        $update_stmt->bindValue(4, $address, SQLITE3_TEXT);
        $update_stmt->bindValue(5, $guest_id, SQLITE3_INTEGER);
        
        if ($update_stmt->execute()) {
            $success = "Profile updated successfully!";
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM guest WHERE guest_id = ?");
            $stmt->bindValue(1, $guest_id, SQLITE3_INTEGER);
            $result = $stmt->execute();
            $user = $result->fetchArray(SQLITE3_ASSOC);
        } else {
            $error = "Failed to update profile!";
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required!";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect!";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters!";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $pwd_stmt = $conn->prepare("UPDATE guest SET password = ? WHERE guest_id = ?");
        $pwd_stmt->bindValue(1, $hashed_password, SQLITE3_TEXT);
        $pwd_stmt->bindValue(2, $guest_id, SQLITE3_INTEGER);
        
        if ($pwd_stmt->execute()) {
            $success = "Password changed successfully!";
        } else {
            $error = "Failed to change password!";
        }
    }
}

// Get user statistics - FIXED: Specify table alias for total_amount
$total_reservations = $conn->querySingle("SELECT COUNT(*) FROM reservation WHERE guest_id = $guest_id");
$total_spent = $conn->querySingle("
    SELECT SUM(b.total_amount) 
    FROM billing b 
    INNER JOIN reservation r ON b.reservation_id = r.reservation_id 
    WHERE r.guest_id = $guest_id AND b.payment_status = 'Paid'
");
$total_spent = $total_spent ? $total_spent : 0;
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <!-- Profile Card -->
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-person-circle" style="font-size: 6rem; color: var(--accent-brown);"></i>
                    </div>
                    <h4 style="color: var(--secondary-dark);">
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                    </h4>
                    <p class="text-muted mb-3">
                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 style="color: var(--accent-brown);"><?php echo $total_reservations; ?></h5>
                            <small class="text-muted">Total Bookings</small>
                        </div>
                        <div class="col-6">
                            <h5 style="color: var(--accent-brown);"><?php echo format_currency($total_spent); ?></h5>
                            <small class="text-muted">Total Spent</small>
                        </div>
                    </div>
                    <hr>
                    <small class="text-muted">
                        <i class="bi bi-calendar-check"></i> Member since 
                        <?php echo date('M Y', strtotime($user['created_date'])); ?>
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Edit Profile -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: var(--secondary-dark); color: var(--light-cream);">
                    <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name *</label>
                                <input type="text" class="form-control" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: var(--secondary-dark); color: var(--light-cream);">
                    <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password *</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">New Password *</label>
                            <input type="password" class="form-control" name="new_password" required>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="bi bi-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>