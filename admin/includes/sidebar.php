<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header">
            <div class="text-center mb-3">
                <div style="width: 60px; height: 60px; background: white; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                    <img src="../uploads/room_images/zaid-logo.png" 
                         alt="ZAID HOTEL" 
                         style="width: 45px; height: 45px; object-fit: contain;">
                </div>
                <h4 class="mb-1" style="font-weight: 700;">ZAID HOTEL</h4>
                <p class="mb-0 small">Admin Panel</p>
            </div>
            <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">
            <p class="mb-0 text-center">
                <i class="bi bi-person-circle"></i> 
                <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
            </p>
        </div>

        <ul class="list-unstyled components" style="overflow-y: visible; max-height: none;">
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