<?php
session_start();
include '../../db/db_conn.php';
include '../../phpqrcode/qrlib.php'; // Include phpqrcode library

if (!isset($_SESSION['e_id'])) {
    header("Location: ../../employee/login.php");
    exit();
}

if (isset($_SESSION['update_success'])) {
    echo '<script>alert("' . htmlspecialchars($_SESSION['update_success']) . '");</script>';
    unset($_SESSION['update_success']);
}

// Fetch user info
$employeeId = $_SESSION['e_id'];
$sql = "SELECT 
    e.e_id, e.firstname, e.middlename, e.lastname, e.birthdate, e.email, e.created_at,
    e.role, e.position, e.department, e.phone_number, e.address, e.pfp, 
    ua.login_time, 
    -- Fetch the last valid logout time
    (SELECT ua2.logout_time 
     FROM user_activity ua2 
     WHERE ua2.user_id = e.e_id 
     AND ua2.logout_time IS NOT NULL 
     ORDER BY ua2.logout_time DESC 
     LIMIT 1) AS last_logout_time
FROM 
    employee_register e
LEFT JOIN 
    user_activity ua ON e.e_id = ua.user_id
WHERE 
    e.e_id = ? 
ORDER BY 
    ua.login_time DESC 
LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employeeInfo = $result->fetch_assoc();
} else {
    $employeeInfo = null;
}

