<?php
require_once '../config/db.php';
$page_title = "Add Room";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

// Fetch room types
$room_types_query = "SELECT * FROM room_type ORDER BY type_name";
$room_types_result = $conn->query($room_types_query);
$room_types = db_fetch_all($room_types_result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_number = trim($_POST['room_number']);
    $room_type_id = intval($_POST['room_type_id']);
    $floor = trim($_POST['floor']);
    $status = $_POST['status'];
    
    // Check if room number already exists
    $check_stmt = $conn->prepare("SELECT room_id FROM room WHERE room_number = ?");
    $check_stmt->bindValue(1, $room_number, SQLITE3_TEXT);
    $check_result = $check_stmt->execute();
    
    if ($check_result->fetchArray()) {
        $error = "Room number already exists!";
    } else {
        // Insert room using prepared statement (removed image column)
        $stmt = $conn->prepare("INSERT INTO room (room_number, room_type_id, floor, status) VALUES (?, ?, ?, ?)");
        $stmt->bindValue(1, $room_number, SQLITE3_TEXT);
        $stmt->bindValue(2, $room_type_id, SQLITE3_INTEGER);
        $stmt->bindValue(3, $floor, SQLITE3_TEXT);
        $stmt->bindValue(4, $status, SQLITE3_TEXT);
        
        if ($stmt->execute()) {
            $success = "Room added successfully!";
            // Clear form
            $_POST = array();
        } else {
            $error = "Failed to add room!";
        }
    }
}
?>

<style>
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, var(--secondary-dark), var(--accent-brown));
    color: white;
    border: none;
    padding: 25px;
}

.card-body {
    padding: 30px;
}

.form-label {
    color: var(--secondary-dark);
    font-weight: 600;
    margin-bottom: 8px;
}

.form-control, .form-select {
    border: 2px solid var(--light-cream);
    border-radius: 10px;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--accent-brown);
    box-shadow: 0 0 0 0.2rem rgba(166, 94, 70, 0.25);
}

.btn {
    padding: 12px 25px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, var(--accent-brown), var(--warm-tan));
    border: none;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(166, 94, 70, 0.4);
}

.btn-secondary {
    background-color: #6c757d;
    border: none;
}

.btn-secondary:hover {
    background-color: #5a6268;
    transform: translateY(-2px);
}

.alert {
    border: none;
    border-radius: 10px;
    border-left: 4px solid;
    padding: 15px 20px;
}

.alert-success {
    background-color: rgba(40, 167, 69, 0.1);
    border-left-color: #28a745;
    color: #155724;
}

.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    border-left-color: #dc3545;
    color: #721c24;
}

.alert-info {
    background-color: rgba(7, 32, 63, 0.1);
    border-left-color: var(--secondary-dark);
    color: var(--secondary-dark);
}

.form-text {
    color: #6c757d;
    font-size: 0.875rem;
}

.room-type-option {
    padding: 5px 0;
}
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Add New Room</h5>
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
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-door-closed"></i> Room Number *
                                </label>
                                <input type="text" class="form-control" name="room_number" 
                                       value="<?php echo $_POST['room_number'] ?? ''; ?>" 
                                       placeholder="e.g., 101, 202, 303"
                                       required>
                                <small class="form-text">Enter a unique room number</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-house-door"></i> Room Type *
                                </label>
                                <select class="form-select" name="room_type_id" required>
                                    <option value="">Select Room Type</option>
                                    <?php foreach ($room_types as $type): ?>
                                        <option value="<?php echo $type['room_type_id']; ?>"
                                                <?php echo (isset($_POST['room_type_id']) && $_POST['room_type_id'] == $type['room_type_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['type_name']); ?> - ₱<?php echo number_format($type['nightly_rate'], 2); ?>/night
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text">Choose the category of this room</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-building"></i> Floor *
                                </label>
                                <input type="text" class="form-control" name="floor" 
                                       value="<?php echo $_POST['floor'] ?? ''; ?>" 
                                       placeholder="e.g., 1, 2, G"
                                       required>
                                <small class="form-text">Specify the floor location</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-info-circle"></i> Status *
                                </label>
                                <select class="form-select" name="status" required>
                                    <option value="Available" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Available') ? 'selected' : ''; ?>>
                                        Available
                                    </option>
                                    <option value="Occupied" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Occupied') ? 'selected' : ''; ?>>
                                        Occupied
                                    </option>
                                    <option value="Maintenance" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Maintenance') ? 'selected' : ''; ?>>
                                        Maintenance
                                    </option>
                                </select>
                                <small class="form-text">Current availability status</small>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Image Management:</strong> Room images are managed per room type, not per individual room. 
                            After adding a room, you can manage the images for its room type using the 
                            <strong>Manage Images</strong> button in the All Rooms page.
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="all-rooms.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Add Room
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