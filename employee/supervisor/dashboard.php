<?php
session_start();
if (!isset($_SESSION['e_id']))  {
    header("Location: ../../employee/login.php"); // Redirect to login if not logged in
    exit();
}

include '../../db/db_conn.php';

// Fetch user info
$employeeId = $_SESSION['e_id'];
$sql = "SELECT firstname, middlename, lastname, email, role, position, pfp FROM employee_register WHERE e_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$employeeInfo = $result->fetch_assoc();
$stmt->close();
$conn->close();

$profilePicture = !empty($employeeInfo['profile_picture']) ? $employeeInfo['profile_picture'] : '../../img/defaultpfp.png';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Employee Dashboard | HR2</title>
    <link href="../../css/styles.css" rel="stylesheet" />
    <link href="../../css/calendar.css" rel="stylesheet"/>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .equal-height {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        #notifyIcon {
            width: 50px;
            height: 50px;
            cursor: pointer;
        }
        .collapse {
            transition: width 3s ease;
        }

        #searchInput.collapsing {
            width: 0;
        }

        #searchInput.collapse.show {
            width: 250px; /* Adjust the width as needed */
        }

        .search-bar {
            position: relative;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        #search-results {
            position: absolute;
            width: 100%;
            z-index: 1000;
            display: none; /* Hidden by default */
        }

        #search-results a {
            text-decoration: none;
        }

        .form-control:focus + #search-results {
            display: block; /* Show the results when typing */
        }

    </style>
</head>

