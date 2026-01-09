<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php?message=Invalid employee ID&type=error");
    exit();
}

$id = $_GET['id'];

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

// Calculate tenure
$hireDate = new DateTime($employee['hire_date']);
$now = new DateTime();
$interval = $hireDate->diff($now);
$tenure = $interval->y . ' years, ' . $interval->m . ' months';

// Calculate salary breakdown
$monthlySalary = $employee['salary'] / 12;
$biWeeklySalary = $employee['salary'] / 26;
$dailySalary = $employee['salary'] / 260;

// Get department color
function getDepartmentColor($department) {
    $colors = [
        'IT' => '#4CAF50',
        'HR' => '#2196F3',
        'Finance' => '#FF9800',
        'Marketing' => '#9C27B0',
        'Sales' => '#F44336',
        'Operations' => '#00BCD4'
    ];
    return $colors[$department] ?? '#607D8B';
}

$deptColor = getDepartmentColor($employee['department']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($employee['name']); ?> | Employee Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght=700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        /* ===== Enhanced Header Styles ===== */
        .main-header {
            background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #3949ab 100%);
            box-shadow: 0 4px 20px rgba(26, 35, 126, 0.3);
            position: relative;
            overflow: hidden;
            border-bottom: 4px solid <?php echo $deptColor; ?>;
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
            background: linear-gradient(135deg, <?php echo $deptColor; ?> 0%, <?php echo adjustColor($deptColor, -30); ?> 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px <?php echo hexToRgba($deptColor, 0.4); ?>;
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
            background: linear-gradient(135deg, <?php echo $deptColor; ?> 0%, <?php echo adjustColor($deptColor, -30); ?> 100%);
            color: white;
            box-shadow: 0 4px 12px <?php echo hexToRgba($deptColor, 0.4); ?>;
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
            border-left: 4px solid <?php echo $deptColor; ?>;
        }

        .current-page i {
            color: <?php echo $deptColor; ?>;
            font-size: 18px;
        }

        .current-page span {
            color: white;
            font-weight: 500;
            font-size: 14px;
        }

        /* ===== Hero Profile Section ===== */
        .hero-profile {
            background: linear-gradient(135deg, <?php echo $deptColor; ?> 0%, <?php echo adjustColor($deptColor, -20); ?> 100%);
            color: white;
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-profile::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 20% 80%, rgba(0, 0, 0, 0.1) 0%, transparent 50%);
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 30px;
            position: relative;
            z-index: 2;
        }

        .profile-hero-content {
            display: flex;
            align-items: center;
            gap: 40px;
            flex-wrap: wrap;
        }

        .profile-avatar-large {
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 70px;
            color: white;
            border: 8px solid rgba(255, 255, 255, 0.3);
            position: relative;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .avatar-initials {
            font-family: 'Montserrat', sans-serif;
            font-size: 72px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .profile-badge {
            position: absolute;
            bottom: 15px;
            right: 15px;
            width: 40px;
            height: 40px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            border: 4px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .profile-hero-info {
            flex: 1;
            min-width: 300px;
        }

        .profile-hero-name {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.1;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .profile-hero-title {
            font-size: 20px;
            opacity: 0.9;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .profile-hero-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .meta-tag {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 25px;
            font-size: 14px;
            font-weight: 500;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .meta-tag i {
            font-size: 16px;
        }

        .hero-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
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
            background: white;
            color: <?php echo $deptColor; ?>;
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(255, 255, 255, 0.4);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.4);
            color: white;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
        }

        /* ===== Main Content ===== */
        .main-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 30px;
        }

        /* ===== Dashboard Cards ===== */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .dashboard-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            padding: 25px 30px;
            background: linear-gradient(to right, #f8fafc, #f1f5f9);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .card-header i {
            font-size: 24px;
            color: <?php echo $deptColor; ?>;
            width: 50px;
            height: 50px;
            background: <?php echo hexToRgba($deptColor, 0.1); ?>;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
        }

        .card-body {
            padding: 30px;
        }

        /* ===== Information Grid ===== */
        .info-grid {
            display: grid;
            gap: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #64748b;
            font-weight: 500;
        }

        .info-label i {
            color: <?php echo $deptColor; ?>;
            width: 20px;
            text-align: center;
        }

        .info-value {
            font-weight: 600;
            color: #2d3748;
            text-align: right;
        }

        .info-value.highlight {
            font-size: 24px;
            color: <?php echo $deptColor; ?>;
        }

        .info-value a {
            color: #3b82f6;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .info-value a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        /* ===== Salary Breakdown ===== */
        .salary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .salary-item {
            text-align: center;
            padding: 20px;
            border-radius: 15px;
            background: #f8fafc;
        }

        .salary-amount {
            font-size: 24px;
            font-weight: 700;
            color: <?php echo $deptColor; ?>;
            margin-bottom: 5px;
        }

        .salary-period {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
        }

        /* ===== Quick Actions ===== */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .action-btn {
            padding: 20px;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }

        .action-btn i {
            font-size: 28px;
        }

        .action-edit {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1d4ed8;
        }

        .action-edit:hover {
            background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
            transform: translateY(-3px);
        }

        .action-delete {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }

        .action-delete:hover {
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            transform: translateY(-3px);
        }

        .action-email {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #16a34a;
        }

        .action-email:hover {
            background: linear-gradient(135deg, #bbf7d0 0%, #86efac 100%);
            transform: translateY(-3px);
        }

        .action-phone {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
        }

        .action-phone:hover {
            background: linear-gradient(135deg, #fde68a 0%, #fcd34d 100%);
            transform: translateY(-3px);
        }

        /* ===== Statistics Card ===== */
        .stats-card {
            background: linear-gradient(135deg, <?php echo $deptColor; ?> 0%, <?php echo adjustColor($deptColor, -20); ?> 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-top: 30px;
            box-shadow: 0 15px 35px <?php echo hexToRgba($deptColor, 0.3); ?>;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ===== Footer ===== */
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
            color: <?php echo $deptColor; ?>;
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

        /* ===== Print Styles ===== */
        @media print {
            .main-header, .hero-actions, .action-grid, .main-footer {
                display: none;
            }
            
            .dashboard-card {
                break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ddd;
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
            
            .profile-hero-content {
                flex-direction: column;
                text-align: center;
                gap: 30px;
            }
            
            .profile-avatar-large {
                width: 150px;
                height: 150px;
                font-size: 60px;
            }
            
            .profile-hero-name {
                font-size: 36px;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .salary-grid,
            .action-grid {
                grid-template-columns: 1fr;
            }
            
            .hero-actions {
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
            
            .profile-hero-name {
                font-size: 28px;
            }
            
            .profile-avatar-large {
                width: 120px;
                height: 120px;
                font-size: 50px;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .profile-hero-meta {
                justify-content: center;
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
                        <i class="fas fa-user-circle"></i>
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
                <a href="view_employee.php?id=<?php echo $employee['id']; ?>" class="nav-item active">
                    <i class="fas fa-eye"></i>
                    <span>View Profile</span>
                </a>
                <a href="../index.php" class="nav-item">
                    <i class="fas fa-list"></i>
                    <span>View All</span>
                </a>
            </nav>
            
            <div class="user-section">
                <div class="current-page">
                    <i class="fas fa-id-card"></i>
                    <span>Profile View - ID: #<?php echo $employee['id']; ?></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Profile Section -->
    <div class="hero-profile">
        <div class="hero-container">
            <div class="profile-hero-content">
                <div class="profile-avatar-large">
                    <div class="avatar-initials">
                        <?php 
                        // Get initials from name
                        $nameParts = explode(' ', $employee['name']);
                        $initials = '';
                        foreach ($nameParts as $part) {
                            $initials .= strtoupper(substr($part, 0, 1));
                            if (strlen($initials) >= 2) break;
                        }
                        echo $initials;
                        ?>
                    </div>
                    <div class="profile-badge">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                
                <div class="profile-hero-info">
                    <h1 class="profile-hero-name">
                        <?php echo htmlspecialchars($employee['name']); ?>
                    </h1>
                    <p class="profile-hero-title">
                        <?php echo htmlspecialchars($employee['position']); ?>
                        <span style="opacity: 0.8;"> • <?php echo htmlspecialchars($employee['department']); ?> Department</span>
                    </p>
                    
                    <div class="profile-hero-meta">
                        <div class="meta-tag">
                            <i class="fas fa-hashtag"></i>
                            <span>ID: #<?php echo $employee['id']; ?></span>
                        </div>
                        <div class="meta-tag">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Joined: <?php echo formatDate($employee['hire_date']); ?></span>
                        </div>
                        <div class="meta-tag">
                            <i class="fas fa-history"></i>
                            <span>Tenure: <?php echo $tenure; ?></span>
                        </div>
                    </div>
                    
                    <div class="hero-actions">
                        <a href="edit_employee.php?id=<?php echo $employee['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i>
                            <span>Edit Profile</span>
                        </a>
                        <a href="mailto:<?php echo htmlspecialchars($employee['email']); ?>" class="btn btn-outline">
                            <i class="fas fa-envelope"></i>
                            <span>Send Email</span>
                        </a>
                        <a href="../index.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back to List</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <!-- Personal Information Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-user-circle"></i>
                    <h3>Personal Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-envelope"></i>
                                <span>Email Address</span>
                            </div>
                            <div class="info-value">
                                <a href="mailto:<?php echo htmlspecialchars($employee['email']); ?>">
                                    <?php echo htmlspecialchars($employee['email']); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-phone"></i>
                                <span>Phone Number</span>
                            </div>
                            <div class="info-value">
                                <a href="tel:<?php echo htmlspecialchars($employee['phone']); ?>">
                                    <?php echo htmlspecialchars($employee['phone']); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-id-badge"></i>
                                <span>Employee ID</span>
                            </div>
                            <div class="info-value">
                                #<?php echo $employee['id']; ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-clock"></i>
                                <span>Record Created</span>
                            </div>
                            <div class="info-value">
                                <?php echo date('F j, Y \a\t g:i A', strtotime($employee['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Details Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-briefcase"></i>
                    <h3>Employment Details</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-building"></i>
                                <span>Department</span>
                            </div>
                            <div class="info-value">
                                <span style="color: <?php echo $deptColor; ?>; font-weight: 700;">
                                    <?php echo htmlspecialchars($employee['department']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user-tie"></i>
                                <span>Position</span>
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($employee['position']); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Hire Date</span>
                            </div>
                            <div class="info-value">
                                <?php echo formatDate($employee['hire_date']); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-history"></i>
                                <span>Tenure</span>
                            </div>
                            <div class="info-value">
                                <?php echo $tenure; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Salary Information Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-chart-line"></i>
                    <h3>Salary Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-item" style="border-bottom: 2px solid #e2e8f0; margin-bottom: 20px;">
                        <div class="info-label">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Annual Salary</span>
                        </div>
                        <div class="info-value highlight">
                            <?php echo formatSalary($employee['salary']); ?>
                        </div>
                    </div>
                    
                    <div class="salary-grid">
                        <div class="salary-item">
                            <div class="salary-amount">
                                <?php echo formatSalary($monthlySalary); ?>
                            </div>
                            <div class="salary-period">Monthly</div>
                        </div>
                        
                        <div class="salary-item">
                            <div class="salary-amount">
                                <?php echo formatSalary($biWeeklySalary); ?>
                            </div>
                            <div class="salary-period">Bi-weekly</div>
                        </div>
                        
                        <div class="salary-item">
                            <div class="salary-amount">
                                <?php echo formatSalary($dailySalary); ?>
                            </div>
                            <div class="salary-period">Daily</div>
                        </div>
                        
                        <div class="salary-item">
                            <div class="salary-amount">
                                <?php echo formatSalary($employee['salary'] / 52); ?>
                            </div>
                            <div class="salary-period">Weekly</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-bolt"></i>
                    <h3>Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="action-grid">
                        <a href="edit_employee.php?id=<?php echo $employee['id']; ?>" class="action-btn action-edit">
                            <i class="fas fa-edit"></i>
                            <span>Edit Profile</span>
                        </a>
                        
                        <button onclick="confirmDelete()" class="action-btn action-delete">
                            <i class="fas fa-trash"></i>
                            <span>Delete</span>
                        </button>
                        
                        <a href="mailto:<?php echo htmlspecialchars($employee['email']); ?>" class="action-btn action-email">
                            <i class="fas fa-envelope"></i>
                            <span>Send Email</span>
                        </a>
                        
                        <a href="tel:<?php echo htmlspecialchars($employee['phone']); ?>" class="action-btn action-phone">
                            <i class="fas fa-phone"></i>
                            <span>Make Call</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="stats-card">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">#<?php echo $employee['id']; ?></div>
                    <div class="stat-label">Employee ID</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-value"><?php echo $interval->y; ?></div>
                    <div class="stat-label">Years with Company</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-value"><?php echo htmlspecialchars($employee['department']); ?></div>
                    <div class="stat-label">Department</div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-value"><?php echo formatSalary($employee['salary']); ?></div>
                    <div class="stat-label">Annual Salary</div>
                </div>
            </div>
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
                <p>Viewing profile of <?php echo htmlspecialchars($employee['name']); ?></p>
            </div>
            <div class="footer-links">
                <a href="../index.php">Dashboard</a>
                <a href="add_employee.php">Add Employee</a>
                <a href="../index.php">View All</a>
            </div>
        </div>
    </footer>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Function to get initials from name
        function getInitials(name) {
            return name.split(' ').map(word => word[0]).join('').toUpperCase();
        }
        
        // Animate profile card
        document.addEventListener('DOMContentLoaded', function() {
            // Add typing effect to employee name
            const employeeName = "<?php echo htmlspecialchars($employee['name']); ?>";
            const nameElement = document.querySelector('.profile-hero-name');
            
            // Add floating animation to avatar
            const avatar = document.querySelector('.profile-avatar-large');
            setInterval(() => {
                avatar.style.animation = 'none';
                setTimeout(() => {
                    avatar.style.animation = 'float 6s ease-in-out infinite';
                }, 10);
            }, 6000);
            
            // Add shine animation to logo
            setInterval(() => {
                const logoIcon = document.querySelector('.logo-icon');
                logoIcon.style.animation = 'none';
                setTimeout(() => {
                    logoIcon.style.animation = 'shine 3s infinite';
                }, 10);
            }, 3000);
        });
        
        // Confirm delete function
        function confirmDelete() {
            Swal.fire({
                title: 'Delete Employee?',
                html: `Are you sure you want to delete <strong><?php echo htmlspecialchars($employee['name']); ?></strong>?<br><br>
                      <span style="color: #666; font-size: 14px;">This action cannot be undone. All employee data will be permanently removed.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(`delete_employee.php?id=<?php echo $employee['id']; ?>`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (!data.success) {
                                throw new Error(data.message);
                            }
                            return data;
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error}`
                            );
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleted!',
                        html: `Employee <strong><?php echo htmlspecialchars($employee['name']); ?></strong> has been deleted successfully.`,
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Go to Dashboard'
                    }).then(() => {
                        window.location.href = '../index.php';
                    });
                }
            });
        }
        
        // Print function
        function printProfile() {
            window.print();
        }
        
        // Copy email to clipboard
        function copyEmail() {
            const email = "<?php echo htmlspecialchars($employee['email']); ?>";
            navigator.clipboard.writeText(email).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Email Copied!',
                    text: 'Email address copied to clipboard',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        }
        
        // Copy phone to clipboard
        function copyPhone() {
            const phone = "<?php echo htmlspecialchars($employee['phone']); ?>";
            navigator.clipboard.writeText(phone).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Phone Copied!',
                    text: 'Phone number copied to clipboard',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        }
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + E to edit
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                window.location.href = `edit_employee.php?id=<?php echo $employee['id']; ?>`;
            }
            
            // Ctrl + Backspace to go back
            if (e.ctrlKey && e.key === 'Backspace') {
                e.preventDefault();
                window.history.back();
            }
            
            // Ctrl + P to print
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printProfile();
            }
            
            // Escape to go back
            if (e.key === 'Escape') {
                window.history.back();
            }
        });
        
        // Add hover effects to cards
        const cards = document.querySelectorAll('.dashboard-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
                card.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.12)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.08)';
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();

// Helper functions for colors
function adjustColor($hex, $percent) {
    // Adjust color brightness
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + $r * $percent / 100));
    $g = max(0, min(255, $g + $g * $percent / 100));
    $b = max(0, min(255, $b + $b * $percent / 100));
    
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

function hexToRgba($hex, $alpha) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    return "rgba($r, $g, $b, $alpha)";
}
?>