$stmt->close();
$conn->close();



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="User Profile Dashboard" />
    <meta name="author" content="Your Name" />
    <title>My Profile | HR2</title>
    <link href="../../css/styles.css" rel="stylesheet" />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="../../css/calendar.css" rel="stylesheet"/>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="sb-nav-fixed bg-black">
    <nav class="sb-topnav navbar navbar-expand navbar-dark border-bottom border-1 border-warning bg-dark">
        <a class="navbar-brand ps-3 text-muted" href="../../employee/staff/dashboard.php">Employee Portal</a>
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
                        <div class="sb-sidenav-menu-heading text-center text-muted">Profile</div>
                            <div class="text-center">
                                <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                                    <li class="nav-item dropdown text">
                                        <a class="nav-link dropdown-toggle text-light d-flex justify-content-center ms-4" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <img src="<?php echo (!empty($employeeInfo['pfp']) && $employeeInfo['pfp'] !== 'defaultpfp.png') 
                                                ? htmlspecialchars($employeeInfo['pfp']) 
                                                : '../../img/defaultpfp.jpg'; ?>" 
                                                class="rounded-circle border border-light" width="120" height="120" alt="" />
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                            <li><a class="dropdown-item" href="../../employee/staff/profile.php">Profile</a></li>
                                            <li><a class="dropdown-item" href="#!">Settings</a></li>
                                            <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                                            <li><hr class="dropdown-divider" /></li>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item text-light d-flex ms-3 flex-column align-items-center text-center">
                                        <span class="big text-light mb-1">
                                            <?php
                                                if ($employeeInfo) {
                                                echo htmlspecialchars($employeeInfo['firstname'] . ' ' . $employeeInfo['middlename'] . ' ' . $employeeInfo['lastname']);
                                                } else {
                                                echo "Employee information not available.";
                                                }
                                            ?>
                                        </span>      
                                        <span class="big text-light">
                                            <?php
                                                if ($employeeInfo) {
                                                echo htmlspecialchars($employeeInfo['position']);
                                                } else {
                                                echo "Employee information not available.";
                                                }
                                            ?>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        <div class="sb-sidenav-menu-heading text-center text-muted border-top border-1 border-warning mt-3">Employee Dashboard</div>
                        <a class="nav-link text-light" href="../../employee/staff/dashboard.php">
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
                                <a class="nav-link text-light" href="../../employee/staff/attendance.php">Attendance Scanner</a>
                                <a class="nav-link text-light" href="">Timesheet</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLM" aria-expanded="false" aria-controls="collapseLM">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar-times"></i></div>
                            Leave Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                            <a class="nav-link text-light" href="../../employee/staff/leave_file.php">File Leave</a>
                            <a class="nav-link text-light" href="../../employee/staff/leave_request.php">Endorse Leave</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePM" aria-expanded="false" aria-controls="collapsePM">
                            <div class="sb-nav-link-icon"><i class="fas fa-line-chart"></i></div>
                            Performance Management
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapsePM" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                            <a class="nav-link text-light" href="../../employee/staff/evaluation.php">Evaluation</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed text-light" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSR" aria-expanded="false" aria-controls="collapseSR">
                            <div class="sb-nav-link-icon"><i class="fa fa-address-card"></i></div>
                            Social Recognition
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseSR" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link text-light" href="../../employee/staff/recognitions.php">View Your Rating</a>
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
                <div class="sb-sidenav-footer bg-black text-light border-top border-1 border-warning">
                    <div class="small">Logged in as: <?php echo htmlspecialchars($employeeInfo['role']); ?></div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main class="bg-black">
                <div class="container-fluid position-relative px-4">
                    <div class="container-fluid" id="calendarContainer" 
                        style="position: fixed; top: 9%; right: 0; z-index: 1050; 
                        max-width: 100%; display: none;">
                        <div class="row">
                            <div class="col-12 col-md-10 col-lg-8 mx-auto">
                                <div id="calendar" class="p-2"></div>
                            </div>
                        </div>
                    </div>
                    <h1 class="big mb-2 text-light">My Profile</h1>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header bg-dark border-1 border-bottom border-warning text-light">
                                    <h3 class="card-title text-start">User Information</h3>
                                </div>
                                <div class="card-body bg-dark">
                                    <div class="row">
                                        <div class="col-xl-2">
                                            <div class="d-flex justify-content-center">
                                                <img src="<?php echo (!empty($employeeInfo['pfp']) && $employeeInfo['pfp'] !== 'defaultpfp.png') 
                                                    ? htmlspecialchars($employeeInfo['pfp']) 
                                                    : '../../img/defaultpfp.jpg'; ?>" 
                                                    class="rounded-circle border border-light img-fluid" 
                                                    style="max-width: 230px; max-height: 230px; min-width: 230px; min-height: 230px; object-fit: cover; cursor: pointer;" 
                                                    alt="Profile Picture" 
                                                    id="profilePic" data-bs-toggle="modal" data-bs-target="#profilePicModal" />
                                            </div>
                                            <div class="modal fade" id="profilePicModal" tabindex="-1" aria-labelledby="profilePicModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered"> <!-- Set the modal size using 'modal-lg' for large -->
                                                    <div class="modal-content bg-dark text-light" style="width: 600px; height: 500px;">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="profilePicModalLabel"><?php echo htmlspecialchars($employeeInfo['firstname'] . ' ' . $employeeInfo['middlename'] . ' ' . $employeeInfo['lastname']); ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <img src="<?php echo (!empty($employeeInfo['pfp']) && $employeeInfo['pfp'] !== 'defaultpfp.png') 
                                                                ? htmlspecialchars($employeeInfo['pfp']) 
                                                                : '../../img/defaultpfp.jpg'; ?>" 
                                                                class="img-fluid rounded" style="width: 500px; height: 400px;" alt="Profile Picture" /> <!-- img-fluid to make it responsive -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-center align-items-center mt-4 mb-3">
                                            <button class="btn btn-light text-center w-30 h-50" type="button" id="editPictureDropdown" 
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 2048 2048">
                                                <path fill="currentColor" d="M1468 1139q-52 43-89 96q-83-42-173-62t-184-21q-108 0-206 27t-184 76t-154 119t-119 155t-76 185t-27 206H128q0-146 43-281t124-247t193-196t254-129q-54-36-96-83t-72-102t-46-116t-16-126q0-106 40-199t110-162t163-110t199-41t199 40t162 110t110 163t41 199q0 65-16 126t-45 117t-73 102t-97 83q43 14 83 31t80 40M640 640q0 80 30 149t82 122t122 83t150 30q79 0 149-30t122-82t83-122t30-150q0-79-30-149t-82-122t-123-83t-149-30q-80 0-149 30t-122 82t-83 123t-30 149m1090 511q66 0 125 25t102 69t69 102t26 125q0 66-25 124t-69 102t-103 69t-125 26q-97 0-177-54l-292 292q-19 19-45 19t-45-19t-19-45t19-45l292-292q-54-80-54-177q0-66 25-124t69-102t102-69t125-26m0 514q40 0 75-15t61-41t42-62t16-75q0-40-15-75t-42-61t-61-42t-76-15q-40 0-75 15t-61 42t-42 61t-15 75q0 40 15 75t41 61t62 42t75 15"/>
                                            </svg>
                                                 </button>


                                                <div class="dropdown">
                                                    <ul class="dropdown-menu" aria-labelledby="editPictureDropdown">
                                                        <li>
                                                            <a class="dropdown-item fw-bold" href="javascript:void(0);" id="changePictureOption">Change Profile Picture</a>
                                                        </li>
                                                        <hr>
                                                        <li>
                                                            <button class="dropdown-item fw-bold text-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteProfilePictureModal">
                                                                Delete Profile Picture
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-10 mb-4">
                                            <div class="">
                                                <div class="d-flex justify-content-start">
                                                    <button type="button" class="btn btn-primary btn-sm me-2"style="margin-left: 3rem;" data-bs-toggle="modal" data-bs-target="#qrCodeModal">Show QR Code</button>
                                                    <a href="../../employee/staff/change_pass.php" class="btn btn-primary"> Change password </a>
                                                </div>
                                            </div>
                                            <div class="mt-3" style="margin-left: 3rem;">
                                                <div class="form-group row">
                                                    <div class="col-sm-6 bg-dark form-floating mb-3">
                                                        <input class="form-control fw-bold" name="fname" value="<?php echo htmlspecialchars($employeeInfo['firstname'] . ' ' . $employeeInfo['middlename'] . ' ' . $employeeInfo['lastname']); ?>" readonly>
                                                        <label class="fw-bold">Name:</label>
                                                    </div>

                                                    <div class="col-sm-6 bg-dark form-floating">
                                                        <input class="form-control fw-bold" name="id" value="<?php echo htmlspecialchars($employeeInfo['e_id']); ?>" readonly>
                                                        <label class="fw-bold">ID No.:</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-3" style="margin-left: 3rem;">
                                                <div class="form-group row">
                                                    <div class="col-sm-6 bg-dark form-floating mb-3">
                                                        <input class="form-control fw-bold" name="position" value="<?php echo htmlspecialchars($employeeInfo['position']); ?>" readonly>
                                                        <label class="fw-bold">Role:</label>
                                                    </div>

                                                    <div class="col-sm-6 bg-dark form-floating">
                                                        <input class="form-control fw-bold" name="department" value="<?php echo htmlspecialchars($employeeInfo['department']); ?>" readonly>
                                                        <label class="fw-bold">Department:</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-3" style="margin-left: 3rem;">
                                                <div class="form-group row">
                                                    <div class="col-sm-12 bg-dark form-floating mb-3">
                                                        <input class="form-control fw-bold" name="email" value="<?php echo htmlspecialchars($employeeInfo['email']); ?>" readonly>
                                                        <label class="fw-bold">Email:</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-header bg-dark text-light">
                                            <hr>
                                            <h3 class="card-title text-center">Edit Information</h3>
                                        </div>
                                        <div class="card-body bg-dark">
                                            <form id="infoForm" action="../../employee_db/staff/update_profile.php" method="post">
                                                <div class="row mb-3">
                                                    <div class="col-sm-4 bg-dark form-floating mb-3">
                                                        <input type="text" class="form-control fw-bold" id="inputfName" name="firstname" value="<?php echo htmlspecialchars($employeeInfo['firstname']); ?>" readonly required>
                                                        <label for="inputfName" class="fw-bold">First Name:</label>
                                                    </div>
                                                    <div class="col-sm-4 bg-dark form-floating mb-3">
                                                        <input type="text" class="form-control fw-bold" id="inputmName" name="middlename" value="<?php echo htmlspecialchars($employeeInfo['middlename']); ?>" readonly required>
                                                        <label for="inputmName" class="fw-bold">Middle Name:</label>
                                                    </div>
                                                    <div class="col-sm-4 bg-dark form-floating">
                                                        <input type="text" class="form-control fw-bold" id="inputlName" name="lastname" value="<?php echo htmlspecialchars($employeeInfo['lastname']); ?>" readonly required>
                                                        <label for="inputlName" class="fw-bold">Last Name:</label>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-sm-6 bg-dark form-floating mb-3">
                                                        <input type="date" class="form-control fw-bold" id="inputbirth" name="birthdate" value="<?php echo htmlspecialchars($employeeInfo['birthdate']); ?>" readonly required>
                                                        <label for="inputbirth" class="fw-bold">Birthdate:</label>
                                                    </div>
                                                    <div class="col-sm-6 bg-dark form-floating">
                                                        <input type="email" class="form-control fw-bold" id="inputEmail" name="email" value="<?php echo htmlspecialchars($employeeInfo['email']); ?>" readonly required>
                                                        <label for="inputEmail" class="fw-bold">Email Address:</label>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-sm-6 bg-dark form-floating mb-3">
                                                        <input type="number" class="form-control fw-bold" id="inputEmail" id="inputPhone" name="phone_number" value="<?php echo htmlspecialchars($employeeInfo['phone_number']); ?>" readonly required>
                                                        <label for="inputPhone" class="fw-bold">Phone Number:</label>
                                                    </div>
                                                    <div class="col-sm-6 bg-dark form-floating">
                                                        <input class="form-control fw-bold" id="inputAddress" name="address" value="<?php echo htmlspecialchars($employeeInfo['address']); ?>" readonly required>
                                                        <label for="inputAddress" class="fw-bold">Address:</label>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <button type="submit" class="btn btn-primary d-none">Save Changes</button>
                                                    <button type="button" id="editButton" class="btn btn-primary">Update Information</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <form action="../../employee_db/staff/update_employee_pfp.php" method="post" enctype="multipart/form-data" id="profilePictureForm" style="display:none;">
                                        <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*" onchange="showConfirmationModal();">
                                    </form>
                                    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content bg-dark text-light" style="width: 500px; height: 400px;">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Profile Picture Update</h5>
                                                    <button type="button" class="close text-light bg-dark" data-bs-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to update your profile picture with the selected image?</p>
                                                    <!-- Center the image in the modal -->
                                                    <div class="d-flex justify-content-center align-items-center img-fluid rounded">
                                                        <img id="modalProfilePicturePreview" src="#" alt="Selected Profile Picture"  style="width: 150px; height: 150px;">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="button" class="btn btn-primary" onclick="submitProfilePictureForm()">Update</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 mb-4">
                        <div class="card bg-dark text-light">
                            <div class="card-header border-bottom border-warning">
                                <h3 class="mb-0">User Activity</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>Your last login was on:</strong> 
                                    <?php 
                                        if (!empty($employeeInfo['login_time'])) {
                                            $login = strtotime($employeeInfo['login_time']); 
                                            echo date("l, F j, Y | g:i A", $login); 
                                        } else {
                                            echo "No login time available";
                                        }
                                    ?>
                                </p>
                                <hr>
                                <p><strong>Your last logout was on:</strong> 
                                    <?php 
                                        if (!empty($employeeInfo['last_logout_time'])) {
                                            $logout = strtotime($employeeInfo['last_logout_time']); 
                                            echo date("l, F j, Y | g:i A", $logout); 
                                        } else {
                                            echo "No logout time available";
                                        }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
                <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark text-light">
                            <div class="modal-header boder-1 border-warning">
                                <h5 class="modal-title" id="qrCodeModalLabel">Employee QR Code</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="<?php echo $qrImagePath; ?>" alt="QR Code" class="img-fluid border border-light" width="200">
                                <hr>
                                <p class="mt-3 text-start">Employee ID: <?php echo htmlspecialchars($employeeInfo['e_id']); ?></p>
                                <p class="text-start">Name: <?php echo htmlspecialchars($employeeInfo['firstname'] . ' ' . $employeeInfo['middlename'] . ' ' . $employeeInfo['lastname']); ?></p>
                            </div>
                            <div class="modal-footer boder-1 border-warning">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
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
                                <form action="../../employee/logout.php" method="POST">
                                    <button type="submit" class="btn btn-danger">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>  
                <div class="modal fade" id="deleteProfilePictureModal" tabindex="-1" aria-labelledby="deleteProfilePictureLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content bg-dark text-light">
                            <div class="modal-header border-bottom border-warning">
                                <h5 class="modal-title" id="deleteProfilePictureLabel">Delete Profile Picture</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-start">
                                <p>Are you sure you want to delete your profile picture?</p>
                            </div>
                            <div class="modal-footer border-top border-warning">
                                <form action="../../employee_db/staff/delete_employee_pfp.php" method="post">
                                    <input type="hidden" name="employeeId" value="<?php echo $employeeInfo['e_id']; ?>">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <footer class="py-4 bg-dark text-light mt-auto border-top border-warning">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2024</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms & Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
