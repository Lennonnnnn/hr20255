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
      overflow: hidden; /* Prevents scroll bar */
    }

    .container {
      display: block;
      position: absolute; /* Changed from fixed to absolute */
      top: 10%; /* Adjusted top position to move the container to the top */
      left: 50%;
      transform: translate(-50%, 0); /* Removed vertical centering */
      width: 450px;
      padding: 20px;
      background-color: white;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      z-index: 1000;
    }

    .modal-overlay {
      display: block; /* Show the overlay initially */
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 999;
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
      width: 15%; /* Make buttons smaller */
      padding: 5px; /* Adjust padding */
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

    .button-container {
      display: flex;
      justify-content: space-between; /* Place buttons on each side */
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

    .modal {
      display: none; /* Hidden by default */
      position: fixed; /* Stay in place */
      z-index: 1; /* Sit on top */
      left: 0;
      top: 0;
      width: 100%; /* Full width */
      height: 100%; /* Full height */
      overflow: auto; /* Enable scroll if needed */
      background-color: rgba(0,0,0,0.5); /* Black w/ opacity */
      display: flex; /* Use flexbox for centering */
      justify-content: center; /* Center horizontally */
      align-items: center; /* Center vertically */
    }

    .modal-content {
      background-color: #fefefe;
      padding: 20px;
      border: 1px solid #888;
      border-radius: 8px; /* Rounded corners */
      width: 90%; /* Increased width */
      max-width: 700px; /* Increased maximum width */
      box-shadow: 0 5px 15px rgba(0,0,0,0.3); /* Add shadow */
      animation: fadeIn 0.3s; /* Fade-in animation */
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
    }

    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <!-- Removed the "Open Leave Tracker" button -->
  <!-- Removed the modal overlay and its functionality -->

  <div class="container" id="leaveTrackerModal">
    <h1>Leave Tracker</h1>
    <div class="button-container">
      <button onclick="window.history.back();">Back</button>
      <button id="openModalBtn">Open Converter</button>
    </div> <!-- Added missing closing div tag for button-container -->

    <div id="myModal" class="modal">
      <div class="modal-content">
        <span class="close">&times;</span>
        <table>
          <thead>
            <tr>
              <th colspan="2">Convert Your Leave Days to Money</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Enter number of leave days:</td>
              <td><input type="number" id="leaveDays" required></td>
            </tr>
            <tr>
              <td>Enter your daily rate (in Peso):</td>
              <td><input type="number" id="dailyRate" required></td>
            </tr>
            <tr>
              <td colspan="2" style="text-align: center;">
                <button id="calculateBtn">Calculate</button>
              </td>
            </tr>
            <tr>
              <td colspan="2" style="text-align: center;">
                <p id="resultMessage" class="result"></p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <br> <!-- Added space instead of separator -->
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
                    <td></td>
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
    // Add the openModal and closeModal functions
    document.getElementById('openModalBtn').onclick = function() {
      document.getElementById('myModal').style.display = 'flex'; // Use flex to center
    };

    document.querySelector('.close').onclick = function() {
      document.getElementById('myModal').style.display = 'none';
    };

    window.onclick = function(event) {
      if (event.target == document.getElementById('myModal')) {
        document.getElementById('myModal').style.display = 'none';
      }
    };

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