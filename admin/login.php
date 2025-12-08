<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields!";
    } else {
        $stmt = $conn->prepare("SELECT admin_id, username, password FROM admin WHERE username = ?");
        $stmt->bindValue(1, $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($row) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $row['admin_id'];
                $_SESSION['admin_username'] = $row['username'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "Username not found!";
        }
    }
}

$page_title = "Admin Login";
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--secondary-dark) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--accent-brown) 0%, var(--warm-tan) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .logo-container {
            width: 100px; 
            height: 100px;
            border-radius: 50%;
            overflow: hidden;      /* Important – clips the image */
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;      /* optional background if image has transparency */
        }

        
        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;      /* This forces it to fill the container */
        }
                
        .login-body {
            padding: 40px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--secondary-dark);
            margin-bottom: 8px;
        }
        
        .form-control {
            background: var(--light-cream);
            border: 2px solid var(--light-cream);
            color: var(--primary-dark);
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: white;
            border-color: var(--accent-brown);
            color: var(--primary-dark);
            box-shadow: 0 0 0 0.2rem rgba(166, 94, 70, 0.25);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #999;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--accent-brown) 0%, var(--warm-tan) 100%);
            border: none;
            color: white;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border-radius: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(166, 94, 70, 0.4);
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: var(--light-cream);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-link a:hover {
            color: var(--warm-tan);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }
        
        .default-credentials {
            background: var(--light-cream);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--secondary-dark);
            margin-top: 20px;
        }
        
        .default-credentials strong {
            color: var(--accent-brown);
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="logo-container">
                <img src="../uploads/room_images/zaid-logo.png" alt="ZAID HOTEL">
            </div>
            <h3>ADMIN ACCESS</h3>
            <p class="mb-0 mt-2">Hotel Management System</p>
        </div>
        
        <div class="login-body">
            <?php if (!empty($error)): ?>
            <div class="alert alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="username" class="form-label">
                        <i class="bi bi-person-circle"></i> Username
                    </label>
                    <input type="text" 
                           class="form-control form-control-lg" 
                           id="username" 
                           name="username" 
                           placeholder="Enter your username"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           required 
                           autofocus>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="bi bi-key-fill"></i> Password
                    </label>
                    <input type="password" 
                           class="form-control form-control-lg" 
                           id="password" 
                           name="password" 
                           placeholder="Enter your password"
                           required>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg btn-login">
                        <i class="bi bi-box-arrow-in-right"></i> Login to Dashboard
                    </button>
                </div>
                
                <div class="default-credentials">
                    <i class="bi bi-info-circle-fill"></i> 
                    <strong>Default:</strong> admin / admin123
                </div>
            </form>
        </div>
    </div>
    
    <div class="back-link">
        <a href="../user/index.php">
            <i class="bi bi-arrow-left-circle-fill"></i> Back to Website
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>