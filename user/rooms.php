<?php
require_once '../config/db.php';
$page_title = "Available Rooms - ZAID HOTEL";
include 'includes/header.php';
include 'includes/navbar.php';

// Get check-in and check-out dates from URL
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';

// Fetch ALL room types with availability count (don't hide occupied ones)
$query = "SELECT rt.*, 
          (SELECT COUNT(*) FROM room r 
           WHERE r.room_type_id = rt.room_type_id 
           AND r.status = 'Available'
           AND r.room_id NOT IN (
               SELECT res.room_id FROM reservation res 
               WHERE res.status IN ('Confirmed', 'Occupied')
               AND (
                   (res.check_in_date <= ? AND res.check_out_date > ?) OR
                   (res.check_in_date < ? AND res.check_out_date >= ?) OR
                   (res.check_in_date >= ? AND res.check_out_date <= ?)
               )
           )
          ) as available_count
          FROM room_type rt
          ORDER BY rt.nightly_rate ASC";

$stmt = $conn->prepare($query);
$date_check_in = $check_in ?: '9999-12-31';
$date_check_out = $check_out ?: '9999-12-31';
$stmt->bindValue(1, $date_check_in, SQLITE3_TEXT);
$stmt->bindValue(2, $date_check_in, SQLITE3_TEXT);
$stmt->bindValue(3, $date_check_out, SQLITE3_TEXT);
$stmt->bindValue(4, $date_check_out, SQLITE3_TEXT);
$stmt->bindValue(5, $date_check_in, SQLITE3_TEXT);
$stmt->bindValue(6, $date_check_out, SQLITE3_TEXT);

$result = $stmt->execute();
$room_types = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $room_types[] = $row;
}
?>

<div class="container my-5">
    <h1 class="text-center mb-4" style="color: var(--secondary-dark);">Available Room Types</h1>
    
    <?php if ($check_in && $check_out): ?>
    <div class="alert alert-info text-center">
        <i class="bi bi-calendar-check"></i> Selected dates: 
        <strong><?php echo date('M d, Y', strtotime($check_in)); ?></strong> to 
        <strong><?php echo date('M d, Y', strtotime($check_out)); ?></strong>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (!empty($room_types)): ?>
            <?php foreach ($room_types as $type): ?>
            <?php $is_available = $type['available_count'] > 0; ?>
            <div class="col-md-6 col-lg-4">
                <div class="card room-card h-100 <?php echo !$is_available ? 'opacity-75' : ''; ?>">
                    <!-- Room Type Images Carousel -->
                    <?php 
                    $images = [];
                    if (!empty($type['image1'])) $images[] = $type['image1'];
                    if (!empty($type['image2'])) $images[] = $type['image2'];
                    if (!empty($type['image3'])) $images[] = $type['image3'];
                    if (!empty($type['image4'])) $images[] = $type['image4'];
                    if (!empty($type['image5'])) $images[] = $type['image5'];
                    
                    if (empty($images)) {
                        $images[] = $type['thumbnail'];
                    }
                    ?>
                    
                    <?php if (count($images) > 1): ?>
                    <div id="carousel-<?php echo $type['room_type_id']; ?>" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($images as $index => $image): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <?php if (!empty($image)): ?>
                                    <img src="../uploads/room_images/<?php echo htmlspecialchars($image); ?>" 
                                         class="d-block w-100" 
                                         alt="<?php echo htmlspecialchars($type['type_name']); ?>"
                                         style="height: 250px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="d-block w-100 d-flex align-items-center justify-content-center" 
                                         style="height: 250px; background: linear-gradient(135deg, var(--secondary-dark), var(--accent-brown));">
                                        <div class="text-center text-white">
                                            <i class="bi bi-door-open" style="font-size: 3rem;"></i>
                                            <p class="mb-0 mt-2 fw-bold"><?php echo htmlspecialchars($type['type_name']); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?php echo $type['room_type_id']; ?>" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?php echo $type['room_type_id']; ?>" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                    <?php else: ?>
                        <?php if (!empty($images[0])): ?>
                            <img src="../uploads/room_images/<?php echo htmlspecialchars($images[0]); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($type['type_name']); ?>"
                                 style="height: 250px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center" 
                                 style="height: 250px; background: linear-gradient(135deg, var(--secondary-dark), var(--accent-brown));">
                                <div class="text-center text-white">
                                    <i class="bi bi-door-open" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2 fw-bold"><?php echo htmlspecialchars($type['type_name']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title" style="color: var(--accent-brown);">
                            <?php echo htmlspecialchars($type['type_name']); ?>
                        </h5>
                        
                        <?php if (!empty($type['description'])): ?>
                        <p class="card-text text-muted small">
                            <?php echo htmlspecialchars($type['description']); ?>
                        </p>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <p class="mb-2">
                                <i class="bi bi-person-fill" style="color: var(--accent-brown);"></i> 
                                <strong>Up to <?php echo $type['max_guests']; ?></strong> Guests
                            </p>
                            <p class="mb-2">
                                <i class="bi bi-door-open" style="color: var(--accent-brown);"></i> 
                                <strong><?php echo $type['available_count']; ?></strong> rooms available
                            </p>
                            <?php if ($is_available): ?>
                                <span class="badge bg-success">Available Now</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Fully Occupied</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h4 class="mb-0" style="color: var(--accent-brown);">
                                        $<?php echo number_format($type['nightly_rate'], 2); ?>
                                    </h4>
                                    <small class="text-muted">per night</small>
                                </div>
                            </div>
                            <?php if ($is_available): ?>
                            <a href="book.php?room_type_id=<?php echo $type['room_type_id']; ?>&check_in=<?php echo urlencode($check_in ?: ''); ?>&check_out=<?php echo urlencode($check_out ?: ''); ?>" 
                               class="btn btn-primary w-100">
                                <i class="bi bi-calendar-check"></i> Book Now
                            </a>
                            <?php else: ?>
                            <button class="btn btn-secondary w-100" disabled>
                                <i class="bi bi-x-circle"></i> Not Available
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i> 
                    No rooms available for the selected dates. Please try different dates.
                </div>
                <div class="text-center">
                    <a href="rooms.php" class="btn btn-primary">View All Available Rooms</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>