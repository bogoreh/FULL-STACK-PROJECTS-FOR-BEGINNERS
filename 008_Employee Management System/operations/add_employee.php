<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$error = '';
$success = '';

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
        $error = "All fields are required! Please fill in all the information.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format! Please enter a valid email address.";
    } elseif (strlen($phone) < 10) {
        $error = "Phone number must be at least 10 digits long!";
    } elseif ($salary <= 0) {
        $error = "Salary must be greater than zero!";
    } else {
        // Insert into database
        $sql = "INSERT INTO employees (name, email, phone, department, position, salary, hire_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssds", $name, $email, $phone, $department, $position, $salary, $hire_date);
        
        if ($stmt->execute()) {
            $employee_id = $stmt->insert_id;
            $success = "Employee added successfully!";
            $_POST = array();
        } else {
            if ($conn->errno == 1062) { // Duplicate email
                $error = "Email already exists! Please use a different email address.";
            } else {
                $error = "Error adding employee: " . $conn->error;
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
    <title>Add New Employee | Employee Management System</title>
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
            border-bottom: 4px solid #5c6bc0;
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
            background: linear-gradient(135deg, #7986cb 0%, #5c6bc0 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(92, 107, 192, 0.4);
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
            background: linear-gradient(135deg, #7986cb 0%, #5c6bc0 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(92, 107, 192, 0.4);
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
            border-left: 4px solid #5c6bc0;
        }

        .current-page i {
            color: #7986cb;
            font-size: 18px;
        }

        .current-page span {
            color: white;
            font-weight: 500;
            font-size: 14px;
        }

        /* ===== Page Header Styles ===== */
        .page-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 40px 0;
            position: relative;
            border-bottom: 1px solid #dee2e6;
        }

        .page-header::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #5c6bc0, #7986cb, #9fa8da);
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
            color: #2d3748;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-title i {
            color: #5c6bc0;
            font-size: 36px;
        }

        .page-subtitle {
            color: #718096;
            font-size: 16px;
            margin-bottom: 0;
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

        /* ===== Form Card Styles ===== */
        .form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #e2e8f0;
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

        .form-header {
            background: linear-gradient(135deg, #5c6bc0 0%, #7986cb 100%);
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
            color: #2d3748;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: #5c6bc0;
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
            color: #4a5568;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: #5c6bc0;
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
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            color: #2d3748;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #5c6bc0;
            box-shadow: 0 0 0 3px rgba(92, 107, 192, 0.1);
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
            color: #718096;
            margin-top: 8px;
            font-style: italic;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-hint i {
            color: #5c6bc0;
            font-size: 12px;
        }

        /* ===== Form Footer Styles ===== */
        .form-footer {
            padding: 30px 40px;
            background: #f8f9fa;
            border-top: 1px solid #e2e8f0;
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
            background: linear-gradient(135deg, #5c6bc0 0%, #7986cb 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(92, 107, 192, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(92, 107, 192, 0.4);
        }

        .btn-outline {
            background: white;
            border: 2px solid #e2e8f0;
            color: #4a5568;
        }

        .btn-outline:hover {
            background: #f8f9fa;
            border-color: #cbd5e0;
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
            border-left: 4px solid #5c6bc0;
        }

        .form-info i {
            color: #5c6bc0;
            font-size: 18px;
            margin-top: 2px;
        }

        .form-info p {
            margin: 0;
            color: #4a5568;
            font-size: 14px;
        }

        .form-info p strong {
            color: #2d3748;
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
            color: #5c6bc0;
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
                        <i class="fas fa-users"></i>
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
                <a href="add_employee.php" class="nav-item active">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Employee</span>
                </a>
                <a href="../index.php" class="nav-item">
                    <i class="fas fa-list"></i>
                    <span>View All</span>
                </a>
                <a href="../index.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </nav>
            
            <div class="user-section">
                <div class="current-page">
                    <i class="fas fa-edit"></i>
                    <span>Adding New Employee</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-container">
            <div class="page-title-section">
                <h1 class="page-title">
                    <i class="fas fa-user-plus"></i>
                    Add New Employee
                </h1>
                <p class="page-subtitle">Register a new team member by filling in the details below</p>
            </div>
            <div class="page-actions">
                <a href="../index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Alert Messages -->
        <?php if ($error || $success): ?>
        <div class="alert-container">
            <?php if ($error): ?>
            <div class="alert alert-error">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-content">
                    <h4>Error!</h4>
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
                    <h4>Success!</h4>
                    <p><?php echo $success; ?></p>
                    <?php if (isset($employee_id)): ?>
                    <p><strong>Employee ID: #<?php echo $employee_id; ?></strong></p>
                    <?php endif; ?>
                </div>
                <div class="alert-actions" style="display: flex; gap: 10px;">
                    <a href="../index.php" class="btn btn-outline" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-users"></i> View All
                    </a>
                    <a href="add_employee.php" class="btn btn-outline" style="padding: 8px 16px; font-size: 14px;">
                        <i class="fas fa-plus"></i> Add Another
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
                        <i class="fas fa-id-card"></i>
                        Employee Information Form
                    </h2>
                    <p>Please fill in all required fields marked with <span style="color: #e53e3e;">*</span></p>
                </div>
                <div class="form-progress">
                    <span class="progress-text">Complete all sections</span>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                </div>
            </div>

            <form method="POST" action="" class="employee-form">
                <div class="form-body">
                    <!-- Personal Details Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-user-circle"></i>
                            Personal Details
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
                                           value="<?php echo $_POST['name'] ?? ''; ?>" 
                                           placeholder="Enter employee's full name" 
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
                                           value="<?php echo $_POST['email'] ?? ''; ?>" 
                                           placeholder="employee@company.com" 
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
                                           value="<?php echo $_POST['phone'] ?? ''; ?>" 
                                           placeholder="+1 (234) 567-8900" 
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
                                        <option value="">Select Department</option>
                                        <option value="IT" <?php echo (isset($_POST['department']) && $_POST['department'] == 'IT') ? 'selected' : ''; ?>>Information Technology</option>
                                        <option value="HR" <?php echo (isset($_POST['department']) && $_POST['department'] == 'HR') ? 'selected' : ''; ?>>Human Resources</option>
                                        <option value="Finance" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Finance') ? 'selected' : ''; ?>>Finance & Accounting</option>
                                        <option value="Marketing" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                        <option value="Sales" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Sales') ? 'selected' : ''; ?>>Sales</option>
                                        <option value="Operations" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Operations') ? 'selected' : ''; ?>>Operations</option>
                                    </select>
                                    <i class="fas fa-chevron-down select-arrow"></i>
                                </div>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Choose the primary department
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
                                           value="<?php echo $_POST['position'] ?? ''; ?>" 
                                           placeholder="e.g., Senior Developer" 
                                           required
                                           class="form-control">
                                </div>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Official job title
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
                                           value="<?php echo $_POST['salary'] ?? ''; ?>" 
                                           placeholder="0.00" 
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
                                           value="<?php echo $_POST['hire_date'] ?? date('Y-m-d'); ?>" 
                                           required
                                           class="form-control">
                                </div>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Official employment start date
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-footer">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i>
                            <span>Add Employee</span>
                        </button>
                        <button type="reset" class="btn btn-outline">
                            <i class="fas fa-redo"></i>
                            <span>Clear Form</span>
                        </button>
                        <a href="../index.php" class="btn btn-outline">
                            <i class="fas fa-times"></i>
                            <span>Cancel</span>
                        </a>
                    </div>
                    
                    <div class="form-info">
                        <i class="fas fa-info-circle"></i>
                        <p><strong>Note:</strong> All fields marked with <span style="color: #e53e3e;">*</span> are required. Employee ID will be automatically generated upon successful submission.</p>
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
            
            // Set max date for hire date (today)
            const hireDateInput = document.getElementById('hire_date');
            const today = new Date().toISOString().split('T')[0];
            hireDateInput.max = today;
            
            // Form validation
            const form = document.querySelector('.employee-form');
            const requiredFields = form.querySelectorAll('.required');
            
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
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
                }
            });
            
            // Clear validation styles on input
            form.addEventListener('input', function(e) {
                if (e.target.classList.contains('form-control')) {
                    e.target.style.borderColor = '#e2e8f0';
                    e.target.style.boxShadow = 'none';
                }
            });
            
            // Reset form
            form.addEventListener('reset', function() {
                const inputs = form.querySelectorAll('.form-control');
                inputs.forEach(function(input) {
                    input.style.borderColor = '#e2e8f0';
                    input.style.boxShadow = 'none';
                });
                
                // Remove any validation errors
                const validationError = document.querySelector('.validation-error');
                if (validationError) {
                    validationError.remove();
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