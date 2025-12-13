<?php
require_once '../config/db.php';
require_once '../config/settings.php';
$page_title = "All Rooms";
include 'includes/header.php';
include 'includes/sidebar.php';

// Display success/error messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Fetch all rooms with room type information including thumbnail
$query = "SELECT r.*, rt.type_name, rt.nightly_rate, rt.thumbnail 
          FROM room r 
          INNER JOIN room_type rt ON r.room_type_id = rt.room_type_id 
          ORDER BY r.room_id DESC";
$result = $conn->query($query);
$rooms = db_fetch_all($result);
?>

<style>
.enhanced-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    overflow: hidden;
}

.enhanced-card-header {
    background: linear-gradient(135deg, var(--accent-brown), var(--warm-tan));
    color: white;
    border: none;
    padding: 20px;
}

.table-enhanced {
    margin: 0;
}

.table-enhanced thead {
    background-color: var(--light-cream);
    color: var(--secondary-dark);
}

.table-enhanced tbody tr {
    transition: all 0.3s ease;
}

.table-enhanced tbody tr:hover {
    background-color: var(--light-cream);
    transform: scale(1.01);
}

.room-thumbnail {
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.room-placeholder {
    width: 80px;
    height: 60px;
    background: linear-gradient(135deg, var(--secondary-dark), var(--accent-brown));
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.btn-action {
    margin: 2px;
    transition: all 0.3s ease;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.info-note {
    background: var(--light-cream);
    padding: 15px 20px;
    border-radius: 10px;
    border-left: 4px solid var(--accent-brown);
    margin-top: 20px;
}
</style>

<div class="container-fluid">
    <?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" style="border-radius: 10px; border-left: 4px solid #28a745;">
        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" style="border-radius: 10px; border-left: 4px solid #dc3545;">
        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="enhanced-card card">
        <div class="enhanced-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> All Rooms</h5>
            <a href="add-room.php" class="btn btn-light btn-sm">
                <i class="bi bi-plus-circle"></i> Add New Room
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-enhanced table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Room Number</th>
                            <th>Room Type</th>
                            <th>Floor</th>
                            <th>Price/Night</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rooms)): ?>
                            <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><strong style="color: var(--secondary-dark);">#<?php echo $room['room_id']; ?></strong></td>
                                <td>
                                    <?php if (!empty($room['thumbnail'])): ?>
                                        <img src="../uploads/room_images/<?php echo htmlspecialchars($room['thumbnail']); ?>" 
                                             alt="Room Type" 
                                             class="room-thumbnail">
                                    <?php else: ?>
                                        <div class="room-placeholder">
                                            <i class="bi bi-image" style="color: white; font-size: 1.5rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong style="color: var(--accent-brown);"><?php echo htmlspecialchars($room['room_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($room['type_name']); ?></td>
                                <td><span class="badge" style="background-color: var(--secondary-dark);"><?php echo htmlspecialchars($room['floor']); ?></span></td>
                                <td><strong><?php echo format_currency($room['nightly_rate']); ?></strong></td>
                                <td>
                                    <?php
                                    $badge_class = '';
                                    switch($room['status']) {
                                        case 'Available':
                                            $badge_class = 'bg-success';
                                            break;
                                        case 'Occupied':
                                            $badge_class = 'bg-warning text-dark';
                                            break;
                                        case 'Maintenance':
                                            $badge_class = 'bg-danger';
                                            break;
                                        default:
                                            $badge_class = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($room['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit-room.php?id=<?php echo $room['room_id']; ?>" 
                                       class="btn btn-sm btn-warning btn-action" 
                                       title="Edit Room Details">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="edit-room-type.php?id=<?php echo $room['room_type_id']; ?>" 
                                       class="btn btn-sm btn-info btn-action" 
                                       title="Manage Images">
                                        <i class="bi bi-images"></i>
                                    </a>
                                    <a href="delete-room.php?id=<?php echo $room['room_id']; ?>" 
                                       class="btn btn-sm btn-danger btn-action" 
                                       title="Delete Room"
                                       onclick="return confirm('Are you sure you want to delete this room?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5" style="color: var(--secondary-dark);">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--warm-tan);"></i>
                                    <p class="mt-3">No rooms found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($rooms)): ?>
            <div class="info-note m-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <i class="bi bi-info-circle" style="color: var(--accent-brown);"></i>
                        <strong style="color: var(--secondary-dark);">Note:</strong>
                        <span style="color: var(--primary-dark);">
                            Images shown are room type thumbnails. Click <i class="bi bi-images"></i> to manage all gallery images.
                        </span>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <strong style="color: var(--accent-brown);">
                            Total Rooms: <?php echo count($rooms); ?>
                        </strong>
                    </div>
                </div>
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