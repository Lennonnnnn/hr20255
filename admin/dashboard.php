<?php
session_start();


if (!isset($_SESSION['a_id'])) {
    header("Location: ../admin/login.php");
    exit();
}

include '../db/db_conn.php';

// Fetch user info
$adminId = $_SESSION['a_id'];
$sql = "SELECT firstname, middlename, lastname, email, role, pfp FROM admin_register WHERE a_id = ?";
$stmt = $conn->prepare($sql);

// Check if statement preparation failed
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user information
$adminInfo = $result->fetch_assoc();

// Set profile picture or use default if not set
$profilePicture = !empty($adminInfo['pfp']) ? $adminInfo['pfp'] : '../img/defaultpfp.jpg';

// Close statement and connection
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Admin Dashboard | HR2</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="../css/styles.css" rel="stylesheet" />
    <link href="../css/calendar.css" rel="stylesheet"/>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed bg-black">
    <nav class="sb-topnav navbar navbar-expand navbar-dark border-bottom border-1 border-warning bg-dark">
        <a class="navbar-brand ps-3 text-muted" href="../admin/dashboard.php">Admin Portal</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars text-light"></i></button>
    
    <!-- Flex container to hold both time/date and search form -->
        <div class="d-flex ms-auto me-0 me-md-3 my-2 my-md-0 align-items-center">
            <div class="text-light me-3 p-2 rounded shadow-sm bg-gradient" id="currentTimeContainer" 
                style="background: linear-gradient(45deg, #333333, #444444); border-radius: 5px;">
                    <span class="d-flex align-items-center">
                        <span class="pe-2">
                            <i class="fas fa-clock"></i> 
                        <span id="currentTime">00:00:00</span>
                    </span>
                    <button class="btn btn-outline-warning btn-sm ms-2" type="button" onclick="toggleCalendar()">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="currentDate">00/00/0000</span>
                    </button>
                    </span>
            </div>
            <form class="d-none d-md-inline-block form-inline">
            <div class="input-group">
                <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                <button class="btn btn-warning" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
            </div>
            </form>
        </div>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion bg-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu ">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading text-center text-muted">Your Profile</div>
                        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                            <li class="nav-item dropdown text">
                                <a class="nav-link dropdown-toggle text-light d-flex justify-content-center ms-4" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="<?php echo (!empty($adminInfo['pfp']) && $adminInfo['pfp'] !== 'defaultpfp.jpg') 
                                        ? htmlspecialchars($adminInfo['pfp']) 
                                        : '../img/defaultpfp.jpg'; ?>" 
                                        class="rounded-circle border border-light" width="120" height="120" alt="Profile Picture" />
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="../admin/profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="#!">Settings</a></li>
                                    <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                                    <li><hr class="dropdown-divider border-black" /></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a></li>
                                </ul>
                            </li>
                            <li class="nav-item text-light d-flex ms-3 flex-column align-items-center text-center">
                                <span class="big text-light mb-1">
                                    <?php
                                        if ($adminInfo) {
                                        echo htmlspecialchars($adminInfo['firstname'] . ' ' . $adminInfo['middlename'] . ' ' . $adminInfo['lastname']);
                                        } else {
                                        echo "Admin information not available.";
                                        }
                                    ?>
                                </span>      
                                <span class="big text-light">
                                    <?php
                                        if ($adminInfo) {
                                        echo htmlspecialchars($adminInfo['role']);
                                        } else {
                                        echo "User information not available.";
                                        }
                                    ?>
                                </span>
                            </li>
                        </ul>
                        <div class="sb-sidenav-menu-heading text-center text-muted border-top border-1 border-warning mt-3">Admin Dashboard</div>
                        <a class="nav-link text-light" href="../admin/dashboard.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTAD" aria-expanded="false" aria-controls="collapseTAD">
                            <div class="sb-nav-link-icon"><i class="fa fa-address-card"></i></div>
                            Time and Attendance
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseTAD" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/attendance.php">Attendance</a>
                                <a class="nav-link text-light" href="../admin/timesheet.php">Timesheet</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLM" aria-expanded="false" aria-controls="collapseLM">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar-times"></i></div>
                            Leave Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/leave_requests.php">Leave Requests</a>
                                <a class="nav-link text-light" href="../admin/leave_history.php">Leave History</a>
                                <a class="nav-link text-light"  href="../admin/leave_allocation.php">Set Leave</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePM" aria-expanded="false" aria-controls="collapsePM">
                            <div class="sb-nav-link-icon"><i class="fas fa-line-chart"></i></div>
                            Performance Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/evaluation.php">Evaluation</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSR" aria-expanded="false" aria-controls="collapseSR">
                            <div class="sb-nav-link-icon"><i class="fa fa-address-card"></i></div>
                            Social Recognition
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseSR" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/awardee.php">Awardee</a>
                                <a class="nav-link text-light" href="../admin/recognition.php">Generate Certificate</a>
                            </nav>
                        </div>
                        <div class="sb-sidenav-menu-heading text-center text-muted border-top border-1 border-warning mt-3">Account Management</div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                            Accounts
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../admin/calendar.php">Calendar</a>
                                <a class="nav-link text-light" href="../admin/admin.php">Admin Accounts</a>
                                <a class="nav-link text-light" href="../admin/employee.php">Employee Accounts</a>
                            </nav>
                        </div>
                        <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                        </div>
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-black text-light border-top border-1 border-warning">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($adminInfo['role']); ?></div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main class="bg-black">
                <div class="container-fluid position-relative px-4">
                    <h1 class="mb-4 text-light">Dashboard</h1>
                    <div class="row">
                        <!-- Move the pie chart to the left side -->
                        <div class="col-md-6 mt-2">
                            <div class="card mb-4">
                                <div class="card-header bg-dark text-light border-bottom border-1 border-warning">
                                    <i class="fas fa-chart-pie me-1"></i> 
                                    <a class="text-light" href="../admin/leave_requests.php">Leave Request Status </a>
                                </div>
                                <div class="card-body bg-dark">
                                    <canvas id="leaveStatusChart" width="300" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <div class="card bg-dark">
                                <div class="card-header border-bottom border-1 border-warning text-info">
                                    <h3>Performance Ratings | Graph</h3>
                                </div>
                                <div class="card-body">
                                    <!-- Rating 1: Quality of Work -->
                                    <div class="mt-2">
                                        <h5 class="text-light">Quality of Work</h5>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-warning">Excellent</span>
                                            <span class="text-warning">85%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <!-- Rating 2: Communication Skills -->
                                    <div class="mt-2">
                                        <h5 class="text-light">Communication Skills</h5>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-warning">Good</span>
                                            <span class="text-warning">75%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <!-- Rating 3: Teamwork -->
                                    <div class="mt-2">
                                        <h5 class="text-light">Teamwork</h5>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-warning">Very Good</span>
                                            <span class="text-warning">80%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 80%;" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <!-- Rating 4: Punctuality -->
                                    <div class="mt-2">
                                        <h5 class="text-light">Punctuality</h5>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-warning">Average</span>
                                            <span class="text-warning">60%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 60%;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <!-- Rating 5: Initiative -->
                                    <div class="mt-2">
                                        <h5 class="text-light">Initiative</h5>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-warning">Excellent</span>
                                            <span class="text-warning">90%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 90%;" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mt-2 mb-2">
                            <div class="card bg-dark text-info border-0">
                                <div class="card-header border-bottom border-warning">
                                    <h3 class="mb-0">Top Performers | Graph</h3>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <!-- Performer 1 -->
                                        <li class="list-group-item bg-dark text-light d-flex align-items-center justify-content-between border-0">
                                            <div class="d-flex align-items-center">
                                                <img src="../../uploads/profile_pictures/try.jpg" alt="Performer 1" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <h5 class="mb-0">John Doe</h5>
                                                    <small class="text-warning">Sales Manager</small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center" style="width: 30%;">
                                                <div class="progress" style="width: 100%; height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 90%;" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="text-warning ms-2">90%</span>
                                            </div>
                                        </li>
                                        <!-- Performer 2 -->
                                        <li class="list-group-item bg-dark text-light d-flex align-items-center justify-content-between border-0">
                                            <div class="d-flex align-items-center">
                                                <img src="../../uploads/profile_pictures/pfp3.jpg" alt="Performer 2" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <h5 class="mb-0">Jane Smith</h5>
                                                    <small class="text-warning">Marketing Specialist</small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center" style="width: 30%;">
                                                <div class="progress" style="width: 100%; height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="text-warning ms-2">85%</span>
                                            </div>
                                        </li>
                                        <!-- Performer 3 -->
                                        <li class="list-group-item bg-dark text-light d-flex align-items-center justify-content-between border-0">
                                            <div class="d-flex align-items-center">
                                                <img src="../../uploads/profile_pictures/logo.jpg" alt="Performer 3" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <h5 class="mb-0">Michael Johnson</h5>
                                                    <small class="text-warning">HR Manager</small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center" style="width: 30%;">
                                                <div class="progress" style="width: 100%; height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: 80%;" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span class="text-warning ms-2">80%</span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mt-2 mb-2">
                            <div class="card mb-4 bg-dark text-light">
                                <div class="card-header border-bottom border-1 border-warning">
                                    <i class="fas fa-table me-1"></i>
                                    DataTable Example
                                </div>
                                <div class="card-body">
                                    <table id="datatablesSimple" class="table text-light">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Office</th>
                                                <th>Age</th>
                                                <th>Start date</th>
                                                <th>Salary</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>Name</th>
                                                <th>Position</th>
                                                <th>Office</th>
                                                <th>Age</th>
                                                <th>Start date</th>
                                                <th>Salary</th>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            <tr>
                                                <td>Zorita Serrano</td>
                                                <td>Software Engineer</td>
                                                <td>San Francisco</td>
                                                <td>56</td>
                                                <td>2012/06/01</td>
                                                <td>$115,000</td>
                                            </tr>
                                            <tr>
                                                <td>Jennifer Acosta</td>
                                                <td>Junior Javascript Developer</td>
                                                <td>Edinburgh</td>
                                                <td>43</td>
                                                <td>2013/02/01</td>
                                                <td>$75,650</td>
                                            </tr>
                                            <tr>
                                                <td>Cara Stevens</td>
                                                <td>Sales Assistant</td>
                                                <td>New York</td>
                                                <td>46</td>
                                                <td>2011/12/06</td>
                                                <td>$145,600</td>
                                            </tr>
                                            <tr>
                                                <td>Hermione Butler</td>
                                                <td>Regional Director</td>
                                                <td>London</td>
                                                <td>47</td>
                                                <td>2011/03/21</td>
                                                <td>$356,250</td>
                                            </tr>
                                            <tr>
                                                <td>Lael Greer</td>
                                                <td>Systems Administrator</td>
                                                <td>London</td>
                                                <td>21</td>
                                                <td>2009/02/27</td>
                                                <td>$103,500</td>
                                            </tr>
                                            <tr>
                                                <td>Jonas Alexander</td>
                                                <td>Developer</td>
                                                <td>San Francisco</td>
                                                <td>30</td>
                                                <td>2010/07/14</td>
                                                <td>$86,500</td>
                                            </tr>
                                            <tr>
                                                <td>Shad Decker</td>
                                                <td>Regional Director</td>
                                                <td>Edinburgh</td>
                                                <td>51</td>
                                                <td>2008/11/13</td>
                                                <td>$183,000</td>
                                            </tr>
                                            <tr>
                                                <td>Michael Bruce</td>
                                                <td>Javascript Developer</td>
                                                <td>Singapore</td>
                                                <td>29</td>
                                                <td>2011/06/27</td>
                                                <td>$183,000</td>
                                            </tr>
                                            <tr>
                                                <td>Donna Snider</td>
                                                <td>Customer Support</td>
                                                <td>New York</td>
                                                <td>27</td>
                                                <td>2011/01/25</td>
                                                <td>$112,000</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container" id="calendarContainer" 
                        style="position: fixed; top: 9%; right: 0; z-index: 1050; 
                        width: 700px; display: none;">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="calendar" class="p-2"></div>
                            </div>
                        </div>
                    </div>
                    <!-- for leaveStatusChart -->
                    <?php

                        include '../db/db_conn.php';

                        $sql = "SELECT status, COUNT(*) as count FROM leave_requests GROUP BY status";
                        $result = $conn->query($sql);

                        $status_counts = [
                            'Approved' => 0,
                            'Supervisor Approved' => 0,
                            'Denied' => 0,
                        ];
                        while ($row = $result->fetch_assoc()) {
                            $status = $row['status'];
                            if (isset($status_counts[$status])) {
                                $status_counts[$status] = $row['count'];
                            }
                        }

                        $conn->close();
                    ?>
                </div>
            </main>
                <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark text-light">
                            <div class="modal-header border-bottom border-warning">
                                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to log out?
                            </div>
                            <div class="modal-footer border-top border-warning">
                                <button type="button" class="btn border-secondary text-light" data-bs-dismiss="modal">Cancel</button>
                                <form action="../admin/logout.php" method="POST">
                                    <button type="submit" class="btn btn-danger">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>  
            <footer class="py-4 bg-dark mt-auto border-top border-1 border-warning">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                            <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
                            </div>
                    </div>
            </footer>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Doughnut chart data
        const data = {
            labels: ['Approved', 'Pending', 'Denied'],
            datasets: [{
                data: [
                    <?php echo $status_counts['Approved']; ?>,
                    <?php echo $status_counts['Supervisor Approved']; ?>,
                    <?php echo $status_counts['Denied']; ?>
                ],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545']
            }]
        };

        // Doughnut chart configuration
        const leaveStatusCtx = document.getElementById('leaveStatusChart').getContext('2d');
        const leaveStatusChart = new Chart(leaveStatusCtx, {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'Leave Request Statuses'
                }
            }
        });
        //for leaveStatusChart end

        //for calendar only
        // Global variable for calendar
        let calendar; // Declare calendar variable globally

