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

<style>
/* Enhanced Featured Cards */
.featured-card {
    position: relative;
    overflow: hidden;
    border-radius: 15px;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.featured-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(166, 94, 70, 0.4);
}

.featured-card-img {
    height: 280px;
    width: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.featured-card:hover .featured-card-img {
    transform: scale(1.1);
}

.featured-card-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(2, 0, 13, 0.95) 0%, rgba(7, 32, 63, 0.85) 60%, transparent 100%);
    padding: 30px 20px 20px;
    color: white;
}

.featured-card-title {
    color: var(--warm-tan);
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.featured-card-desc {
    color: var(--light-cream);
    font-size: 0.9rem;
    margin-bottom: 15px;
    line-height: 1.4;
}

.featured-card-price {
    color: white;
    font-size: 1.8rem;
    font-weight: 700;
}

.featured-card-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: var(--accent-brown);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

/* Enhanced Modal */
.modal-carousel-img {
    height: 450px;
    width: 100%;
    object-fit: cover;
    border-radius: 10px;
}

.modal-carousel-placeholder {
    height: 450px;
    width: 100%;
    background: linear-gradient(135deg, var(--secondary-dark), var(--accent-brown));
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}

.amenity-item {
    display: flex;
    align-items: center;
    padding: 8px 0;
    color: var(--primary-dark);
}

.amenity-item i {
    color: var(--accent-brown);
    margin-right: 10px;
    font-size: 1.2rem;
}
</style>

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
                 onclick="openRoomModal(<?php echo htmlspecialchars(json_encode($room)); ?>)">
                <?php if (!empty($room['thumbnail'])): ?>
                    <img src="../uploads/room_images/<?php echo htmlspecialchars($room['thumbnail']); ?>" 
                         class="featured-card-img" 
                         alt="<?php echo htmlspecialchars($room['type_name']); ?>">
                <?php else: ?>
                    <div class="featured-card-img" style="background: linear-gradient(135deg, var(--secondary-dark), var(--accent-brown)); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-door-open" style="font-size: 4rem; color: white;"></i>
                    </div>
                <?php endif; ?>
                
                <span class="featured-card-badge">
                    <i class="bi bi-star-fill"></i> Featured
                </span>
                
                <div class="featured-card-overlay">
                    <h5 class="featured-card-title"><?php echo htmlspecialchars($room['type_name']); ?></h5>
                    <p class="featured-card-desc">
                        <i class="bi bi-people-fill"></i> Up to <?php echo $room['max_guests']; ?> guests
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="featured-card-price">₱<?php echo number_format($room['nightly_rate'], 2); ?></span>
                        <small style="color: var(--light-cream);">per night</small>
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
        <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header" style="background-color: var(--secondary-dark); color: var(--light-cream);">
                <h5 class="modal-title" id="roomModalTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 30px;">
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
            <div class="modal-footer" style="background-color: var(--light-cream);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="bookNowBtn" class="btn btn-primary">
                    <i class="bi bi-calendar-check"></i> Book Now
                </a>
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
            <img src="../uploads/room_images/${img}" class="modal-carousel-img" alt="${room.type_name}">
        </div>
    `).join('');
    
    document.getElementById('carouselImages').innerHTML = carouselHtml || `
        <div class="carousel-item active">
            <div class="modal-carousel-placeholder">
                <i class="bi bi-door-open" style="font-size: 5rem; color: white;"></i>
            </div>
        </div>
    `;
    
    // Build details
    document.getElementById('roomModalDetails').innerHTML = `
        <div class="row mb-4">
            <div class="col-md-6">
                <h3 style="color: var(--accent-brown);">₱${parseFloat(room.nightly_rate).toFixed(2)} 
                    <small class="text-muted" style="font-size: 1rem;">per night</small>
                </h3>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="badge" style="background-color: var(--accent-brown); font-size: 1rem; padding: 8px 15px;">
                    <i class="bi bi-people-fill"></i> Up to ${room.max_guests} Guest${room.max_guests > 1 ? 's' : ''}
                </span>
            </div>
        </div>
        
        <div style="background-color: var(--light-cream); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <h6 style="color: var(--secondary-dark); margin-bottom: 15px;">
                <i class="bi bi-info-circle-fill"></i> Room Description
            </h6>
            <p style="color: var(--primary-dark); margin-bottom: 0;">${room.description || 'Experience comfort and elegance in this beautifully appointed room.'}</p>
        </div>
        
        <h6 style="color: var(--secondary-dark); margin-bottom: 15px;">
            <i class="bi bi-check-circle-fill"></i> Room Amenities
        </h6>
        <div class="row">
            <div class="col-md-6">
                <div class="amenity-item"><i class="bi bi-tv"></i> Flat-screen TV with cable</div>
                <div class="amenity-item"><i class="bi bi-wifi"></i> Complimentary WiFi</div>
                <div class="amenity-item"><i class="bi bi-snow"></i> Air conditioning & heating</div>
            </div>
            <div class="col-md-6">
                <div class="amenity-item"><i class="bi bi-cup-hot"></i> Coffee/tea maker</div>
                <div class="amenity-item"><i class="bi bi-droplet"></i> Private bathroom with toiletries</div>
                <div class="amenity-item"><i class="bi bi-safe"></i> In-room safe</div>
            </div>
        </div>
    `;
    
    // Set book now link
    document.getElementById('bookNowBtn').href = `book.php?room_type_id=${room.room_type_id}`;
    
    // Show modal
    new bootstrap.Modal(document.getElementById('roomModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>