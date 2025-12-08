<?php
require_once '../config/db.php';
$page_title = "Edit Room";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

// Get room ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: all-rooms.php");
    exit();
}

$room_id = intval($_GET['id']);

// Fetch room types
$room_types_query = "SELECT * FROM room_type ORDER BY type_name";
$room_types_result = $conn->query($room_types_query);
$room_types = db_fetch_all($room_types_result);

// Fetch room details
$stmt = $conn->prepare("SELECT * FROM room WHERE room_id = ?");
$stmt->bindValue(1, $room_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$room = $result->fetchArray(SQLITE3_ASSOC);

if (!$room) {
    header("Location: all-rooms.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_number = trim($_POST['room_number']);
    $room_type_id = intval($_POST['room_type_id']);
    $floor = trim($_POST['floor']);
    $status = $_POST['status'];
    
    if (empty($error)) {
        // Check if room number already exists (except current room)
        $check_stmt = $conn->prepare("SELECT room_id FROM room WHERE room_number = ? AND room_id != ?");
        $check_stmt->bindValue(1, $room_number, SQLITE3_TEXT);
        $check_stmt->bindValue(2, $room_id, SQLITE3_INTEGER);
        $check_result = $check_stmt->execute();
        
        if ($check_result->fetchArray()) {
            $error = "Room number already exists!";
        } else {
            // Update room using prepared statement
            $update_stmt = $conn->prepare("UPDATE room SET room_number = ?, room_type_id = ?, floor = ?, status = ? WHERE room_id = ?");
            $update_stmt->bindValue(1, $room_number, SQLITE3_TEXT);
            $update_stmt->bindValue(2, $room_type_id, SQLITE3_INTEGER);
            $update_stmt->bindValue(3, $floor, SQLITE3_TEXT);
            $update_stmt->bindValue(4, $status, SQLITE3_TEXT);
            $update_stmt->bindValue(5, $room_id, SQLITE3_INTEGER);
            
            if ($update_stmt->execute()) {
                $success = "Room updated successfully!";
                // Refresh room data
                $stmt = $conn->prepare("SELECT * FROM room WHERE room_id = ?");
                $stmt->bindValue(1, $room_id, SQLITE3_INTEGER);
                $result = $stmt->execute();
                $room = $result->fetchArray(SQLITE3_ASSOC);
            } else {
                $error = "Failed to update room!";
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Room</h5>
                </div>
                <div class="card-body">
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
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Room Number *</label>
                                <input type="text" class="form-control" name="room_number" 
                                       value="<?php echo htmlspecialchars($room['room_number']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Room Type *</label>
                                <select class="form-select" name="room_type_id" required>
                                    <?php foreach ($room_types as $type): ?>
                                        <option value="<?php echo $type['room_type_id']; ?>" 
                                                <?php echo $type['room_type_id'] == $room['room_type_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['type_name']); ?> - $<?php echo number_format($type['nightly_rate'], 2); ?>/night
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Floor *</label>
                                <input type="text" class="form-control" name="floor" 
                                       value="<?php echo htmlspecialchars($room['floor']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status *</label>
                                <select class="form-select" name="status" required>
                                    <option value="Available" <?php echo $room['status'] == 'Available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="Occupied" <?php echo $room['status'] == 'Occupied' ? 'selected' : ''; ?>>Occupied</option>
                                    <option value="Maintenance" <?php echo $room['status'] == 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                To manage room type images (thumbnails and carousel), go to <strong>All Rooms</strong> and click the <strong>blue "Manage Images"</strong> button.
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="all-rooms.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Room
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

    </div><!-- #content -->
</div><!-- .wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>