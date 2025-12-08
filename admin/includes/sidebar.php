<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header">
            <div class="d-flex align-items-center mb-3">
                <img src="../uploads/room_images/zaid-logo.png" 
                     alt="ZAID HOTEL" 
                     style="height: 50px; width: 50px; object-fit: contain; margin-right: 10px; filter: brightness(0) invert(1);">
                <h3 class="mb-0"><i class="bi bi-building"></i> Hotel Admin</h3>
            </div>
            <p class="mb-0">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
        </div>

        <ul class="list-unstyled components">
            <li>
                <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="all-rooms.php" class="<?php echo $current_page == 'all-rooms.php' ? 'active' : ''; ?>">
                    <i class="bi bi-list-ul"></i> All Rooms
                </a>
            </li>
            <li>
                <a href="add-room.php" class="<?php echo $current_page == 'add-room.php' ? 'active' : ''; ?>">
                    <i class="bi bi-plus-circle"></i> Add Room
                </a>
            </li>
            <li>
                <a href="reservations.php" class="<?php echo $current_page == 'reservations.php' ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-check"></i> Reservations
                </a>
            </li>
            <li>
                <a href="guests.php" class="<?php echo $current_page == 'guests.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i> Guests
                </a>
            </li>
            <li>
                <a href="billing.php" class="<?php echo $current_page == 'billing.php' ? 'active' : ''; ?>">
                    <i class="bi bi-receipt"></i> Billing
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <div id="content">
        <div class="navbar-top d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?></h4>
            <div>
                <span class="text-muted"><i class="bi bi-calendar3"></i> <?php echo date('F d, Y'); ?></span>
            </div>
        </div>