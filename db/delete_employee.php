<?php
session_start();

include '../db/db_conn.php';

// Get employee ID from request
$employeeId = isset($_POST['e_id']) ? trim($_POST['e_id']) : '';

// Basic validation
if (empty($employeeId)) {
    echo json_encode(['error' => 'Employee ID is required.']);
    exit();
}

// Prepare and execute SQL statement for deleting
$sql = "DELETE FROM employee_register WHERE e_id=?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("i", $employeeId);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Employee deleted successfully!']);
} else {
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}

// Close connection
$stmt->close();
$conn->close();
?>
