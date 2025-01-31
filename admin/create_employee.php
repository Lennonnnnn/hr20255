<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Employee Account Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <style>
        /* Ensures the page fills the full height */
        html, body {
            height: 100%;
        }
        /* Makes the layout use the full height and pushes footer to the bottom */
        #layoutAuthentication {
            min-height: 100%;
            display: flex;
            flex-direction: column;
        }
        #layoutAuthentication_content {
            flex-grow: 1;
        }
    </style>
</head>

<body class="bg-black">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-7">
                            <div class="card shadow-lg border-0 rounded-lg mt-5 my-5 bg-dark">
                                <div class="card-header border-bottom border-1 border-warning">
                                    <h3 class="text-center text-light font-weight-light my-4">Create Employee Account</h3>
                                    <div id="form-feedback" class="alert text-center" style="display: none;"></div>
                                </div>
                                <div class="card-body">
                                    <form id="registrationForm" action="../db/registeremployee_db.php" method="POST">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" id="inputFirstName" type="text"
                                                        name="firstname" placeholder="Enter your first name" required />
                                                    <label for="inputFirstName">First name</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating"> 
                                                    <input class="form-control" id="inputLastName" type="text"
                                                        name="lastname" placeholder="Enter your last name" required />
                                                    <label for="inputLastName">Last name</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputEmail" type="email"
                                                name="email" placeholder="name@example.com" required />
                                            <label for="inputEmail">Email address</label>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" id="inputPassword" type="password"
                                                        name="password" placeholder="Create a password" required />
                                                    <label for="inputPassword">Password</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" id="inputPasswordConfirm"
                                                        type="password" name="confirm_password" placeholder="Confirm password" required />
                                                    <label for="inputPasswordConfirm">Confirm Password</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input type="hidden" id="inputRoleHidden" name="role" value="Employee">
                                                    <input class="form-control" type="text" id="inputRole" value="Employee" disabled>
                                                    <label for="inputRole">Role</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <select id="inputDepartment" name="department" class="form-select" required>
                                                        <option value="" disabled selected></option>
                                                        <option value="Finance Department">Finance Department</option>
                                                        <option value="Administration Department">Administration Department</option>
                                                        <option value="Sales Department">Sales Department</option>
                                                        <option value="Credit Department">Credit Department</option>
                                                        <option value="Human Resource Department">Human Resource Department</option>
                                                        <option value="IT Department">IT Department</option>
                                                    </select>
                                                    <label for="inputDepartment">Select Department</label>
                                                </div>          
                                            </div>
                                        </div>
                                        <div class="form-floating mt-3">
                                            <select id="inputPosition" name="position" class="form-select" required>
                                                <option value="" disabled selected>Select department first.</option>
                                            </select>
                                            <label for="inputPosition">Select Position</label>
                                        </div> 
                                        <div class="mt-4 mb-0">
                                            <div class="d-grid">
                                                <button class="btn btn-primary btn-block" type="submit">Create Account</button>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-center mt-2 mb-2"> <a class="btn border-secondary w-100 text-light" href="../admin/employee.php">Back</a></div>
                                            </div>  
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center border-top border-1 border-warning">
                                    <p class="small text-center text-muted mt-1">Human Resource 2</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        
        <!-- Updated Footer matching Admin registration -->
        <div id="layoutAuthentication_footer">
            <footer class="py-4 bg-dark text-light mt-auto border-1 border-warning border-top">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#" class="text-light">Privacy Policy</a>
                            &middot;
                            <a href="#" class="text-light">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    
    <!-- JavaScript for dynamic position filtering based on department -->
    <script>
        const positionsByDepartment = {
            "Finance Department": ["Financial Controller", "Accountant", "Credit Analyst", "Supervisor", "Staff"],
            "Administration Department": ["Facilities Manager", "Operations Manager", "Customer Service Representative", "Supervisor", "Staff"],
            "Sales Department": ["Sales Manager", "Sales Representative", "Marketing Coordinator", "Supervisor", "Staff"],
            "Credit Department": ["Loan Officer", "Loan Collection Officer", "Credit Risk Analyst", "Supervisor", "Staff"],
            "Human Resource Department": ["HR Manager", "Recruitment Specialists", "Training Coordinator", "Supervisor", "Staff"],
            "IT Department": ["IT Manager", "Network Administrator", "System Administrator", "IT Support Specialist", "Supervisor", "Staff"]
        };

        function filterPositions() {
            const departmentSelect = document.getElementById("inputDepartment");
            const positionSelect = document.getElementById("inputPosition");
            const selectedDepartment = departmentSelect.value;

            // Clear the previous options in the position dropdown
            positionSelect.innerHTML = '<option value="" disabled selected></option>';

            // Populate the position dropdown with positions relevant to the selected department
            if (positionsByDepartment[selectedDepartment]) {
                positionsByDepartment[selectedDepartment].forEach(position => {
                    const option = document.createElement("option");
                    option.value = position;
                    option.textContent = position;
                    positionSelect.appendChild(option);
                });
            }
        }

        // Attach event listener to department dropdown
        document.getElementById("inputDepartment").addEventListener("change", filterPositions);
    </script>
</body>

</html>
