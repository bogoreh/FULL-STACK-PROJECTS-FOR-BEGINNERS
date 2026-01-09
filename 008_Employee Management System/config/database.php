<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'employee_management');

// Create connection function
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Create employees table if it doesn't exist
function createTableIfNotExists($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS employees (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        department VARCHAR(50) NOT NULL,
        position VARCHAR(50) NOT NULL,
        salary DECIMAL(10, 2) NOT NULL,
        hire_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === FALSE) {
        echo "Error creating table: " . $conn->error;
    }
}

// Call the function to create table when this file is included
$conn = connectDB();
createTableIfNotExists($conn);
?>