<?php
session_start();
require_once '../config/db.php';

$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

// Handle User Registration
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)) {
        $response['message'] = 'All fields except address are required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format!';
    } elseif (strlen($password) < 6) {
        $response['message'] = 'Password must be at least 6 characters!';
    } elseif ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match!';
    } else {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT guest_id FROM guest WHERE email = ?");
        $check_stmt->bindValue(1, $email);
        $check_result = $check_stmt->execute();
        
        if ($check_result->fetchArray()) {
            $response['message'] = 'Email already registered!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new guest
            $insert_stmt = $conn->prepare("INSERT INTO guest (first_name, last_name, email, phone, address, password) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bindValue(1, $first_name);
            $insert_stmt->bindValue(2, $last_name);
            $insert_stmt->bindValue(3, $email);
            $insert_stmt->bindValue(4, $phone);
            $insert_stmt->bindValue(5, $address);
            $insert_stmt->bindValue(6, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Registration successful! Please login.';
            } else {
                $response['message'] = 'Registration failed. Please try again.';
            }
        }
    }
    
    echo json_encode($response);
    exit();
}

// Handle User Login
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $response['message'] = 'Email and password are required!';
    } else {
        $stmt = $conn->prepare("SELECT * FROM guest WHERE email = ?");
        $stmt->bindValue(1, $email);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['guest_id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            
            $response['success'] = true;
            $response['message'] = 'Login successful!';
            $response['redirect'] = 'index.php';
        } else {
            $response['message'] = 'Invalid email or password!';
        }
    }
    
    echo json_encode($response);
    exit();
}

// Handle Logout
if ($action === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>