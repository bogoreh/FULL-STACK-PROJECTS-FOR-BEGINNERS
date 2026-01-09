<?php
// Include configuration and functions
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get all employees
$sql = "SELECT * FROM employees ORDER BY created_at DESC";
$result = $conn->query($sql);

include 'includes/header.php';
?>

<div class="dashboard-header">
    <h2><i class="fas fa-tachometer-alt"></i> Employee Dashboard</h2>
    <a href="operations/add_employee.php" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Add New Employee
    </a>
</div>

<?php
// Display any messages from URL parameters
if (isset($_GET['message'])) {
    $messageType = $_GET['type'] ?? 'success';
    $messageText = urldecode($_GET['message']);
    echo displayMessage($messageType, $messageText);
}
?>

<div class="stats-cards">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3>Total Employees</h3>
            <p><?php echo $result->num_rows; ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <h3>Total Salary</h3>
            <p>
                <?php 
                $totalSalary = 0;
                if ($result->num_rows > 0) {
                    $result->data_seek(0); // Reset pointer
                    while($row = $result->fetch_assoc()) {
                        $totalSalary += $row['salary'];
                    }
                    $result->data_seek(0); // Reset pointer again for table display
                }
                echo formatSalary($totalSalary);
                ?>
            </p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-info">
            <h3>Departments</h3>
            <p>
                <?php 
                $deptCount = 0;
                if ($result->num_rows > 0) {
                    $departments = [];
                    $result->data_seek(0); // Reset pointer
                    while($row = $result->fetch_assoc()) {
                        if (!in_array($row['department'], $departments)) {
                            $departments[] = $row['department'];
                            $deptCount++;
                        }
                    }
                    $result->data_seek(0); // Reset pointer again for table display
                }
                echo $deptCount;
                ?>
            </p>
        </div>
    </div>
</div>

<div class="table-container">
    <h3><i class="fas fa-list"></i> All Employees</h3>
    
    <?php if ($result->num_rows > 0): ?>
        <table class="employee-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Salary</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><span class="dept-badge"><?php echo htmlspecialchars($row['department']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                    <td class="salary"><?php echo formatSalary($row['salary']); ?></td>
                    <td class="actions">
                        <a href="operations/view_employee.php?id=<?php echo $row['id']; ?>" class="btn-view" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="operations/edit_employee.php?id=<?php echo $row['id']; ?>" class="btn-edit" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>')" class="btn-delete" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-user-slash"></i>
            <h3>No Employees Found</h3>
            <p>Add your first employee to get started!</p>
            <a href="operations/add_employee.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Add Employee
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>