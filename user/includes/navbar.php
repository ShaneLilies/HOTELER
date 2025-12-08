<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="../uploads/room_images/zaid-logo.png" 
                 alt="ZAID HOTEL Logo" 
                 style="height: 50px; width: 50px; object-fit: contain;">
            <span>ZAID HOTEL</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-house-door"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="about.php">
                        <i class="bi bi-info-circle"></i> About Us
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''; ?>" href="rooms.php">
                        <i class="bi bi-door-open"></i> Rooms
                    </a>
                </li>
                
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my-reservations.php' ? 'active' : ''; ?>" href="my-reservations.php">
                        <i class="bi bi-calendar-check"></i> My Reservations
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="background-color: var(--primary-dark);">
                        <li><a class="dropdown-item" href="profile.php" style="color: var(--light-cream);"><i class="bi bi-person"></i> Profile</a></li>
                        <li><hr class="dropdown-divider" style="border-color: var(--accent-brown);"></li>
                        <li><a class="dropdown-item" href="auth-handler.php?action=logout" style="color: var(--light-cream);"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">
                        <i class="bi bi-box-arrow-in-right"></i> Login / Register
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>