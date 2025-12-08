<?php
$page_title = "About Us - ZAID HOTEL";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<style>
.about-hero {
    background: linear-gradient(rgba(2,0,13,0.7), rgba(7,32,63,0.8)),
                url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1600') center/cover;
    padding: 100px 0;
    color: var(--light-cream);
}

.about-section {
    padding: 80px 0;
}

.feature-box {
    padding: 40px;
    border-radius: 15px;
    background: white;
    box-shadow: 0 4px 12px rgba(7,32,63,0.1);
    height: 100%;
    transition: transform 0.3s;
}

.feature-box:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 24px rgba(166,94,70,0.3);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--accent-brown), var(--warm-tan));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.gallery-img {
    height: 300px;
    object-fit: cover;
    border-radius: 15px;
    transition: transform 0.3s;
    cursor: pointer;
}

.gallery-img:hover {
    transform: scale(1.05);
}
</style>

<!-- Hero Section -->
<section class="about-hero text-center">
    <div class="container">
        <h1 class="display-3 fw-bold mb-3">Welcome to ZAID HOTEL</h1>
        <p class="lead mb-4">Where Luxury Meets Comfort</p>
        <p class="fs-5">Experience unparalleled hospitality and elegance in the heart of the city</p>
    </div>
</section>

<!-- Our Story -->
<section class="about-section" style="background-color: var(--light-cream);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800" 
                     alt="ZAID HOTEL Lobby" 
                     class="img-fluid rounded shadow-lg">
            </div>
            <div class="col-lg-6">
                <h2 class="mb-4" style="color: var(--secondary-dark);">Our Story</h2>
                <p style="color: var(--primary-dark); line-height: 1.8;">
                    ZAID HOTEL has been a beacon of luxury and comfort since our establishment. 
                    Founded with a vision to redefine hospitality, we have consistently delivered 
                    exceptional experiences to our guests from around the world.
                </p>
                <p style="color: var(--primary-dark); line-height: 1.8;">
                    Our commitment to excellence is reflected in every detail, from our elegantly 
                    appointed rooms to our world-class amenities. We believe that every guest deserves 
                    the finest in comfort, service, and attention to detail.
                </p>
                <p style="color: var(--primary-dark); line-height: 1.8;">
                    At ZAID HOTEL, we don't just provide accommodation; we create memories that last 
                    a lifetime. Our dedicated team works tirelessly to ensure your stay is nothing 
                    short of extraordinary.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Values -->
<section class="about-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 style="color: var(--secondary-dark);">Our Mission & Values</h2>
            <p class="text-muted">What drives us to be the best</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-box text-center">
                    <div class="feature-icon">
                        <i class="bi bi-heart-fill" style="font-size: 2.5rem; color: white;"></i>
                    </div>
                    <h4 style="color: var(--accent-brown);">Guest Satisfaction</h4>
                    <p style="color: var(--primary-dark);">
                        Your comfort and happiness are our top priorities. We go above and beyond 
                        to exceed your expectations at every turn.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-box text-center">
                    <div class="feature-icon">
                        <i class="bi bi-gem" style="font-size: 2.5rem; color: white;"></i>
                    </div>
                    <h4 style="color: var(--accent-brown);">Premium Quality</h4>
                    <p style="color: var(--primary-dark);">
                        From our luxurious rooms to our impeccable service, we maintain the highest 
                        standards in everything we do.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-box text-center">
                    <div class="feature-icon">
                        <i class="bi bi-people-fill" style="font-size: 2.5rem; color: white;"></i>
                    </div>
                    <h4 style="color: var(--accent-brown);">Exceptional Service</h4>
                    <p style="color: var(--primary-dark);">
                        Our professional and friendly staff are always ready to assist you with 
                        genuine warmth and hospitality.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery -->
