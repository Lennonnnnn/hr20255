<?php
session_start();

include '../db/db_conn.php';

// Get form data with validation
$employeeId = isset($_POST['e_id']) ? trim($_POST['e_id']) : '';
$firstName = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
$lastName = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
$Email = isset($_POST['email']) ? trim($_POST['email']) : '';
$department = isset($_POST['department']) ? trim($_POST['department']) : '';
$phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';

// Basic validation
if (empty($employeeId) || empty($firstName) || empty($lastName) || empty($Email) || empty($department) || empty($phone_number) || empty($address)) {
    echo json_encode(['error' => 'All fields are required.']);
    exit();
}

// Prepare and execute SQL statement for updating
$sql = "UPDATE employee_register SET firstname=?, lastname=?, email=?, department=?, phone_number=?, address=? WHERE e_id=?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("ssssssi", $firstName, $lastName, $Email, $department, $phone_number, $address, $employeeId);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Employee information updated successfully!']);
} else {
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}

// Close connection
$stmt->close();
$conn->close();
?>