<script>
    //CALENDAR 
    let calendar;
        function toggleCalendar() {
            const calendarContainer = document.getElementById('calendarContainer');
                if (calendarContainer.style.display === 'none' || calendarContainer.style.display === '') {
                    calendarContainer.style.display = 'block';
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
                    height: 440,  
                    events: {
                    url: '../../db/holiday.php',  
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
            const currentDate = new Date().toLocaleDateString(); 
            currentDateElement.textContent = currentDate; 
        });

        document.addEventListener('click', function(event) {
            const calendarContainer = document.getElementById('calendarContainer');
            const calendarButton = document.querySelector('button[onclick="toggleCalendar()"]');

                if (!calendarContainer.contains(event.target) && !calendarButton.contains(event.target)) {
                    calendarContainer.style.display = 'none';
                    }
        });
        //CALENDAR END

        //TIME 
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

    setCurrentTime();
    setInterval(setCurrentTime, 1000);
        //TIME END


        document.addEventListener('DOMContentLoaded', function() {
        // Check if there is a message to show
        <?php if (isset($message)) : ?>
            var modalMessage = "<?php echo $message; ?>";
            var messageType = "<?php echo $messageType; ?>";

            // Set the modal content based on success or error
            document.getElementById('modalMessage').textContent = modalMessage;
            
            // Change modal appearance based on message type (optional)
            if (messageType === "success") {
                document.getElementById('messageModalLabel').textContent = "Success";
                document.querySelector('.modal-content').classList.add('bg-success', 'text-white');
            } else {
                document.getElementById('messageModalLabel').textContent = "Error";
                document.querySelector('.modal-content').classList.add('bg-danger', 'text-white');
            }

            // Show the modal
            var myModal = new bootstrap.Modal(document.getElementById('messageModal'));
            myModal.show();
        <?php endif; ?>
    });
</script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../../js/employee.js"></script>
    <script src="../../js/profile.js"></script>
</body>
</html>