<section class="about-section" style="background-color: var(--light-cream);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 style="color: var(--secondary-dark);">Experience ZAID HOTEL</h2>
            <p class="text-muted">A glimpse into our world of luxury</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600" 
                     alt="Luxury Lobby" 
                     class="gallery-img w-100 shadow">
            </div>
            <div class="col-md-4">
                <img src="https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=600" 
                     alt="Hotel Room" 
                     class="gallery-img w-100 shadow">
            </div>
            <div class="col-md-4">
                <img src="https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=600" 
                     alt="Hotel Pool" 
                     class="gallery-img w-100 shadow">
            </div>
            <div class="col-md-6">
                <img src="https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=700" 
                     alt="Hotel Corridor" 
                     class="gallery-img w-100 shadow">
            </div>
            <div class="col-md-6">
                <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=700" 
                     alt="Hotel Lounge" 
                     class="gallery-img w-100 shadow">
            </div>
        </div>
    </div>
</section>

<!-- Amenities -->
<section class="about-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 style="color: var(--secondary-dark);">World-Class Amenities</h2>
            <p class="text-muted">Everything you need for a perfect stay</p>
        </div>
        
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="bi bi-wifi" style="font-size: 3rem; color: var(--accent-brown);"></i>
                    <h5 class="mt-3" style="color: var(--secondary-dark);">Free WiFi</h5>
                    <p class="small text-muted">High-speed internet</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="bi bi-p-square" style="font-size: 3rem; color: var(--accent-brown);"></i>
                    <h5 class="mt-3" style="color: var(--secondary-dark);">Free Parking</h5>
                    <p class="small text-muted">Secure parking lot</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="bi bi-water" style="font-size: 3rem; color: var(--accent-brown);"></i>
                    <h5 class="mt-3" style="color: var(--secondary-dark);">Swimming Pool</h5>
                    <p class="small text-muted">Rooftop pool access</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="bi bi-cup-hot" style="font-size: 3rem; color: var(--accent-brown);"></i>
                    <h5 class="mt-3" style="color: var(--secondary-dark);">Restaurant</h5>
                    <p class="small text-muted">Fine dining experience</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="bi bi-heart-pulse" style="font-size: 3rem; color: var(--accent-brown);"></i>
                    <h5 class="mt-3" style="color: var(--secondary-dark);">Fitness Center</h5>
                    <p class="small text-muted">24/7 gym access</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="bi bi-flower1" style="font-size: 3rem; color: var(--accent-brown);"></i>
                    <h5 class="mt-3" style="color: var(--secondary-dark);">Spa & Wellness</h5>
                    <p class="small text-muted">Relaxation services</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="bi bi-headset" style="font-size: 3rem; color: var(--accent-brown);"></i>
                    <h5 class="mt-3" style="color: var(--secondary-dark);">24/7 Concierge</h5>
                    <p class="small text-muted">Always at your service</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="bi bi-briefcase" style="font-size: 3rem; color: var(--accent-brown);"></i>
                    <h5 class="mt-3" style="color: var(--secondary-dark);">Business Center</h5>
                    <p class="small text-muted">Meeting rooms available</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info -->
<section class="about-section" style="background-color: var(--secondary-dark); color: var(--light-cream);">
    <div class="container text-center">
        <h2 class="mb-4" style="color: var(--warm-tan);">Visit Us</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <i class="bi bi-geo-alt-fill" style="font-size: 3rem; color: var(--warm-tan);"></i>
                <h5 class="mt-3">Location</h5>
                <p>123 Luxury Avenue<br>Downtown, City 12345</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-telephone-fill" style="font-size: 3rem; color: var(--warm-tan);"></i>
                <h5 class="mt-3">Phone</h5>
                <p>+1 (555) 123-4567<br>+1 (555) 123-4568</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-envelope-fill" style="font-size: 3rem; color: var(--warm-tan);"></i>
                <h5 class="mt-3">Email</h5>
                <p>info@zaidhotel.com<br>reservations@zaidhotel.com</p>
            </div>
        </div>
        
        <div class="mt-5">
            <a href="rooms.php" class="btn btn-primary btn-lg">Book Your Stay Now</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>