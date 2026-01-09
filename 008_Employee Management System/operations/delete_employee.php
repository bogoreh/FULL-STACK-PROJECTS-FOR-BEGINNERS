<?php
require_once '../config/database.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $response = [
        'success' => false,
        'message' => 'Invalid employee ID'
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$id = $_GET['id'];

// Get employee name for message
$sql = "SELECT name FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $response = [
        'success' => false,
        'message' => 'Employee not found'
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$employee = $result->fetch_assoc();
$employeeName = $employee['name'];
$stmt->close();

// Delete the employee
$sql = "DELETE FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $response = [
        'success' => true,
        'message' => "Employee '$employeeName' deleted successfully!"
    ];
} else {
    $response = [
        'success' => false,
        'message' => "Error deleting employee: " . $conn->error
    ];
}

$stmt->close();
$conn->close();

// Return JSON response for AJAX
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>