function toggleCalendar() {
    const calendarContainer = document.getElementById('calendarContainer');

    // Toggle visibility of the calendar container
    if (calendarContainer.style.display === 'none' || calendarContainer.style.display === '') {
        calendarContainer.style.display = 'block';

        // Initialize the calendar if it hasn't been initialized yet
        if (!calendar) {
            initializeCalendar();
        }
    } else {
        calendarContainer.style.display = 'none';
    }
}

// Function to initialize FullCalendar
function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 440,  // Set the height of the calendar to make it small
        events: {
            url: '../db/holiday.php',  // Endpoint for fetching events
            method: 'GET',
            failure: function() {
                alert('There was an error fetching events!');
            }
        }
    });

    calendar.render();
}

// Set the current date when the page loads
document.addEventListener('DOMContentLoaded', function () {
    const currentDateElement = document.getElementById('currentDate');
    const currentDate = new Date().toLocaleDateString(); // Get the current date
    currentDateElement.textContent = currentDate; // Set the date text
});

// Close the calendar when clicking outside of it
document.addEventListener('click', function(event) {
    const calendarContainer = document.getElementById('calendarContainer');
    const calendarButton = document.querySelector('button[onclick="toggleCalendar()"]');

    // Hide the calendar if the user clicks outside of the calendar and button
    if (!calendarContainer.contains(event.target) && !calendarButton.contains(event.target)) {
        calendarContainer.style.display = 'none';
    }
});
        //for calendar only end

        //for leave request (error)
 document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', // Basic view to confirm setup
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth'
        }
    });
    calendar.render();
    //console.log("Calendar initialized and rendered");


            function fetchLeaveData(date) {
                fetch(`leave_data.php?date=${date}`)
                .then(response => response.json())
                .then(data => {
                    let leaveDetails = 'Employees on leave:\n';
                    if (data.length > 0) {
                        data.forEach(employee => {
                            leaveDetails += `${employee.name} (${employee.leave_type})\n`;
                        });
                    } else {
                        leaveDetails = 'No employees on leave for this day.';
                    }
                    alert(leaveDetails); // You can replace this with a modal or a more styled output
                })
                .catch(error => {
                    console.error('Error fetching leave data:', error);
                    alert('An error occurred while fetching leave data.');
                });
            }
        });
            //for leave request (error) end

            function setCurrentTime() {
    const currentTimeElement = document.getElementById('currentTime');
    const currentDateElement = document.getElementById('currentDate');

    // Get the current date and time in UTC
    const currentDate = new Date();
    
    // Adjust time to Philippine Time (UTC+8)
    currentDate.setHours(currentDate.getHours() + 0);

    // Extract hours, minutes, and seconds
    const hours = currentDate.getHours();
    const minutes = currentDate.getMinutes();
    const seconds = currentDate.getSeconds();

    // Format hours, minutes, and seconds to ensure they are always two digits
    const formattedHours = hours < 10 ? '0' + hours : hours;
    const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
    const formattedSeconds = seconds < 10 ? '0' + seconds : seconds;

    // Set the current time
    currentTimeElement.textContent = `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;

    // Set the current date
    currentDateElement.textContent = currentDate.toLocaleDateString();
}

// Initial call to set the current time and date
setCurrentTime();

// Update the current time every second
setInterval(setCurrentTime, 1000);

    </script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../js/admin.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script> 
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="../js/datatables-simple-demo.js"></script>

</body>

</html>
