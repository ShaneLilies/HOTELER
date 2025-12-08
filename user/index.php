<?php
require_once '../config/db.php';
$page_title = "Home - ZAID HOTEL";
include 'includes/header.php';
include 'includes/navbar.php';

// Get 4 featured room types
$query = "SELECT * FROM room_type ORDER BY nightly_rate ASC LIMIT 4";
$result = $conn->query($query);
$featured_rooms = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $featured_rooms[] = $row;
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-3 fw-bold mb-3" style="text-shadow: 2px 2px 8px rgba(0,0,0,0.5);">Welcome to ZAID HOTEL</h1>
        <p class="lead mb-4" style="font-size: 1.5rem;">Experience the perfect blend of comfort, elegance, and exceptional service</p>
        <a href="rooms.php" class="btn btn-primary btn-lg">Explore Our Rooms</a>
    </div>
</section>

<!-- Quick Booking Form -->
<div class="container">
    <div class="booking-form-container">
        <h3 class="text-center mb-4">Quick Reservation</h3>
        <form action="rooms.php" method="GET">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Check-in Date</label>
                    <input type="date" class="form-control" name="check_in" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Check-out Date</label>
                    <input type="date" class="form-control" name="check_out" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Featured Room Types Section -->
<section class="container my-5">
    <h2 class="text-center mb-5" style="color: var(--secondary-dark);">Our Featured Room Types</h2>
    <div class="row g-4">
        <?php foreach ($featured_rooms as $room): ?>
        <div class="col-md-6 col-lg-3">
            <div class="card featured-card h-100" 
                 onclick="openRoomModal(<?php echo htmlspecialchars(json_encode($room)); ?>)"
                 style="cursor: pointer;">
                <?php if (!empty($room['thumbnail'])): ?>
                    <img src="../uploads/room_images/<?php echo htmlspecialchars($room['thumbnail']); ?>" 
                         class="card-img" 
                         style="height: 300px; object-fit: cover;"
                         alt="<?php echo htmlspecialchars($room['type_name']); ?>">
                <?php else: ?>
                    <div class="card-img" style="height: 300px; background: linear-gradient(135deg, var(--secondary-dark), var(--accent-brown)); display: flex; align-items: center; justify-content: center;">
                        <div class="text-center text-white">
                            <i class="bi bi-door-open" style="font-size: 4rem;"></i>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--accent-brown);">
                        <?php echo htmlspecialchars($room['type_name']); ?>
                    </h5>
                    <p class="card-text text-muted small mb-3">
                        <?php echo htmlspecialchars(substr($room['description'], 0, 80)) . '...'; ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0" style="color: var(--accent-brown);">
                            $<?php echo number_format($room['nightly_rate'], 2); ?>
                        </h4>
                        <small class="text-muted">per night</small>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-5">
        <a href="rooms.php" class="btn btn-outline-primary btn-lg">View All Room Types</a>
    </div>
</section>

<!-- Room Type Modal -->
<div class="modal fade" id="roomModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--secondary-dark); color: var(--light-cream);">
                <h5 class="modal-title" id="roomModalTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Carousel -->
                <div id="roomModalCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-inner" id="carouselImages"></div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#roomModalCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#roomModalCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
                
                <!-- Details -->
                <div id="roomModalDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="bookNowBtn" class="btn btn-primary">Book Now</a>
            </div>
        </div>
    </div>
</div>

<!-- Amenities Section -->
<section class="bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-5" style="color: var(--secondary-dark);">World-Class Amenities</h2>
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="amenities-icon">
                    <i class="bi bi-wifi"></i>
                </div>
                <h5 style="color: var(--secondary-dark);">Free WiFi</h5>
                <p class="text-muted">High-speed internet throughout</p>
            </div>
            <div class="col-md-3">
                <div class="amenities-icon">
                    <i class="bi bi-cup-hot"></i>
                </div>
                <h5 style="color: var(--secondary-dark);">Restaurant</h5>
                <p class="text-muted">Fine dining experience</p>
            </div>
            <div class="col-md-3">
                <div class="amenities-icon">
                    <i class="bi bi-p-square"></i>
                </div>
                <h5 style="color: var(--secondary-dark);">Free Parking</h5>
                <p class="text-muted">Complimentary for all guests</p>
            </div>
            <div class="col-md-3">
                <div class="amenities-icon">
                    <i class="bi bi-clock"></i>
                </div>
                <h5 style="color: var(--secondary-dark);">24/7 Service</h5>
                <p class="text-muted">Round-the-clock support</p>
            </div>
        </div>
    </div>
</section>

<script>
function openRoomModal(room) {
    // Set title
    document.getElementById('roomModalTitle').textContent = room.type_name;
    
    // Build carousel images
    const images = [];
    if (room.image1) images.push(room.image1);
    if (room.image2) images.push(room.image2);
    if (room.image3) images.push(room.image3);
    if (room.image4) images.push(room.image4);
    if (room.image5) images.push(room.image5);
    
    if (images.length === 0 && room.thumbnail) {
        images.push(room.thumbnail);
    }
    
    const carouselHtml = images.map((img, index) => `
        <div class="carousel-item ${index === 0 ? 'active' : ''}">
            <img src="../uploads/room_images/${img}" class="d-block w-100" style="height: 400px; object-fit: cover;">
        </div>
    `).join('');
    
    document.getElementById('carouselImages').innerHTML = carouselHtml || `
        <div class="carousel-item active">
            <div class="d-flex align-items-center justify-content-center" style="height: 400px; background: linear-gradient(135deg, var(--secondary-dark), var(--accent-brown)); color: white;">
                <i class="bi bi-door-open" style="font-size: 5rem;"></i>
            </div>
        </div>
    `;
    
    // Build details
    document.getElementById('roomModalDetails').innerHTML = `
        <h4 style="color: var(--accent-brown);">$${parseFloat(room.nightly_rate).toFixed(2)} <small class="text-muted">per night</small></h4>
        <p class="mb-3"><i class="bi bi-people-fill"></i> Maximum ${room.max_guests} guests</p>
        <h6 style="color: var(--secondary-dark);">Description</h6>
        <p>${room.description || 'No description available'}</p>
        <h6 style="color: var(--secondary-dark);">Amenities</h6>
        <ul>
            <li>Premium bedding and linens</li>
            <li>Flat-screen TV with cable</li>
            <li>Private bathroom with toiletries</li>
            <li>Air conditioning and heating</li>
            <li>Mini refrigerator</li>
            <li>Complimentary WiFi</li>
        </ul>
    `;
    
    // Set book now link
    document.getElementById('bookNowBtn').href = `book.php?room_type_id=${room.room_type_id}`;
    
    // Show modal
    new bootstrap.Modal(document.getElementById('roomModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>