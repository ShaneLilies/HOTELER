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
$debug_mode = false; // Set to true only for debugging

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields!";
    } else {
        // Prepare statement
        $stmt = $conn->prepare("SELECT admin_id, username, password FROM admin WHERE username = ?");
        $stmt->bindValue(1, $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($row) {
            // Verify password
            if (password_verify($password, $row['password'])) {
                // Login successful
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $row['admin_id'];
                $_SESSION['admin_username'] = $row['username'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password!";
                
                if ($debug_mode) {
                    $error .= " (Hash: " . substr($row['password'], 0, 20) . "...)";
                }
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
    <title><?php echo $page_title; ?> - Hotel Management</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(64, 64, 64, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 80% 50%, rgba(64, 64, 64, 0.3) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .login-container {
            max-width: 480px;
            width: 100%;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        
        .login-card {
            background: linear-gradient(145deg, #2a2a2a, #1f1f1f);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .login-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #333333 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #666, #999, #666);
            border-radius: 10px;
        }
        
        .login-header i {
            font-size: 3.5rem;
            margin-bottom: 15px;
            color: #999;
            text-shadow: 0 0 20px rgba(153, 153, 153, 0.3);
        }
        
        .login-header h3 {
            margin: 0;
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: #e0e0e0;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }
        
        .login-header p {
            margin-top: 8px;
            color: #999;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }
        
        .login-body {
            padding: 45px 35px;
            background: #242424;
        }
        
        .form-label {
            font-weight: 600;
            color: #b0b0b0;
            margin-bottom: 10px;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .form-label i {
            color: #888;
            margin-right: 5px;
        }
        
        .form-control {
            background: #1a1a1a;
            border: 1px solid #3a3a3a;
            color: #ffffff;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: #1f1f1f;
            border-color: #666;
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(102, 102, 102, 0.15), 0 0 20px rgba(102, 102, 102, 0.1);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #555;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #3a3a3a 0%, #2a2a2a 100%);
            border: 1px solid #4a4a4a;
            color: #ffffff;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            border-radius: 10px;
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #4a4a4a 0%, #3a3a3a 100%);
            border-color: #666;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        }
        
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        
        .back-link a {
            color: #999;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-link a:hover {
            color: #ccc;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #3d1f1f 0%, #2d1515 100%);
            color: #ffcccc;
            border-left: 4px solid #ff6666;
            box-shadow: 0 4px 15px rgba(255, 102, 102, 0.2);
        }
        
        .alert i {
            color: #ff6666;
        }
        
        .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.6;
        }
        
        .default-credentials {
            background: linear-gradient(135deg, #2a2a2a 0%, #1f1f1f 100%);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 0.9rem;
            color: #888;
            margin-top: 20px;
            border: 1px solid #3a3a3a;
        }
        
        .default-credentials strong {
            color: #b0b0b0;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-shield-lock-fill"></i>
            <h3>ADMIN ACCESS</h3>
            <p>Hotel Management System</p>
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
                        <i class="bi bi-box-arrow-in-right"></i> Login
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