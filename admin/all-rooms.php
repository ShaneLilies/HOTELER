<?php
require_once '../config/db.php';
$page_title = "All Rooms";
include 'includes/header.php';
include 'includes/sidebar.php';

// Display success/error messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Fetch all rooms with room type information
$query = "SELECT r.*, rt.type_name, rt.nightly_rate 
          FROM room r 
          INNER JOIN room_type rt ON r.room_type_id = rt.room_type_id 
          ORDER BY r.room_id DESC";
$result = $conn->query($query);
$rooms = db_fetch_all($result);
?>

<div class="container-fluid">
    <?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> All Rooms</h5>
            <a href="add-room.php" class="btn btn-light btn-sm">
                <i class="bi bi-plus-circle"></i> Add New Room
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Room Number</th>
                            <th>Room Type</th>
                            <th>Floor</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rooms)): ?>
                            <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><?php echo $room['room_id']; ?></td>
                                <td>
                                    <?php if (!empty($room['image'])): ?>
                                        <img src="../uploads/room_images/<?php echo htmlspecialchars($room['image']); ?>" 
                                             alt="Room" 
                                             class="img-thumbnail" 
                                             style="max-width: 80px; max-height: 60px;">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white text-center" style="width:80px; height:60px; line-height:60px;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($room['room_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($room['type_name']); ?></td>
                                <td><?php echo htmlspecialchars($room['floor']); ?></td>
                                <td>$<?php echo number_format($room['nightly_rate'], 2); ?></td>
                                <td>
                                    <?php
                                    $badge_class = '';
                                    switch($room['status']) {
                                        case 'Available':
                                            $badge_class = 'bg-success';
                                            break;
                                        case 'Occupied':
                                            $badge_class = 'bg-warning';
                                            break;
                                        case 'Maintenance':
                                            $badge_class = 'bg-danger';
                                            break;
                                        default:
                                            $badge_class = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($room['status']); ?></span>
                                </td>
                                <td>
                                    <a href="edit-room.php?id=<?php echo $room['room_id']; ?>" 
                                       class="btn btn-sm btn-warning btn-action" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="edit-room-type.php?id=<?php echo $room['room_type_id']; ?>" 
                                       class="btn btn-sm btn-info btn-action" 
                                       title="Manage Images">
                                        <i class="bi bi-images"></i>
                                    </a>
                                    <a href="delete-room.php?id=<?php echo $room['room_id']; ?>" 
                                       class="btn btn-sm btn-danger btn-action" 
                                       title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this room?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No rooms found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    </div><!-- #content -->
</div><!-- .wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>