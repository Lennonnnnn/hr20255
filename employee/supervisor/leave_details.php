<?php
// Start the session
session_start();

// Include database connection
include '../../db/db_conn.php';

// Ensure session variable is set
if (!isset($_SESSION['e_id'])) {
    die("Error: Employee ID is not set in the session.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leave Tracker</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f9;
      margin: 0;
      padding: 0;
    }

    .container {
      width: 450px; /* Increased container width */
      margin: 50px auto;
      padding: 20px;
      background-color: white;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
    }

    h1 {
      text-align: center;
      margin-bottom: 20px;
    }

    table {
      width: 100%; /* Table takes up 100% of the container width */
      border-collapse: collapse;
    }

    th, td {
      padding: 15px; /* Increased padding to make cells wider */
      text-align: left;
      border: 1px solid #ddd;
    }

    th {
      background-color: #f4f4f4;
    }

    input {
      width: 100%;
      padding: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    input.no-border {
      border: none;
      background-color: transparent;
    }

    button {
      width: 100%;
      padding: 10px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin-top: 20px;
    }

    button:hover {
      background-color: #45a049;
    }

    #leaveStatus p {
      font-size: 16px;
      margin: 5px 0;
    }
    .no-border {
  border: none;
}

.no-border-cell {
  border: none;
  text-align: center; /* Optionally align text inside cells */
}

table {
  width: 100%;
  border-collapse: collapse; /* Ensures borders don't double up */
}

table td, table th {
  border: 1px solid black; /* Set border for table cells */
  padding: 8px;
  text-align: left; /* Adjust text alignment */
}

table input {
  border: none; /* Remove border from input elements */
  width: 100%; /* Make input element fill the cell */
  box-sizing: border-box; /* Ensure padding is considered inside the input */
}

  </style>
</head>
<body>
<div class="container">
    <h1>Leave Tracker</h1>
    <div class="table-container">
      <table>
        <tr>
          <th>Employee ID</th>
          <th>Employee Name</th>
          <th>Leave Type</th>
          <th>Total Days</th>
          <th>Used</th>
        </tr>
        <?php
          // Fetch user info
          $employeeId = $_SESSION['e_id'];
          $sql = "SELECT e_id, firstname, middlename, lastname, available_leaves, role, position, department, phone_number 
                  FROM employee_register WHERE e_id = ?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("i", $employeeId);
          $stmt->execute();
          $result = $stmt->get_result();
          $employeeInfo = $result->fetch_assoc();

          if ($employeeInfo) {
            echo "<tr>
                    <td><input type='text' class='no-border' value='{$employeeInfo['e_id']}' readonly></td>
                    <td><input type='text' class='no-border' value='{$employeeInfo['firstname']} {$employeeInfo['middlename']} {$employeeInfo['lastname']}' readonly></td>
                    <td>{$employeeInfo['role']}</td>
                    <td><input type='number' class='no-border-cell' value='{$employeeInfo['available_leaves']}' readonly></td>
                    <td><input type='number' class='no-border-cell' value='0' readonly></td>
                  </tr>";
          } else {
            echo "<tr><td colspan='5'>Error fetching employee information.</td></tr>";
          }

          $conn->close();
        ?>
      </table>
    </div>
</div>

  <script>
    function calculateRemainingLeaves() {
      // Get the input values
      const totalDays = parseInt(document.querySelector('input[value="12"]').value); // Replace with actual logic to fetch the total leave days
      const usedDays = parseInt(document.querySelector('input[value="3"]').value); // Replace with actual logic to fetch the used leave days
      
      // Calculate remaining leaves
      const remainingLeave = totalDays - usedDays;
      
      // Update the display
      document.getElementById('sickLeaveRemaining').textContent = `Leave Remaining: ${remainingLeave}`;
    }
  </script>
</body>
</html>
