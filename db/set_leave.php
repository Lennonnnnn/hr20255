<?php
session_start();
include '../db/db_conn.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the admin is logged in
if (!isset($_SESSION['a_id'])) {
    die("Error: You must be logged in as admin.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get leave allocations from the form
    $employee_leaves = $_POST['employee_leaves'];
    $employeeId = $_POST['employee_id']; // Get specific employee ID

    // Function to get current leave balance for a specific employee
    function get_current_leave_balance($conn, $employeeId) {
        $sql = "SELECT available_leaves FROM employee_register WHERE e_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param('i', $employeeId);
        $stmt->execute();
        $stmt->bind_result($available_leaves);
        $stmt->fetch();
        $stmt->close();
        return $available_leaves;
    }

    // Validate input
    if (!is_numeric($employee_leaves) || $employee_leaves <= 0) {
        die("Error: Leave days must be a positive number.");
    }

    // Update leave allocation for either all employees or a specific employee
    if ($employeeId == 'all') {
        // Update for all employees
        $update_sql = "UPDATE employee_register SET available_leaves = available_leaves + ? WHERE role = 'Employee'";
        $update_stmt = $conn->prepare($update_sql);
        if ($update_stmt === false) {
            die("Error preparing update statement: " . $conn->error);
        }
        $update_stmt->bind_param('i', $employee_leaves);
        if (!$update_stmt->execute()) {
            die("Error executing update statement: " . $update_stmt->error);
        }
        echo "<div class='alert alert-success'>Leave allocations updated for all employees!</div>";
    } else {
        // Update for a specific employee
        $current_leaves = get_current_leave_balance($conn, $employeeId);
        $new_leave_total = $current_leaves + $employee_leaves;

        $update_sql = "UPDATE employee_register SET available_leaves = ? WHERE e_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if ($update_stmt === false) {
            die("Error preparing update statement: " . $conn->error);
        }
        $update_stmt->bind_param('ii', $new_leave_total, $employeeId);
        if (!$update_stmt->execute()) {
            die("Error executing update statement: " . $update_stmt->error);
        }
        echo "<div class='alert alert-success'>Leave credit added successfully!</div>";
    }
}
?>