<body class="sb-nav-fixed bg-black">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark border-bottom border-1 border-warning">
        <a class="navbar-brand ps-3 text-muted" href="../../employee/supervisor/dashboard.php">Employee Portal</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars text-light"></i></button>
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
            <div class="dropdown search-container" style="position: relative;">
                <form class="d-none d-md-inline-block form-inline">
                    <div class="input-group">
                        <!-- Search Input -->
                        <input class="form-control collapse" id="searchInput" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" data-bs-toggle="dropdown" aria-expanded="false" />
                        <button class="btn btn-outline-warning rounded" id="btnNavbarSearch" type="button" data-bs-toggle="collapse" data-bs-target="#searchInput" aria-expanded="false" aria-controls="searchInput">
                            <i id="searchIcon" class="fas fa-search"></i> <!-- Initial Icon -->
                        </button>
                    </div>
                    <ul id="searchResults" class="dropdown-menu list-group mt-2 bg-transparent" style="width: 100%;"></ul>
                </form>
            </div>
            </form>
        </div>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                    <i class="fa fa-bell" style="font-size:20px" alt="Notification Bell" onclick="showNotification()" style="width: 50px; height: 50px; cursor: pointer;"></i> 
        
                         <div class="sb-sidenav-menu-heading text-center text-muted">Profile</div>  
                        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-light d-flex justify-content-center ms-4" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="<?php echo (!empty($employeeInfo['pfp']) && $employeeInfo['pfp'] !== 'defaultpfp.png') 
                                        ? htmlspecialchars($employeeInfo['pfp']) 
                                        : '../../img/defaultpfp.jpg'; ?>" 
                                        class="rounded-circle border border-light" width="120" height="120" alt="" />
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="../../employee/supervisor/profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="#!">Settings</a></li>
                                    <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                                    <li><hr class="dropdown-divider" /></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a></li>
                                </ul>
                            </li>
                            <li class="nav-item text-light d-flex ms-3 flex-column align-items-center text-center">
                                <span class="big text-light mb-1">
                                    <?php echo htmlspecialchars($employeeInfo['firstname'] . ' ' . $employeeInfo['middlename'] . ' ' . $employeeInfo['lastname']); ?>
                                </span>
                                <span class="big text-light">
                                    <?php echo htmlspecialchars($employeeInfo['position']); ?>
                                </span>
                            </li>
                        </ul>
                        <div class="sb-sidenav-menu-heading text-center text-muted border-top border-1 border-warning mt-3">Employee Dashboard</div>
                        <a class="nav-link text-light" href="../../employee/supervisor/dashboard.php">
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
                                <a class="nav-link text-light" href="../../employee/supervisor/attendance.php">Attendance Scanner</a>
                                <a class="nav-link text-light" href="">View Attendance Record</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLM" aria-expanded="false" aria-controls="collapseLM">
                            <div class="sb-nav-link-icon "><i class="fas fa-calendar-times"></i></div>
                            Leave Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../../employee/supervisor/leave_file.php">File Leave</a>
                                <a class="nav-link text-light" href="../../employee/supervisor/leave_request.php">Leave Request</a>
                                
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePM" aria-expanded="false" aria-controls="collapsePM">
                            <div class="sb-nav-link-icon"><i class="fas fa-line-chart"></i></div>
                            Performance Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../../employee/supervisor/evaluation.php">View Ratings</a>
                            </nav>
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../../employee/supervisor/department.php">Department Evaluation</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSR" aria-expanded="false" aria-controls="collapseSR">
                            <div class="sb-nav-link-icon"><i class="fa fa-address-card"></i></div>
                            Social Recognition
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseSR" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../../employee/supervisor/awardee.php">View Your Rating</a>
                            </nav>
                        </div> 
                        <div class="sb-sidenav-menu-heading text-center text-muted border-top border-1 border-warning mt-3">Feedback</div> 
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseFB" aria-expanded="false" aria-controls="collapseFB">
                            <div class="sb-nav-link-icon"><i class="fas fa-exclamation-circle"></i></div>
                            Report Issue
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseFB" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="">Report Issue</a>
                            </nav>
                        </div> 
                    </div>
                </div>
                <div class="sb-sidenav-footer bg-black border-top border-1 border-warning">
                    <div class="small text-light">Logged in as: <?php echo htmlspecialchars($employeeInfo['role']); ?></div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid position-relative px-4">
                    <h1 class="mb-4 text-light">Dashboard</h1>
                    <div class="container" id="calendarContainer" 
                        style="position: fixed; top: 9%; right: 0; z-index: 1050; 
                        width: 700px; height: 300px; display: none;">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="calendar" class="p-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-3 mt-2">
                            <div class="card bg-dark text-light border-0 equal-height">
                                <div class="card-header border-bottom border-warning text-info">
                                    <h3 class="mb-0">To Do</h3>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item bg-dark text-light fs-4 border-0 d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="task1">
                                                <label class="form-check-label" for="task1">
                                                    <i class="bi bi-check-circle text-warning me-2"></i>Facial Recognition
                                                </label>
                                            </div>
                                        </li>
                                        <li class="list-group-item bg-dark text-light fs-4 border-0 d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="task2">
                                                <label class="form-check-label" for="task2">
                                                    <i class="bi bi-check-circle text-warning me-2"></i>Attendance Record
                                                </label>
                                            </div>
                                        </li>
                                        <li class="list-group-item bg-dark text-light fs-4 border-0 d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="task3">
                                                <label class="form-check-label" for="task3">
                                                    <i class="bi bi-check-circle text-warning me-2"></i>Leave Processing
                                                </label>
                                            </div>
                                        </li>
                                        <li class="list-group-item bg-dark text-light fs-4 border-0 d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="task3">
                                                <label class="form-check-label" for="task3">
                                                    <i class="bi bi-check-circle text-warning me-2"></i>Performance Processing
                                                </label>
                                            </div>
                                        </li>
                                        <li class="list-group-item bg-dark text-light fs-4 border-0 d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="task3">
                                                <label class="form-check-label" for="task3">
                                                    <i class="bi bi-check-circle text-warning me-2"></i>Payroll Processing
                                                </label>
                                            </div>
                                        </li>
                                        <li class="list-group-item bg-dark text-light fs-4 border-0 d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="task3">
                                                <label class="form-check-label" for="task3">
                                                    <i class="bi bi-check-circle text-warning me-2"></i>Social Recognition
                                                </label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2 mb-2">
                            <div class="card bg-dark text-light equal-height">
                                <div class="card-header border-bottom border-1 border-warning text-info">
                                    <h3 class="mb-0">Attendance</h3>
                                </div>
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div>
                                            <h5 class="fw-bold">Today's Date:</h5>
                                            <p class="text-warning">January 18, 2025</p>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold">Time in:</h5>
                                            <p class="text-warning">08:11 AM</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="mb-0">
                                        <h4 class="fw-bold">January</h4>
                                        <div class="row text-center fw-bold">
                                            <!-- Days of the week -->
                                            <div class="col">Sun</div>
                                            <div class="col">Mon</div>
                                            <div class="col">Tue</div>
                                            <div class="col">Wed</div>
                                            <div class="col">Thu</div>
                                            <div class="col">Fri</div>
                                            <div class="col">Sat</div>
                                        </div>

                                        <!-- Calendar rows -->
                                        <div class="row text-center border-top pt-3">
                                            <!-- First week -->
                                            <div class="col"></div> <!-- Empty for days before 1st -->
                                            <div class="col">
                                                <span class="fw-bold">1</span>
                                                <div class="text-success">Present</div>
                                            </div>
                                            <div class="col">
                                                <span class="fw-bold">2</span>
                                                <div class="text-danger">Absent</div>
                                            </div>
                                            <div class="col">
                                                <span class="fw-bold">3</span>
                                                <div class="text-success">Present</div>
                                            </div>
                                            <div class="col">
                                                <span class="fw-bold">4</span>
                                                <div class="text-success">Present</div>
                                            </div>
                                            <div class="col">
                                                <span class="fw-bold">5</span>
                                                <div class="text-danger">Absent</div>
                                            </div>
                                            <div class="col">
                                                <span class="fw-bold">6</span>
                                                <div class="text-success">Present</div>
                                            </div>
                                        </div>

                                        <div class="row text-center pt-3">
                                            <!-- Second week -->
                                            <div class="col">
                                                <span class="fw-bold">7</span>
                                                <div class="text-danger">Absent</div>
                                            </div>
                                            <div class="col">
                                                <span class="fw-bold">8</span>
                                                <div class="text-success">Present</div>
                                            </div>
                                            <div class="col">
                                                <span class="fw-bold">9</span>
                                                <div class="text-danger">Absent</div>
                                            </div>
                                            <div class="col">
                                                <span class="fw-bold">10</span>
                                                <div class="text-success">Present</div>
                                            </div>
                                            <div class="col">
                                                <span class="fw-bold">11</span>
                                                <div class="text-success">Present</div>
                                            </div>
                                            <div class="col">
                                                <span class="fw-bold">12</span>
                                                <div class="text-danger">Absent</div>
                                            </div>
                                            <div class="col">
                                                <span class="fw-bold">13</span>
                                                <div class="text-success">Present</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mt-2">
                            <div class="card bg-dark equal-height">
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
                    </div>
                    <div class="row mb-4">
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
                                            <div class="progress" style="width: 30%; height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 90%;" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
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
                                            <div class="progress" style="width: 30%; height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
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
                                            <div class="progress" style="width: 30%; height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 80%;" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
                <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark text-light">
                            <div class="modal-header">
                                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to log out?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn border-secondary text-light" data-bs-dismiss="modal">Cancel</button>
                                <form action="../../employee/logout.php" method="POST">
                                    <button type="submit" class="btn btn-danger">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <footer class="py-4 bg-light mt-auto bg-dark border-top border-1 border-warning">
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

