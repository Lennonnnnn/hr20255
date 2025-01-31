<?php
session_start();

include '../db/db_conn.php';
include '../phpqrcode/qrlib.php';  // Include the QR code library

// Sanitize inputs to prevent XSS attacks
$firstname = htmlspecialchars($_POST['firstname']);
$lastname = htmlspecialchars($_POST['lastname']);
$email = htmlspecialchars($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$role = "Employee"; // Static role value as requested
$department = $_POST['department'];
$position = $_POST['position'];

// Password validation
if ($password !== $confirm_password) {
    echo "<script>alert('Passwords do not match. Please try again.'); window.history.back();</script>";
    exit();
}

// Hash the password before storing it
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if the email already exists in the database
$email_check_query = "SELECT * FROM employee_register WHERE email = ?";
$stmt = $conn->prepare($email_check_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<script>alert('Email already exists. Please use a different email.'); window.history.back();</script>";
    exit();
}

// Function to generate a random employee ID with first 4 digits fixed to "8080"
function generateEmployeeId($conn) {
    do {
        $randomDigits = rand(1000, 9999);  // Generate random 4 digits
        $employeeId = '8080' . $randomDigits;  // Concatenate with '8080' to make an 8-digit number
        // Check if this employee ID already exists in the database
        $checkIdQuery = "SELECT * FROM employee_register WHERE e_id = ?";
        $stmt = $conn->prepare($checkIdQuery);
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);  // Repeat if the ID already exists

    return $employeeId;
}

// Generate a unique employee ID with the first 4 digits as "8080"
$employeeId = generateEmployeeId($conn);

// Insert data into the employee_register table with the 8-digit employee ID
$insert_query = "INSERT INTO employee_register (e_id, firstname, lastname, email, password, role, department, position) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("isssssss", $employeeId, $firstname, $lastname, $email, $hashed_password, $role, $department, $position);

if ($stmt->execute()) {
    // Define QR code content (using the employee ID)
    $codeContents = 'Employee ID: ' . $employeeId . ' | Email: ' . $email;

    // Define the directory for QR codes
    $tempDir = $_SERVER['DOCUMENT_ROOT'] . "/HR2/QR/";

    // Create the directory if it doesn't exist
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    // Define the file name and path for the QR code
    $fileName = 'employee_' . $employeeId . '.png';
    $pngAbsoluteFilePath = $tempDir . $fileName;

    // Generate the QR code
    try {
        QRcode::png($codeContents, $pngAbsoluteFilePath);
    } catch (Exception $e) {
        echo "<script>alert('Error generating QR code: " . $e->getMessage() . "'); window.history.back();</script>";
        exit();
    }

    // Update the employee's record with the QR code path
    $qrCodePath = 'QR/' . $fileName;
    $updateSql = "UPDATE employee_register SET qr_code_path = ? WHERE e_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("si", $qrCodePath, $employeeId);
    $updateStmt->execute();
    $updateStmt->close();

    // Redirect to employee management page on success
    echo "<script>alert('Employee account created successfully!'); window.location.href='../admin/employee.php';</script>";
} else {
    echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
}

// Close the prepared statement and connection
$stmt->close();
$conn->close();
?>
