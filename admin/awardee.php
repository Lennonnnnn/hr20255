<?php
// Start session and check admin login
session_start();
if (!isset($_SESSION['a_id'])) {
    header("Location: ../admin/login.php");
    exit();
}

// Include database connection
include '../db/db_conn.php';

// Fetch user info
$adminId = $_SESSION['a_id'];
$sql = "SELECT a_id, firstname, middlename, lastname, birthdate, email, role, position, department, phone_number, address, pfp FROM admin_register WHERE a_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$adminInfo = $result->fetch_assoc();

function getTopEmployeesByCriterion($conn, $criterion, $criterionLabel, $index) {
    // SQL query to fetch the highest average score for each employee
    $sql = "SELECT e.e_id, e.firstname, e.lastname, e.department, e.pfp, 
                   AVG(ae.$criterion) AS avg_score
            FROM employee_register e
            JOIN admin_evaluations ae ON e.e_id = ae.e_id
            GROUP BY e.e_id
            ORDER BY avg_score DESC
            LIMIT 1";  // Select the top employee with the highest average score

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    // Path to the default profile picture
    $defaultPfpPath = '../img/defaultpfp.jpg'; // Update this path to your actual default profile picture location
    $defaultPfp = base64_encode(file_get_contents($defaultPfpPath));

    // Output the awardee's information for each criterion
    echo "<div class='category' id='category-$index' style='display: none;'>";
    echo "<h3 class='text-center mt-4'>$criterionLabel</h3>";

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Check if profile picture exists, else use the default picture
            if (file_exists($row['pfp']) && !empty($row['pfp'])) {
                $pfp = base64_encode(file_get_contents($row['pfp']));
            } else {
                $pfp = $defaultPfp;
            }

            // Calculate percentage for the progress circle
            $scorePercentage = ($row['avg_score'] / 10) * 100;
            
            echo "<div class='employee-card'>";
            
            
            echo "<div class='metrics-container'>";
            
            // Left metrics
            echo "<div class='metrics-column'>";
            echo "<div class='metric-box fade-in'>";
            echo "<span class='metric-label'>Projects Completed</span>";
            echo "<span class='metric-value'>10</span>";
            echo "</div>";
            
            echo "<div class='metric-box fade-in' style='animation-delay: 0.2s;'>";
            echo "<span class='metric-label'>Project Quality</span>";
            echo "<span class='metric-value'>92%</span>";
            echo "</div>";
            echo "</div>";

            // Center profile section
            echo "<div class='profile-section'>";
            echo "<div class='progress-circle-container'>";
            echo "<div class='progress-circle' data-progress='" . $scorePercentage . "'>";
            echo "<div class='profile-image-container'>";
            if (!empty($pfp)) {
                echo "<img src='data:image/jpeg;base64,$pfp' alt='Profile Picture' class='profile-image'>";
            }
            echo "</div>";
            echo "</div>";
            echo "</div>";
            
            echo "<div class='profile-info'>";
            echo "<h2 class='employee-name'>" . htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) . "</h2>";
            echo "<p class='department-name'>" . htmlspecialchars($row['department']) . "</p>";
            echo "</div>";
            echo "</div>";

            // Right metrics
            echo "<div class='metrics-column'>";
            echo "<div class='metric-box fade-in' style='animation-delay: 0.4s;'>";
            echo "<span class='metric-label'>Efficiency</span>";
            echo "<span class='metric-value'>60%</span>";
            echo "</div>";
            
            echo "<div class='metric-box fade-in' style='animation-delay: 0.6s;'>";
            echo "<span class='metric-label'>Timeline</span>";
            echo "<span class='metric-value'>75%</span>";
            echo "</div>";
            echo "</div>";
            
            echo "</div>"; // End metrics-container
            
            echo "<div class='employee-id fade-in' style='animation-delay: 0.8s;'>";
            echo "Employee ID: " . htmlspecialchars($row['e_id']);
            echo "</div>";
            
            echo "</div>"; // End employee-card
        }
    } else {
        echo "<p class='text-center'>No outstanding employees found for $criterionLabel.</p>";
    }

    echo "</div>"; // End of category
    $stmt->close();
}