<script>
    // for calendar only
    let calendar; // Declare calendar variable globally

    function toggleCalendar() {
        const calendarContainer = document.getElementById('calendarContainer');
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
                url: '../../db/holiday.php',  // Endpoint for fetching events
                method: 'GET',
                failure: function() {
                    alert('There was an error fetching events!');
                }
            }
        });

        calendar.render();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const currentDateElement = document.getElementById('currentDate');
        const currentDate = new Date().toLocaleDateString(); // Get the current date
        currentDateElement.textContent = currentDate; // Set the date text
    });

    document.addEventListener('click', function(event) {
        const calendarContainer = document.getElementById('calendarContainer');
        const calendarButton = document.querySelector('button[onclick="toggleCalendar()"]');

        if (!calendarContainer.contains(event.target) && !calendarButton.contains(event.target)) {
            calendarContainer.style.display = 'none';
        }
    });
    // for calendar only end

    function setCurrentTime() {
        const currentTimeElement = document.getElementById('currentTime');
        const currentDateElement = document.getElementById('currentDate');

        const currentDate = new Date();

        // Convert to 12-hour format with AM/PM
        let hours = currentDate.getHours();
        const minutes = currentDate.getMinutes();
        const seconds = currentDate.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12; // If hour is 0, set to 12

        const formattedHours = hours < 10 ? '0' + hours : hours;
        const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
        const formattedSeconds = seconds < 10 ? '0' + seconds : seconds;

        currentTimeElement.textContent = `${formattedHours}:${formattedMinutes}:${formattedSeconds} ${ampm}`;

        // Format the date in text form (e.g., "January 12, 2025")
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        currentDateElement.textContent = currentDate.toLocaleDateString('en-US', options);
    }

    // Update the time every second
    setInterval(setCurrentTime, 1000);

    function showNotification() {
      if (Notification.permission === "granted") {
        new Notification("Hello!", {
          icon: "https://via.placeholder.com/50", // Your icon URL here
        });
      } else if (Notification.permission !== "denied") {
        Notification.requestPermission().then(permission => {
          if (permission === "granted") {
            new Notification("Hello!", {
              icon: "https://via.placeholder.com/50", // Your icon URL here
            });
          }
        });
      }
    }

    const features = [
        { name: "Dashboard", link: "../../employee/supervisor/dashboard.php", path: "Employee Dashboard" },
        { name: "Attendance Scanner", link: "../../employee/supervisor/attendance.php", path: "Time and Attendance/Attendance Scanner" },
        { name: "Leave Request", link: "../../employee/supervisor/leave_request.php", path: "Leave Management/Leave Request" },
        { name: "Evaluation Ratings", link: "../../employee/supervisor/evaluation.php", path: "Performance Management/Evaluation Ratings" },
        { name: "File Leave", link: "../../employee/supervisor/leave_file.php", path: "Leave Management/File Leave" },
        { name: "View Your Rating", link: "../../employee/supervisor/social_recognition.php", path: "Social Recognition/View Your Rating" },
        { name: "Report Issue", link: "../../employee/supervisor/report_issue.php", path: "Feedback/Report Issue" }
    ];

    // Handle search input change
    document.getElementById('searchInput').addEventListener('input', function () {
        let input = this.value.toLowerCase();
        let results = '';

        if (input) {
            // Filter the features based on the search input
            const filteredFeatures = features.filter(feature => 
                feature.name.toLowerCase().includes(input)
            );

            if (filteredFeatures.length > 0) {
                // Generate the HTML for the filtered results
                filteredFeatures.forEach(feature => {
                    results += `                   
                        <a href="${feature.link}" class="list-group-item list-group-item-action">
                            ${feature.name}
                            <br>
                            <small class="text-muted">${feature.path}</small>
                        </a>`;
                });
            } else {
                // If no matches found, show "No result found"
                results = '<li class="list-group-item list-group-item-action">No result found</li>';
            }
        }

        // Update the search results with the filtered features
        document.getElementById('searchResults').innerHTML = results;
        
        if (!input) {
            document.getElementById('searchResults').innerHTML = ''; // Clears the dropdown if input is empty
        }
    });

    // Handle collapse event to clear search input when hidden
    const searchInputElement = document.getElementById('searchInput');
    searchInputElement.addEventListener('hidden.bs.collapse', function () {
        // Clear the search input and search results when the input collapses
        searchInputElement.value = '';  // Clear the input
        document.getElementById('searchResults').innerHTML = '';  // Clear the search results
    });

</script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/employee.js"></script>


</body>

</html>

