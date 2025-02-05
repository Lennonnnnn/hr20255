<?php
// Start the session
session_start();

// Include database connection
include '../../db/db_conn.php';

// Ensure session variable is set
if (!isset($_SESSION['e_id'])) {
    die("Error: Employee ID is not set in the session.");
}

// Fetch user info
$employeeId = $_SESSION['e_id'];

// Correct SQL query
$sql = "SELECT e_id, firstname, middlename, lastname, available_leaves, role, position, department, phone_number 
        FROM employee_register WHERE e_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$employeeInfo = $result->fetch_assoc();

// Query for leave requests specific to the logged-in employee
$searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';
$fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : '';
$toDate = isset($_GET['toDate']) ? $_GET['toDate'] : '';
$statusFilter = isset($_GET['statusFilter']) ? $_GET['statusFilter'] : '';

$sql = "
    SELECT lr.*, e.firstname, e.lastname, s.firstname AS supervisor_firstname, s.lastname AS supervisor_lastname 
    FROM leave_requests lr
    JOIN employee_register e ON lr.e_id = e.e_id
    LEFT JOIN employee_register s ON lr.supervisor_id = s.e_id
    WHERE lr.e_id = ?";

if ($searchTerm) {
    $sql .= " AND (e.firstname LIKE ? OR e.lastname LIKE ? OR lr.e_id LIKE ?)";
}
if ($fromDate) {
    $sql .= " AND lr.start_date >= ?";
}
if ($toDate) {
    $sql .= " AND lr.end_date <= ?";
}
if ($statusFilter) {
    $sql .= " AND lr.status = ?";
}

$sql .= " ORDER BY lr.created_at ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing the query: " . $conn->error);
}

$bindParams = [$employeeId];
$bindTypes = "i";

if ($searchTerm) {
    $searchTerm = "%$searchTerm%";
    $bindParams[] = $searchTerm;
    $bindParams[] = $searchTerm;
    $bindParams[] = $searchTerm;
    $bindTypes .= "sss";
}
if ($fromDate) {
    $bindParams[] = $fromDate;
    $bindTypes .= "s";
}
if ($toDate) {
    $bindParams[] = $toDate;
    $bindTypes .= "s";
}
if ($statusFilter) {
    $bindParams[] = $statusFilter;
    $bindTypes .= "s";
}

$stmt->bind_param($bindTypes, ...$bindParams);
if (!$stmt->execute()) {
    die("Error executing the query: " . $stmt->error);
}

$result = $stmt->get_result();

// Calculate total leave days excluding Sundays and holidays
function calculateLeaveDays($start_date, $end_date) {
    $leave_days = 0;
    $current_date = strtotime($start_date);
    $end_date = strtotime($end_date);

    while ($current_date <= $end_date) {
        $day_of_week = date('w', $current_date);
        if ($day_of_week != 0) { // Exclude Sundays
            $leave_days++;
        }
        $current_date = strtotime('+1 day', $current_date);
    }
    return $leave_days;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave History</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Leave History</h1>
            <button class="btn btn-primary">Export</button>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form class="row g-3" method="GET" action="">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="searchTerm" placeholder="Search by Employee Name/ID" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="fromDate" placeholder="From Date" value="<?php echo htmlspecialchars($fromDate); ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="toDate" placeholder="To Date" value="<?php echo htmlspecialchars($toDate); ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="statusFilter">
                            <option value="">Filter by Status</option>
                            <option value="Approved" <?php if ($statusFilter == 'Approved') echo 'selected'; ?>>Approved</option>
                            <option value="Pending" <?php if ($statusFilter == 'Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Denied" <?php if ($statusFilter == 'Denied') echo 'selected'; ?>>Denied</option>
                            <option value="Supervisor Approved" <?php if ($statusFilter == 'Supervisor Approved') echo 'selected'; ?>>Supervisor Approved</option>
                            
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Leave History Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>Date Applied</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Leave Dates</th>
                        <th>Leave Type</th>
                        <th class="border-0">Total Leave Days</th> <!-- Removed border -->
                        <th>Status</th>
                        <th>Supervisor</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                            $leave_days = calculateLeaveDays($row['start_date'], $row['end_date']);
                        ?>
                        <tr>
                            <td>
                                <?php 
                                    if (isset($row['created_at'])) {
                                        echo htmlspecialchars(date("F j, Y", strtotime($row['created_at']))) . ' <span class="text-warning"> | </span> ' . htmlspecialchars(date("g:i A", strtotime($row['created_at'])));
                                    } else {
                                        echo "Not Available";
                                    }
                                ?>
                            </td> 
                            <td><?php echo htmlspecialchars($row['e_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                            <td><?php echo htmlspecialchars(date("F j, Y", strtotime($row['start_date']))) . ' <span class="text-warning"> | </span> ' . htmlspecialchars(date("F j, Y", strtotime($row['end_date']))); ?></td>
                            <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                            <td><?php echo htmlspecialchars($leave_days); ?> day/s</td>
                            <td>
                                <?php 
                                $status = $row['status'];
                                if ($status === 'Approved') {
                                    echo '<span class="text-success" style="font-weight: bold;">' . $status . '</span>';
                                } elseif ($status === 'Denied') {
                                    echo '<span class="text-danger" style="font-weight: bold;">' . $status . '</span>';
                                } elseif ($status === 'Pending') {
                                    echo '<span class="text-warning" style="font-weight: bold;">' . $status . '</span>';
                                } elseif ($status === 'Supervisor Approved' || $status === 'Supervisor Denied') {
                                    echo '<span class="text-primary" style="font-weight: bold;">' . $status . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if ($status === 'Supervisor Approved' || $status === 'Supervisor Denied') {
                                    $supervisorName = htmlspecialchars($row['supervisor_firstname'] . ' ' . $row['supervisor_lastname']);
                                    echo '<span class="text-primary" style="font-weight: bold;">' . $supervisorName . '</span>';
                                } elseif ($status === 'Denied') {
                                    $supervisorName = htmlspecialchars($row['supervisor_firstname'] . ' ' . $row['supervisor_lastname']);
                                    echo '<span class="text-danger" style="font-weight: bold;">' . $supervisorName . '</span>';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-info" onclick="viewItem()">View</button>
                                    <button class="btn btn-sm btn-warning" onclick="editItem()">Edit</button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" <?php if ($row['status'] === 'Approved') echo 'disabled'; ?>>Delete</button>
                                </div>
                            </td>
                       </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                            <td colspan="9" class="text-center">No records found</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    // View Button Function
    function viewItem() {
        alert("View button clicked.");
        // You can replace this with the code for showing more details, redirecting to a view page, etc.
    }

    // Edit Button Function
    function editItem() {
        alert("Edit button clicked.");
        // You can replace this with the code for opening an edit form or redirecting to an edit page, etc.
    }

    // Delete Button Function
    function confirmDelete() {
        alert("Item deleted.");
        // You can replace this with code to actually delete the item, like sending a request to a server.
    }
</script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>