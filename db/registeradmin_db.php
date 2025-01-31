<?php
session_start();

include '../db/db_conn.php';

// Get form data with validation
$firstName = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
$lastName = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
$Email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';
$phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';

// Basic validation
if (empty($firstName) || empty($lastName) || empty($Email) || empty($password) || empty($confirm_password) || empty($role) || empty($phone_number) || empty($address)) {
    echo json_encode(['error' => 'All fields are required.']);
    exit();
}

// Check if passwords match
if ($password !== $confirm_password) {
    echo json_encode(['error' => 'Passwords do not match.']);
    exit();
}

// Password strength validation
$pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/';
if (!preg_match($pattern, $password)) {
    echo json_encode(['error' => 'Password must be at least 8 characters long and contain at least one number, special character, uppercase and lowercase letter.']);
    exit();
}

// Check if the email already exists
$sql = "SELECT COUNT(*) FROM admin_register WHERE email = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("s", $Email);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    echo json_encode(['error' => 'This email is already registered.']);
    exit();
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepare and execute SQL statement
$sql = "INSERT INTO admin_register (firstname, lastname, email, password, role, phone_number, address) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("sssssss", $firstName, $lastName, $Email, $hashedPassword, $role, $phone_number, $address);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Registration successful!']);
} else {
    echo json_encode(['error' => 'Error: ' . $stmt->error]);
}

// Close connection
$stmt->close();
$conn->close();
?>