// Function to get the current reactions from the database
function getReactions($conn) {
    $sql = "SELECT reaction, COUNT(*) as count FROM reactions GROUP BY reaction";
    $result = $conn->query($sql);
    $reactions = [];
    while ($row = $result->fetch_assoc()) {
        $reactions[$row['reaction']] = $row['count'];
    }
    return $reactions;
}

// Function to save a reaction to the database
function saveReaction($conn, $reaction) {
    $sql = "INSERT INTO reactions (reaction) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $reaction);
    $stmt->execute();
    $stmt->close();
}

// Handle reaction submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reaction'])) {
    saveReaction($conn, $_POST['reaction']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$reactions = getReactions($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outstanding Employees</title>
    <link href="../css/styles.css" rel="stylesheet" />
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link href="../css/calendar.css" rel="stylesheet"/>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .card {
            border: 2px solid #ddd; 
            border-radius: 10px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            padding: 10px; 
            background-color: #f9f9f9;
        }
        .card-img {
            border-radius: 10px;
        }
        .card-body {
            padding-left: 10px;
        }
        .card-title {
            font-size: 20px; 
            font-weight: bold; 
            color: #333;
        }
        .card-text {
            font-size: 14px;
        }
        .category {
            display: none;
        }
        .btn {
            transition: transform 0.3s, background-color 0.3s; /* Smooth transition */
            border-radius: 20px;
            padding: 5px 10px;
            font-size: 14px;
        }

        .btn:hover {
            transform: translateY(-2px); /* Raise the button up */
        }
        
        .emoji-container {
            display: none;
            gap: 15px;
            cursor: pointer;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .emoji {
            font-size: 30px;
            transition: transform 0.2s ease;
            padding: 10px;
        }
        .emoji:hover {
            transform: scale(1.2);
        }
        .reaction {
            margin-top: 15px;
            font-size: 18px;
            color: #333;
        }
        .saved-reaction {
            margin-top: 10px;
            color: #007bff;
        }
        .open-btn {
            font-size: 16px;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .open-btn:hover {
            background-color: #0056b3;
        }
        .reaction-count {
            font-size: 18px;
            color: #333;
            margin-top: 10px;
            display: flex;
            gap: 15px;
        }
        .reaction-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .selected-emoji {
            font-size: 50px;
            margin-top: 20px;
        }
        .employee-card {
            background: linear-gradient(135deg, #172554 0%, #1e3a8a 100%);
            border-radius: 20px;
            padding: 2rem;
            max-width: 1200px;
            margin: 2rem auto;
            color: white;
        }

        .dashboard-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 2rem;
            color: white;
        }

        .metrics-container {
            display: grid;
            grid-template-columns: 1fr 1.5fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .metrics-column {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .metric-box {
            background: rgba(64, 61, 223, 0.27);
            border-radius: 15px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
        }

        .metric-label {
            color: #22d3ee;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .metric-value {
            font-size: 1.875rem;
            font-weight: bold;
        }

        .profile-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .progress-circle-container {
            position: relative;
            width: 200px;
            height: 200px;
        }

        .progress-circle {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            position: relative;
            background: #1e3a8a;
        }

        .profile-image-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #22d3ee;
            overflow: hidden;
        }

        .profile-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info {
            text-align: center;
            margin-top: 1rem;
        }

        .employee-name {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        .department-name {
            color: #22d3ee;
            font-size: 0.875rem;
        }

        .employee-id {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 1rem;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
            opacity: 0;
        }

        /* Progress Circle Animation */
        @keyframes progressCircle {
            from {
                stroke-dashoffset: 628;
            }
            to {
                stroke-dashoffset: var(--progress);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .metrics-container {
                grid-template-columns: 1fr;
            }
            
            .employee-card {
                margin: 1rem;
                padding: 1rem;
            }
        }

        /* Additional styles for progress circle */
        .progress-ring {
            position: absolute;
            top: 0;
            left: 0;
            transform: rotate(-90deg);
        }

        .progress-ring__circle {
            transition: stroke-dashoffset 0.5s ease-out;
        }
    </style>
</head>
<body class="sb-nav-fixed bg-black">
    <nav class="sb-topnav navbar navbar-expand navbar-dark border-bottom border-1 border-warning bg-dark">
        <a class="navbar-brand ps-3 text-muted" href="../admin/dashboard.php" style="font-size: 18px;">Microfinance</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars text-light"></i></button>
        <div class="d-flex ms-auto me-0 me-md-3 my-2 my-md-0 align-items-center">
            <div class="text-light me-3 p-2 rounded shadow-sm bg-gradient" id="currentTimeContainer" 
                style="background: linear-gradient(45deg, #333333, #444444); border-radius: 5px; font-size: 14px;">
                <span class="d-flex align-items-center">
                    <span class="pe-2">
                        <i class="fas fa-clock"></i> 
                        <span id="currentTime">00:00:00</span>
                    </span>
                    <button class="btn btn-outline-warning btn-sm ms-2" type="button" onclick="toggleCalendar()" style="font-size: 14px;">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="currentDate">00/00/0000</span>
                    </button>
                </span>
            </div>
            <form class="d-none d-md-inline-block form-inline">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" style="font-size: 14px;" />
                    <button class="btn btn-warning" id="btnNavbarSearch" type="button" style="font-size: 14px;"><i class="fas fa-search"></i></button>
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
                                    <li><hr class="dropdown-divider" /></li>
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
                        <div class="sb-sidenav-menu-heading text-center text-muted border-top border-1 border-warning">Account Management</div>
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
            <main class="container-fluid position-relative bg-black">
                <div class="container" id="calendarContainer" 
                    style="position: fixed; top: 9%; right: 0; z-index: 1050; 
                    width: 700px; display: none;">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="calendar" class="p-2"></div>
                        </div>
                    </div>
                </div>   
                <h1 class="mb-2 text-light ms-2">Outstanding Employees by Evaluation Criteria</h1>            
                <div class="container text-light">

                    <!-- Top Employees for Different Criteria -->
                    <?php getTopEmployeesByCriterion($conn, 'quality', 'Quality of Work', 1); ?>
                    <?php getTopEmployeesByCriterion($conn, 'communication_skills', 'Communication Skills', 2); ?>
                    <?php getTopEmployeesByCriterion($conn, 'teamwork', 'Teamwork', 3); ?>
                    <?php getTopEmployeesByCriterion($conn, 'punctuality', 'Punctuality', 4); ?>
                    <?php getTopEmployeesByCriterion($conn, 'initiative', 'Initiative', 5); ?>

                    <!-- Navigation buttons for manually controlling the categories -->
                    <div class="text-center">
                        <button class="btn btn-primary" onclick="showPreviousCategory()">Previous</button>
                        <button class="btn btn-primary" onclick="showNextCategory()">Next</button>
                    </div>
                    
                    <button class="open-btn" onclick="toggleEmojis()">Open Emoji Reactions</button>

                    <div class="emoji-container" id="emojiContainer">
                        <form method="POST" id="reactionForm">
                            <input type="hidden" name="reaction" id="reactionInput">
                            <div class="emoji" onclick="submitReaction('Like')">👍</div>
                            <div class="emoji" onclick="submitReaction('Love')">❤️</div>
                            <div class="emoji" onclick="submitReaction('Haha')">😂</div>
                            <div class="emoji" onclick="submitReaction('Wow')">😲</div>
                            <div class="emoji" onclick="submitReaction('Sad')">😢</div>
                            <div class="emoji" onclick="submitReaction('Angry')">😡</div>
                        </form>
                    </div>

                    <div class="reaction" id="reactionText"></div>

                    <div class="saved-reaction" id="savedReactionText"></div>

                    <div class="selected-emoji" id="selectedEmoji"></div>

                    <div class="reaction-count">
                        <?php foreach ($reactions as $reaction => $count): ?>
                            <div class="reaction-item">
                                <span><?php echo htmlspecialchars($reaction); ?></span>
                                <span><?php echo $count; ?></span>
                                <span class="user-reaction" style="display: none;">(You)</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
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
                        url: '../db/holiday.php',  
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
    
            currentDate.setHours(currentDate.getHours() + 0);
                const hours = currentDate.getHours();
                const minutes = currentDate.getMinutes();
                const seconds = currentDate.getSeconds();
                const formattedHours = hours < 10 ? '0' + hours : hours;
                const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
                const formattedSeconds = seconds < 10 ? '0' + seconds : seconds;

            currentTimeElement.textContent = `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
            currentDateElement.textContent = currentDate.toLocaleDateString();
        }
        setCurrentTime();
        setInterval(setCurrentTime, 1000);
        //TIME END

        let currentCategoryIndex = 1;
        const totalCategories = 5; // Total number of categories

        function showNextCategory() {
            // Hide the current category
            document.getElementById(`category-${currentCategoryIndex}`).style.display = 'none';

            // Update to the next category index, loop back to 1 if at the end
            currentCategoryIndex = (currentCategoryIndex % totalCategories) + 1;

            // Show the next category
            document.getElementById(`category-${currentCategoryIndex}`).style.display = 'block';
        }

        function showPreviousCategory() {
            // Hide the current category
            document.getElementById(`category-${currentCategoryIndex}`).style.display = 'none';

            // Update to the previous category index, loop back to totalCategories if at the start
            currentCategoryIndex = (currentCategoryIndex - 1) || totalCategories;

            // Show the previous category
            document.getElementById(`category-${currentCategoryIndex}`).style.display = 'block';
        }

        // Start the slideshow, show the first category immediately
        window.onload = function() {
            // Show the first category immediately
            document.getElementById(`category-1`).style.display = 'block';
            
            // Start the slideshow after showing the first category
            setInterval(showNextCategory, 3000); // Change every 3 seconds
        };
         // Toggle the visibility of the emoji container when button is clicked
         function toggleEmojis() {
            const emojiContainer = document.getElementById("emojiContainer");
            emojiContainer.style.display = emojiContainer.style.display === "none" || emojiContainer.style.display === "" ? "flex" : "none";
        }

        // Load the saved reaction (if any) from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const savedReaction = localStorage.getItem('userReaction');
            if (savedReaction) {
                document.getElementById('savedReactionText').innerHTML = `You previously reacted with: ${savedReaction}`;
                const reactionItems = document.querySelectorAll('.reaction-item');
                reactionItems.forEach(item => {
                    if (item.querySelector('span').textContent === savedReaction) {
                        item.querySelector('.user-reaction').style.display = 'inline';
                    }
                });
            }
        });

        // Function to save the user's reaction
        function saveReaction(reaction) {
            // Display the selected reaction
            const reactionText = document.getElementById("reactionText");
            reactionText.style.display = "block";
            reactionText.innerHTML = `You reacted with: ${reaction}`;

            // Save the reaction to localStorage
            localStorage.setItem('userReaction', reaction);

            // Show the saved reaction below
            document.getElementById('savedReactionText').innerHTML = `You saved your reaction: ${reaction}`;

            // Show the selected emoji
            const selectedEmoji = document.getElementById("selectedEmoji");
            selectedEmoji.innerHTML = getEmoji(reaction);
        }

        // Function to get the emoji based on the reaction
        function getEmoji(reaction) {
            switch (reaction) {
                case 'Like': return '👍';
                case 'Love': return '❤️';
                case 'Haha': return '😂';
                case 'Wow': return '😲';
                case 'Sad': return '😢';
                case 'Angry': return '😡';
                default: return '';
            }
        }

        function submitReaction(reaction) {
            document.getElementById('reactionInput').value = reaction;
            document.getElementById('reactionForm').submit();
        }

        // Load the saved reaction (if any) from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const savedReaction = localStorage.getItem('userReaction');
            if (savedReaction) {
                document.getElementById('savedReactionText').innerHTML = `You previously reacted with: ${savedReaction}`;
                const reactionItems = document.querySelectorAll('.reaction-item');
                reactionItems.forEach(item => {
                    if (item.querySelector('span').textContent === savedReaction) {
                        item.querySelector('.user-reaction').style.display = 'inline';
                    }
                });

                // Show the selected emoji
                const selectedEmoji = document.getElementById("selectedEmoji");
                selectedEmoji.innerHTML = getEmoji(savedReaction);
            }
        });
    </script>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../js/admin.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>