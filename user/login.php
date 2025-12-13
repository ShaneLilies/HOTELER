<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$page_title = "Login / Register";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - ZAID HOTEL</title>
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
            background: linear-gradient(135deg, var(--secondary-dark) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .auth-container {
            max-width: 500px;
            width: 100%;
        }
        
        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            overflow: hidden;
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--accent-brown) 0%, var(--warm-tan) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .logo-container {
            width: 90px;
            height: 90px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .logo-container img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        
        .auth-header h3 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .auth-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .auth-body {
            padding: 40px;
        }
        
        .nav-pills .nav-link {
            border-radius: 10px;
            font-weight: 600;
            color: var(--secondary-dark);
            transition: all 0.3s ease;
        }
        
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, var(--accent-brown) 0%, var(--warm-tan) 100%);
            color: white;
        }
        
        .form-label {
            color: var(--secondary-dark);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid var(--light-cream);
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-brown);
            box-shadow: 0 0 0 0.2rem rgba(166, 94, 70, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--accent-brown) 0%, var(--warm-tan) 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(166, 94, 70, 0.4);
        }
        
        .admin-link {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: var(--light-cream);
            border-radius: 10px;
        }
        
        .admin-link a {
            color: var(--accent-brown);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .admin-link a:hover {
            color: var(--secondary-dark);
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: var(--light-cream);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .back-link a:hover {
            color: var(--warm-tan);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="logo-container">
                <img src="../uploads/room_images/zaid-logo.png" alt="ZAID HOTEL">
            </div>
            <h3>ZAID HOTEL</h3>
            <p>Guest Portal</p>
        </div>
        
        <div class="auth-body">
            <ul class="nav nav-pills mb-4 justify-content-center" role="tablist">
                <li class="nav-item me-2">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#login-tab">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#register-tab">
                        <i class="bi bi-person-plus"></i> Register
                    </button>
                </li>
            </ul>
            
            <div class="tab-content">
                <!-- Login Tab -->
                <div class="tab-pane fade show active" id="login-tab">
                    <div id="login-message"></div>
                    <form id="login-form">
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-envelope"></i> Email</label>
                            <input type="email" class="form-control" name="email" placeholder="your@email.com" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label"><i class="bi bi-lock"></i> Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Login to Your Account
                        </button>
                    </form>
                </div>
                
                <!-- Register Tab -->
                <div class="tab-pane fade" id="register-tab">
                    <div id="register-message"></div>
                    <form id="register-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" placeholder="John" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" placeholder="Doe" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="your@email.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" placeholder="+1 234 567 8900" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address (Optional)</label>
                            <textarea class="form-control" name="address" rows="2" placeholder="Your address"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Minimum 6 characters" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Re-enter password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-person-plus"></i> Create Account
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="admin-link">
                <i class="bi bi-shield-lock"></i>
                <a href="../admin/login.php">Admin Login</a>
            </div>
        </div>
    </div>
    
    <div class="back-link">
        <a href="index.php"><i class="bi bi-arrow-left-circle"></i> Back to Home</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Login Form Handler
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('auth-handler.php?action=login', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        const msgDiv = document.getElementById('login-message');
        if (data.success) {
            msgDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle"></i> ' + data.message + '</div>';
            setTimeout(() => window.location.href = data.redirect, 1000);
        } else {
            msgDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle"></i> ' + data.message + '</div>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
});

// Register Form Handler
document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('auth-handler.php?action=register', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        const msgDiv = document.getElementById('register-message');
        if (data.success) {
            msgDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle"></i> ' + data.message + '</div>';
            this.reset();
            setTimeout(() => {
                document.querySelector('[data-bs-target="#login-tab"]').click();
                msgDiv.innerHTML = '';
            }, 2000);
        } else {
            msgDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle"></i> ' + data.message + '</div>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
});
</script>
</body>
</html>