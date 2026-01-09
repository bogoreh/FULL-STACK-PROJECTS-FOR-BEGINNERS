<?php
// Display success/error messages
function displayMessage($type, $message) {
    $alertClass = $type === 'success' ? 'alert-success' : 'alert-error';
    return "<div class='alert $alertClass'><i class='fas fa-info-circle'></i> $message</div>";
}

// Sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Format salary
function formatSalary($salary) {
    return 'et ' . number_format($salary, 2);
}
?>