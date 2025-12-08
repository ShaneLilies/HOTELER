<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'ZAID HOTEL - Luxury Accommodation'; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-dark: #02000d;
            --secondary-dark: #07203f;
            --light-cream: #ebded4;
            --warm-tan: #d9aa90;
            --accent-brown: #a65e46;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-cream);
            color: var(--primary-dark);
        }
        
        /* Navbar Styling */
        .navbar {
            background-color: var(--primary-dark) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--warm-tan) !important;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .hotel-logo {
            width: 50px;
            height: 50px;
            filter: brightness(0) invert(1);
        }
        
        .nav-link {
            color: var(--light-cream) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--warm-tan) !important;
            background-color: rgba(217, 170, 144, 0.1);
            border-radius: 5px;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(2,0,13,0.6), rgba(7,32,63,0.8)),
                        url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1600') center/cover;
            color: var(--light-cream);
            padding: 120px 0;
            min-height: 600px;
            display: flex;
            align-items: center;
        }
        
        /* Cards */
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            background: white;
            box-shadow: 0 4px 12px rgba(7,32,63,0.1);
        }
        
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(166,94,70,0.3);
        }
        
        .room-card img {
            height: 250px;
            object-fit: cover;
            background: var(--secondary-dark);
        }
        
        /* Buttons */
        .btn-primary {
            background-color: var(--accent-brown);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--warm-tan);
            color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(166,94,70,0.4);
        }
        
        .btn-outline-primary {
            border-color: var(--accent-brown);
            color: var(--accent-brown);
            font-weight: 600;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--accent-brown);
            border-color: var(--accent-brown);
        }
        
        /* Footer */
        .footer {
            background-color: var(--primary-dark);
            color: var(--light-cream);
            padding: 50px 0 30px;
            margin-top: 80px;
        }
        
        .footer h5 {
            color: var(--warm-tan);
            margin-bottom: 20px;
        }
        
        .footer a {
            color: var(--light-cream);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer a:hover {
            color: var(--warm-tan);
        }
        
        /* Booking Form */
        .booking-form-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 24px rgba(7,32,63,0.2);
            margin-top: -80px;
            position: relative;
            z-index: 10;
            border-top: 4px solid var(--accent-brown);
        }
        
        .booking-form-container h3 {
            color: var(--secondary-dark);
            margin-bottom: 30px;
        }
        
        /* Badge Styling */
        .badge {
            font-size: 0.9rem;
            padding: 8px 15px;
            font-weight: 600;
        }
        
        .bg-success {
            background-color: #28a745 !important;
        }
        
        .bg-warning {
            background-color: #ffc107 !important;
        }
        
        /* Form Controls */
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-brown);
            box-shadow: 0 0 0 0.2rem rgba(166, 94, 70, 0.25);
        }
        
        .form-label {
            color: var(--secondary-dark);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        /* Alert Messages */
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .alert-info {
            background-color: rgba(7, 32, 63, 0.1);
            color: var(--secondary-dark);
            border-left: 4px solid var(--secondary-dark);
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        /* Carousel for room images */
        .carousel-item img {
            height: 400px;
            object-fit: cover;
        }
        
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: var(--accent-brown);
            border-radius: 50%;
            padding: 20px;
        }
        
        /* Featured Room Cards */
        .featured-card {
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .featured-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, transparent 60%, rgba(2,0,13,0.8) 100%);
            transition: all 0.3s ease;
        }
        
        .featured-card:hover::after {
            background: linear-gradient(to bottom, transparent 40%, rgba(166,94,70,0.9) 100%);
        }
        
        .featured-card .card-body {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1;
            color: white;
        }
        
        /* Amenities Section */
        .amenities-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent-brown), var(--warm-tan));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .amenities-icon i {
            font-size: 2.5rem;
            color: white;
        }
    </style>
</head>
<body>