<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php?message=Invalid employee ID&type=error");
    exit();
}

$id = $_GET['id'];
$error = '';
$success = '';

// Fetch employee details
$sql = "SELECT * FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: ../index.php?message=Employee not found&type=error");
    exit();
}

$employee = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $department = sanitizeInput($_POST['department']);
    $position = sanitizeInput($_POST['position']);
    $salary = sanitizeInput($_POST['salary']);
    $hire_date = sanitizeInput($_POST['hire_date']);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($phone) || empty($department) || empty($position) || empty($salary) || empty($hire_date)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Update database
        $sql = "UPDATE employees SET name=?, email=?, phone=?, department=?, position=?, salary=?, hire_date=? WHERE id=?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssdsi", $name, $email, $phone, $department, $position, $salary, $hire_date, $id);
        
        if ($stmt->execute()) {
            $success = "Employee updated successfully!";
            // Update the employee array with new values
            $employee = array(
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'department' => $department,
                'position' => $position,
                'salary' => $salary,
                'hire_date' => $hire_date,
                'id' => $id
            );
        } else {
            if ($conn->errno == 1062) { // Duplicate email
                $error = "Email already exists! Please use a different email.";
            } else {
                $error = "Error updating employee: " . $conn->error;
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee | Employee Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght=700&display=swap" rel="stylesheet">
    <style>
        /* ===== Enhanced Header Styles ===== */
        .main-header {
            background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #3949ab 100%);
            box-shadow: 0 4px 20px rgba(26, 35, 126, 0.3);
            position: relative;
            overflow: hidden;
            border-bottom: 4px solid #ff9800;
        }

        .main-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            z-index: 1;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 80px;
            position: relative;
            z-index: 2;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 20px;
            flex: 1;
        }

        .logo-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .logo-wrapper:hover {
            transform: translateY(-2px);
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(255, 152, 0, 0.4);
            position: relative;
            overflow: hidden;
        }

        .logo-icon::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .logo-icon i {
            font-size: 28px;
            color: white;
            position: relative;
            z-index: 2;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-main {
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: white;
            line-height: 1.2;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .logo-sub {
            font-size: 12px;
            color: #c5cae9;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .system-title {
            padding-left: 25px;
            border-left: 2px solid rgba(255, 255, 255, 0.2);
            margin-left: 25px;
        }

        .system-title span {
            font-size: 18px;
            color: white;
            font-weight: 600;
            opacity: 0.9;
        }

        .nav-section {
            display: flex;
            align-items: center;
            gap: 5px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 8px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            text-decoration: none;
            color: #e8eaf6;
            font-weight: 500;
            font-size: 15px;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.6s;
        }

        .nav-item:hover::before {
            left: 100%;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateY(-1px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.4);
        }

        .nav-item i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-left: 30px;
        }

        .current-page {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            border-left: 4px solid #ff9800;
        }

        .current-page i {
            color: #ff9800;
            font-size: 18px;
        }

        .current-page span {
            color: white;
            font-weight: 500;
            font-size: 14px;
        }

        /* ===== Page Header Styles ===== */
        .page-header {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            padding: 40px 0;
            position: relative;
            border-bottom: 1px solid #ffcc80;
        }

        .page-header::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff9800, #ffb74d, #ffcc80);
        }

        .page-header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title-section {
            flex: 1;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #5d4037;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-title i {
            color: #ff9800;
            font-size: 36px;
        }

        .page-subtitle {
            color: #8d6e63;
            font-size: 16px;
            margin-bottom: 0;
        }

        .employee-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ff9800;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-left: 15px;
        }

        .page-actions {
            display: flex;
            gap: 15px;
        }

        /* ===== Main Content Styles ===== */
        .main-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 30px;
        }

        .alert-container {
            margin-bottom: 30px;
        }

        .alert {
            padding: 20px 25px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideIn 0.4s ease-out;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-left: 4px solid #28a745;
            color: #155724;
        }

        .alert-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border-left: 4px solid #dc3545;
            color: #721c24;
        }

        .alert i {
            font-size: 24px;
        }

        .alert-content {
            flex: 1;
        }

        .alert-content h4 {
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: 600;
        }

        .alert-content p {
            margin: 0;
            font-size: 15px;
        }

        .alert-close {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            font-size: 18px;
            opacity: 0.7;
            transition: opacity 0.3s ease;
            padding: 5px;
            border-radius: 6px;
        }

        .alert-close:hover {
            opacity: 1;
            background: rgba(0, 0, 0, 0.1);
        }

        /* ===== Profile Header Card ===== */
        .profile-header-card {
            background: linear-gradient(135deg, #ffffff 0%, #f5f5f5 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-header-content {
            display: flex;
            align-items: center;
            gap: 25px;
            flex-wrap: wrap;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            position: relative;
            box-shadow: 0 8px 25px rgba(255, 152, 0, 0.3);
        }

        .profile-status {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 20px;
            height: 20px;
            background: #4caf50;
            border-radius: 50%;
            border: 3px solid white;
        }

        .profile-info {
            flex: 1;
            min-width: 300px;
        }

        .profile-name {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .profile-title {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
        }

        .profile-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 15px;
            background: #f5f5f5;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .meta-item i {
            color: #ff9800;
        }

        .meta-item.id {
            background: #e3f2fd;
            color: #1976d2;
        }

        .meta-item.dept {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .meta-item.date {
            background: #e8f5e8;
            color: #388e3c;
        }

        /* ===== Form Card Styles ===== */
        .form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            animation: fadeInUp 0.6s ease-out 0.2s both;
        }

        .form-header {
            background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
            color: white;
            padding: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .form-header-content h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .form-header-content h2 i {
            font-size: 28px;
        }

        .form-header-content p {
            margin: 0;
            opacity: 0.9;
            font-size: 15px;
        }

        .form-progress {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.2);
            padding: 15px 20px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .progress-text {
            font-weight: 500;
            font-size: 14px;
        }

        .progress-bar {
            width: 200px;
            height: 8px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: white;
            border-radius: 4px;
            animation: progressLoad 1.5s ease-out;
        }

        @keyframes progressLoad {
            from { width: 0%; }
            to { width: 100%; }
        }

        /* ===== Form Body Styles ===== */
        .form-body {
            padding: 40px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ffcc80;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: #ff9800;
            font-size: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #5d4037;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: #ff9800;
            width: 20px;
            text-align: center;
        }

        .form-label .required {
            color: #e53e3e;
            font-size: 18px;
        }

        .input-with-icon {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 16px;
        }

        .form-control {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            color: #333;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff9800;
            box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.1);
        }

        .form-control::placeholder {
            color: #a0aec0;
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper select {
            appearance: none;
            padding-right: 45px;
        }

        .select-arrow {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            pointer-events: none;
            font-size: 14px;
        }

        .form-hint {
            font-size: 13px;
            color: #666;
            margin-top: 8px;
            font-style: italic;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-hint i {
            color: #ff9800;
            font-size: 12px;
        }

        /* ===== Form Footer Styles ===== */
        .form-footer {
            padding: 30px 40px;
            background: #fff8e1;
            border-top: 1px solid #ffecb3;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff9800 0%, #ff5722 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 152, 0, 0.4);
        }

        .btn-outline {
            background: white;
            border: 2px solid #ffcc80;
            color: #5d4037;
        }

        .btn-outline:hover {
            background: #fff3e0;
            border-color: #ffb74d;
        }

        .btn-lg {
            padding: 16px 32px;
            font-size: 16px;
        }

        .form-info {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            border-left: 4px solid #ff9800;
            border: 1px solid #ffcc80;
        }

        .form-info i {
            color: #ff9800;
            font-size: 18px;
            margin-top: 2px;
        }

        .form-info p {
            margin: 0;
            color: #5d4037;
            font-size: 14px;
        }

        .form-info p strong {
            color: #333;
        }

        /* ===== Footer Styles ===== */
        .main-footer {
            background: #1a202c;
            color: white;
            padding: 40px 0;
            margin-top: 60px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }

        .footer-logo i {
            color: #ff9800;
            font-size: 24px;
        }

        .footer-logo span {
            font-size: 16px;
            font-weight: 600;
            color: #e2e8f0;
        }

        .footer-info {
            text-align: center;
            flex: 1;
        }

        .footer-info p {
            margin: 5px 0;
            color: #a0aec0;
            font-size: 14px;
        }

        .footer-links {
            display: flex;
            gap: 20px;
        }

        .footer-links a {
            color: #cbd5e0;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
        }

        /* ===== Change Detection ===== */
        .form-control.changed {
            border-color: #4caf50;
            background-color: #f8fff8;
        }

        .save-indicator {
            position: fixed;
            top: 100px;
            right: 30px;
            background: #4caf50;
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
            animation: slideInRight 0.3s ease-out;
            z-index: 1000;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 1024px) {
            .nav-section {
                display: none;
            }
            
            .header-container {
                flex-wrap: wrap;
                padding: 15px 20px;
            }
            
            .system-title {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .header-container {
                justify-content: center;
                text-align: center;
            }
            
            .logo-section {
                justify-content: center;
                flex-direction: column;
                gap: 10px;
            }
            
            .page-header-container {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .profile-header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-meta {
                justify-content: center;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-footer {
                flex-direction: column;
                text-align: center;
            }
            
            .form-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .logo-icon {
                width: 50px;
                height: 50px;
            }
            
            .logo-icon i {
                font-size: 22px;
            }
            
            .logo-main {
                font-size: 20px;
            }
            
            .page-title {
                font-size: 24px;
                flex-direction: column;
                gap: 10px;
            }
            
            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 32px;
            }
            
            .form-header {
                padding: 20px;
            }
            
            .form-header-content h2 {
                font-size: 20px;
            }
            
            .form-body {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <!-- Main Header -->
    <header class="main-header">
        <div class="header-container">
            <div class="logo-section">
                <a href="../index.php" class="logo-wrapper">
                    <div class="logo-icon">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div class="logo-text">
                        <span class="logo-main">EMPLOYEE MANAGER</span>
                        <span class="logo-sub">Professional HR System</span>
                    </div>
                </a>
                <div class="system-title">
                    <span>Employee Management System</span>
                </div>
            </div>
            
            <nav class="nav-section">
                <a href="../index.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="add_employee.php" class="nav-item">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Employee</span>
                </a>
                <a href="edit_employee.php?id=<?php echo $employee['id']; ?>" class="nav-item active">
                    <i class="fas fa-user-edit"></i>
                    <span>Edit Employee</span>
                </a>
                <a href="../index.php" class="nav-item">
                    <i class="fas fa-list"></i>
                    <span>View All</span>
                </a>
            </nav>
            
            <div class="user-section">
                <div class="current-page">
                    <i class="fas fa-edit"></i>
                    <span>Editing Employee #<?php echo $employee['id']; ?></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-container">
            <div class="page-title-section">
                <h1 class="page-title">
                    <i class="fas fa-user-edit"></i>
                    Edit Employee Profile
                    <span class="employee-badge">
                        <i class="fas fa-id-card"></i>
                        ID: #<?php echo $employee['id']; ?>
                    </span>
                </h1>
                <p class="page-subtitle">Update information for <?php echo htmlspecialchars($employee['name']); ?> from the <?php echo htmlspecialchars($employee['department']); ?> department</p>
            </div>
            <div class="page-actions">
                <a href="view_employee.php?id=<?php echo $employee['id']; ?>" class="btn btn-outline">
                    <i class="fas fa-eye"></i> View Profile
                </a>
                <a href="../index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Profile Header Card -->
        <div class="profile-header-card">
            <div class="profile-header-content">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                    <div class="profile-status"></div>
                </div>
                <div class="profile-info">
                    <h2 class="profile-name">
                        <?php echo htmlspecialchars($employee['name']); ?>
                        <span style="font-size: 16px; color: #666; font-weight: normal;">
                            (<?php echo htmlspecialchars($employee['position']); ?>)
                        </span>
                    </h2>
                    <p class="profile-title">Last updated: <?php echo date('F j, Y', strtotime($employee['created_at'])); ?></p>
                    
                    <div class="profile-meta">
                        <div class="meta-item id">
                            <i class="fas fa-hashtag"></i>
                            <span>ID: #<?php echo $employee['id']; ?></span>
                        </div>
                        <div class="meta-item dept">
                            <i class="fas fa-building"></i>
                            <span><?php echo htmlspecialchars($employee['department']); ?> Department</span>
                        </div>
                        <div class="meta-item date">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Joined: <?php echo formatDate($employee['hire_date']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($error || $success): ?>
        <div class="alert-container">
            <?php if ($error): ?>
            <div class="alert alert-error">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-content">
                    <h4>Update Failed!</h4>
                    <p><?php echo $error; ?></p>
                </div>
                <button class="alert-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-content">
                    <h4>Update Successful!</h4>
                    <p><?php echo $success; ?></p>
                </div>
                <div class="alert-actions" style="display: flex; gap: 10px;">
                    <a href="view_employee.php?id=<?php echo $employee['id']; ?>" class="btn btn-outline" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-eye"></i> View Profile
                    </a>
                    <a href="../index.php" class="btn btn-outline" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-users"></i> All Employees
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="form-card">
            <div class="form-header">
                <div class="form-header-content">
                    <h2>
                        <i class="fas fa-edit"></i>
                        Update Employee Information
                    </h2>
                    <p>Modify the details below and click Save Changes to update the employee record</p>
                </div>
                <div class="form-progress">
                    <span class="progress-text">Review all fields</span>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                </div>
            </div>

            <form method="POST" action="" class="employee-form" id="editForm">
                <div class="form-body">
                    <!-- Personal Details Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-user-circle"></i>
                            Personal Information
                        </h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Full Name <span class="required">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           value="<?php echo htmlspecialchars($employee['name']); ?>" 
                                           required
                                           class="form-control">
                                </div>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Legal name as per official documents
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address <span class="required">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           value="<?php echo htmlspecialchars($employee['email']); ?>" 
                                           required
                                           class="form-control">
                                </div>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Official company email address
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone"></i>
                                    Phone Number <span class="required">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="tel" 
                                           id="phone" 
                                           name="phone" 
                                           value="<?php echo htmlspecialchars($employee['phone']); ?>" 
                                           required
                                           class="form-control">
                                </div>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Include country and area code
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Employment Details Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-briefcase"></i>
                            Employment Details
                        </h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="department" class="form-label">
                                    <i class="fas fa-building"></i>
                                    Department <span class="required">*</span>
                                </label>
                                <div class="select-wrapper">
                                    <select id="department" name="department" required class="form-control">
                                        <option value="IT" <?php echo ($employee['department'] == 'IT') ? 'selected' : ''; ?>>Information Technology</option>
                                        <option value="HR" <?php echo ($employee['department'] == 'HR') ? 'selected' : ''; ?>>Human Resources</option>
                                        <option value="Finance" <?php echo ($employee['department'] == 'Finance') ? 'selected' : ''; ?>>Finance & Accounting</option>
                                        <option value="Marketing" <?php echo ($employee['department'] == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                        <option value="Sales" <?php echo ($employee['department'] == 'Sales') ? 'selected' : ''; ?>>Sales</option>
                                        <option value="Operations" <?php echo ($employee['department'] == 'Operations') ? 'selected' : ''; ?>>Operations</option>
                                    </select>
                                    <i class="fas fa-chevron-down select-arrow"></i>
                                </div>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Primary work department
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="position" class="form-label">
                                    <i class="fas fa-user-tie"></i>
                                    Position <span class="required">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user-tie input-icon"></i>
                                    <input type="text" 
                                           id="position" 
                                           name="position" 
                                           value="<?php echo htmlspecialchars($employee['position']); ?>" 
                                           required
                                           class="form-control">
                                </div>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Current job title
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="salary" class="form-label">
                                    <i class="fas fa-money-bill-wave"></i>
                                    Salary <span class="required">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i class="fas fa-dollar-sign input-icon"></i>
                                    <input type="number" 
                                           id="salary" 
                                           name="salary" 
                                           step="0.01" 
                                           min="0" 
                                           value="<?php echo $employee['salary']; ?>" 
                                           required
                                           class="form-control">
                                </div>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Annual gross salary in USD
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="hire_date" class="form-label">
                                    <i class="fas fa-calendar-alt"></i>
                                    Hire Date <span class="required">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <i class="fas fa-calendar-alt input-icon"></i>
                                    <input type="date" 
                                           id="hire_date" 
                                           name="hire_date" 
                                           value="<?php echo $employee['hire_date']; ?>" 
                                           required
                                           class="form-control">
                                </div>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Original employment start date
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-footer">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg" id="saveButton">
                            <i class="fas fa-save"></i>
                            <span>Save Changes</span>
                        </button>
                        <a href="view_employee.php?id=<?php echo $employee['id']; ?>" class="btn btn-outline">
                            <i class="fas fa-times"></i>
                            <span>Cancel</span>
                        </a>
                    </div>
                    
                    <div class="form-info">
                        <i class="fas fa-history"></i>
                        <p><strong>Last Modified:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($employee['created_at'])); ?></p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <a href="../index.php" class="footer-logo">
                <i class="fas fa-users"></i>
                <span>Employee Management System</span>
            </a>
            <div class="footer-info">
                <p>&copy; <?php echo date('Y'); ?> Employee Management System. All rights reserved.</p>
                <p>A professional HR management solution</p>
            </div>
            <div class="footer-links">
                <a href="../index.php">Dashboard</a>
                <a href="add_employee.php">Add Employee</a>
                <a href="../index.php">View All</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Store original form values
            const form = document.getElementById('editForm');
            const originalValues = {};
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                originalValues[input.name] = input.value;
            });
            
            // Phone number formatting
            const phoneInput = document.getElementById('phone');
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 10) {
                    value = value.substring(0, 10);
                }
                
                if (value.length > 6) {
                    value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                } else if (value.length > 3) {
                    value = value.replace(/(\d{3})(\d{1,3})/, '($1) $2');
                } else if (value.length > 0) {
                    value = value.replace(/(\d{1,3})/, '($1');
                }
                
                e.target.value = value;
            });
            
            // Salary formatting
            const salaryInput = document.getElementById('salary');
            salaryInput.addEventListener('blur', function(e) {
                let value = parseFloat(e.target.value);
                if (!isNaN(value)) {
                    e.target.value = value.toFixed(2);
                }
            });
            
            // Highlight changed fields
            function highlightChanges() {
                inputs.forEach(input => {
                    if (input.name && originalValues[input.name] !== input.value) {
                        input.classList.add('changed');
                    } else {
                        input.classList.remove('changed');
                    }
                });
            }
            
            // Add change detection
            form.addEventListener('input', highlightChanges);
            form.addEventListener('change', highlightChanges);
            
            // Check for changes on page load
            highlightChanges();
            
            // Warn before leaving if unsaved changes
            let hasUnsavedChanges = false;
            
            form.addEventListener('input', () => {
                hasUnsavedChanges = true;
            });
            
            window.addEventListener('beforeunload', (e) => {
                if (hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                }
            });
            
            // Reset hasUnsavedChanges on form submit
            form.addEventListener('submit', () => {
                hasUnsavedChanges = false;
            });
            
            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const requiredFields = form.querySelectorAll('.required');
                
                requiredFields.forEach(function(span) {
                    const input = span.closest('.form-label').nextElementSibling.querySelector('.form-control');
                    if (!input.value.trim()) {
                        isValid = false;
                        input.style.borderColor = '#e53e3e';
                        input.style.boxShadow = '0 0 0 3px rgba(229, 62, 62, 0.1)';
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    // Show error message
                    if (!document.querySelector('.validation-error')) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-error validation-error';
                        errorDiv.innerHTML = `
                            <div class="alert-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="alert-content">
                                <h4>Validation Error!</h4>
                                <p>Please fill in all required fields marked with *</p>
                            </div>
                            <button class="alert-close" onclick="this.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        document.querySelector('.alert-container').prepend(errorDiv);
                        
                        // Scroll to error
                        setTimeout(() => {
                            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 100);
                    }
                } else {
                    // Show saving indicator
                    const saveButton = document.getElementById('saveButton');
                    const originalText = saveButton.querySelector('span').textContent;
                    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Saving...</span>';
                    saveButton.disabled = true;
                    
                    // Simulate save delay
                    setTimeout(() => {
                        saveButton.innerHTML = `<i class="fas fa-check"></i><span>${originalText}</span>`;
                        saveButton.disabled = false;
                    }, 1500);
                }
            });
            
            // Clear validation styles on input
            form.addEventListener('input', function(e) {
                if (e.target.classList.contains('form-control')) {
                    e.target.style.borderColor = '#e0e0e0';
                    e.target.style.boxShadow = 'none';
                }
            });
            
            // Show success message after form submission if there was an error before
            if (window.location.hash === '#success') {
                setTimeout(() => {
                    const successAlert = document.querySelector('.alert-success');
                    if (successAlert) {
                        successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }, 300);
            }
        });
        
        // Add shine animation to logo
        setInterval(() => {
            const logoIcon = document.querySelector('.logo-icon');
            logoIcon.style.animation = 'none';
            setTimeout(() => {
                logoIcon.style.animation = 'shine 3s infinite';
            }, 10);
        }, 3000);
    </script>
</body>
</html>

<?php
$conn->close();
?>