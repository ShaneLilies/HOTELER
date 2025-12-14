<?php
require_once '../config/db.php';
$page_title = "Edit Room Type Images";
include 'includes/header.php';
include 'includes/sidebar.php';

$success = '';
$error = '';

// Get room type ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: all-rooms.php");
    exit();
}

$room_type_id = intval($_GET['id']);

// Fetch room type details
$stmt = $conn->prepare("SELECT * FROM room_type WHERE room_type_id = ?");
$stmt->bindValue(1, $room_type_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$room_type = $result->fetchArray(SQLITE3_ASSOC);

if (!$room_type) {
    header("Location: all-rooms.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $image_fields = ['thumbnail', 'image1', 'image2', 'image3', 'image4', 'image5'];
    $uploaded_images = [];
    
    foreach ($image_fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES[$field]['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                $image_name = time() . '_' . $field . '_' . $filename;
                $upload_path = '../uploads/room_images/' . $image_name;
                
                if (!is_dir('../uploads/room_images/')) {
                    mkdir('../uploads/room_images/', 0777, true);
                }
                
                if (move_uploaded_file($_FILES[$field]['tmp_name'], $upload_path)) {
                    // Delete old image if exists
                    if (!empty($room_type[$field]) && file_exists('../uploads/room_images/' . $room_type[$field])) {
                        unlink('../uploads/room_images/' . $room_type[$field]);
                    }
                    $uploaded_images[$field] = $image_name;
                } else {
                    $error = "Failed to upload " . $field . "!";
                }
            } else {
                $error = "Invalid file type for " . $field . ". Only JPG, JPEG, PNG & GIF allowed.";
            }
        }
    }
    
    if (empty($error) && !empty($uploaded_images)) {
        // Build update query
        $update_parts = [];
        $values = [];
        
        foreach ($uploaded_images as $field => $image_name) {
            $update_parts[] = "$field = ?";
            $values[] = $image_name;
        }
        
        $update_sql = "UPDATE room_type SET " . implode(', ', $update_parts) . " WHERE room_type_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        
        foreach ($values as $index => $value) {
            $update_stmt->bindValue($index + 1, $value, SQLITE3_TEXT);
        }
        $update_stmt->bindValue(count($values) + 1, $room_type_id, SQLITE3_INTEGER);
        
        if ($update_stmt->execute()) {
            $success = "Images updated successfully!";
            // Refresh room type data
            $stmt = $conn->prepare("SELECT * FROM room_type WHERE room_type_id = ?");
            $stmt->bindValue(1, $room_type_id, SQLITE3_INTEGER);
            $result = $stmt->execute();
            $room_type = $result->fetchArray(SQLITE3_ASSOC);
        } else {
            $error = "Failed to update images!";
        }
    } elseif (empty($error)) {
        $error = "No images were uploaded!";
    }
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-images"></i> Manage Images for: <?php echo htmlspecialchars($room_type['type_name']); ?>
                    </h5>
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
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Note:</strong> Upload a thumbnail for table display and up to 5 additional images for the carousel on the user side.
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Thumbnail -->
                        <div class="card mb-4">
                            <div class="card-header bg-warning">
                                <h6 class="mb-0">Thumbnail Image (For Table Display)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <?php if (!empty($room_type['thumbnail'])): ?>
                                            <img src="../uploads/room_images/<?php echo htmlspecialchars($room_type['thumbnail']); ?>" 
                                                 class="img-thumbnail mb-2" 
                                                 style="max-height: 150px;">
                                            <p class="small text-muted mb-0">Current thumbnail</p>
                                        <?php else: ?>
                                            <div class="bg-secondary text-white text-center p-5">
                                                <i class="bi bi-image" style="font-size: 3rem;"></i>
                                                <p class="mb-0 mt-2">No thumbnail</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Upload New Thumbnail</label>
                                        <input type="file" class="form-control" name="thumbnail" accept="image/*">
                                        <small class="text-muted">Recommended: 400x300px. Leave empty to keep current.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Carousel Images -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Carousel Images (For Room Details Page)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Image <?php echo $i; ?></h6>
                                                <?php if (!empty($room_type['image' . $i])): ?>
                                                    <img src="../uploads/room_images/<?php echo htmlspecialchars($room_type['image' . $i]); ?>" 
                                                         class="img-thumbnail mb-2 w-100" 
                                                         style="height: 150px; object-fit: cover;">
                                                    <p class="small text-muted mb-2">Current image <?php echo $i; ?></p>
                                                <?php else: ?>
                                                    <div class="bg-light text-center p-4 mb-2">
                                                        <i class="bi bi-image" style="font-size: 2rem; color: #ccc;"></i>
                                                        <p class="mb-0 small text-muted">No image</p>
                                                    </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control form-control-sm" name="image<?php echo $i; ?>" accept="image/*">
                                            </div>
                                        </div>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-muted small mt-3">
                                    <i class="bi bi-lightbulb"></i> 
                                    These images will appear in a carousel when users view room details. Upload high-quality images showing different angles of the room.
                                </p>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="all-rooms.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Upload Images
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Room Type Info -->
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">Room Type Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($room_type['type_name']); ?></p>
                            <p><strong>Rate:</strong> ₱<?php echo number_format($room_type['nightly_rate'], 2); ?> per night</p>
                            <p><strong>Max Guests:</strong> <?php echo $room_type['max_guests']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Description:</strong></p>
                            <p class="text-muted"><?php echo htmlspecialchars($room_type['description']); ?></p>
                        </div>
                    </div>
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