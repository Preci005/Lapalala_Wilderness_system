<?php include('../includes/header.php')?>
<?php
// Check if the user is logged in
if (!isset($_SESSION['slogin']) || !isset($_SESSION['srole'])) {
    header('Location: ../index.php');
    exit();
}

// Check if the user has the role of Manager
$userRole = $_SESSION['srole'];
if ($userRole !== 'Manager') {
    header('Location: ../index.php');
    exit();
}

// Get the logged-in manager's employee ID
$managerId = $_SESSION['slogin'];

// Query to get team members (employees with this manager as supervisor)
$query = "SELECT emp_id, first_name, middle_name, last_name, phone_number, designation, email_id, department, image_path 
          FROM tblemployees 
          WHERE supervisor_id = ? 
          ORDER BY first_name ASC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $managerId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
mysqli_stmt_bind_result($stmt, $id, $firstname, $middlename, $lastname, $contact, $designation, $email, $department, $image_path);

$employeeData = [];

while (mysqli_stmt_fetch($stmt)) {
    $employeeData[] = [
        'id' => $id,
        'firstname' => $firstname,
        'middlename' => $middlename,
        'lastname' => $lastname,
        'contact' => $contact,
        'designation' => $designation,
        'email' => $email,
        'department' => $department,
        'image_path' => $image_path,
    ];
}

mysqli_stmt_close($stmt);
?>

<body>
<!-- Pre-loader start -->
 <?php include('../includes/loader.php')?>
<!-- Pre-loader end -->
<div id="pcoded" class="pcoded">
    <div class="pcoded-overlay-box"></div>
    <div class="pcoded-container navbar-wrapper">

        <?php include('../includes/topbar.php')?>

        <!-- Sidebar inner chat end-->
        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                 <?php $page_name = "my_team"; ?>
                <?php include('../includes/sidebar.php')?>
                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <!-- Main-body start -->
                        <div class="main-body">
                            <div class="page-wrapper">
                                <!-- Page-header start -->
                                <div class="page-header">
                                    <div class="row align-items-end">
                                        <div class="col-lg-8">
                                            <div class="page-header-title">
                                                <div class="d-inline">
                                                    <h4>My Team</h4>
                                                    <span>View your team members</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page-header end -->
                                    <!-- Page-body start -->
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <!-- tab content start -->
                                                <div class="tab-content">
                                                    <!-- tab pane info start -->
                                                    <div class="tab-pane active" id="info" role="tabpanel">
                                                        <!-- contact data table card start -->
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h5 class="card-header-text">Team Members</h5>
                                                            </div>
                                                            <div class="card-block contact-details">
                                                                <div class="data_table_main table-responsive dt-responsive">
                                                                    <table id="simpletable" class="table table-striped table-bordered nowrap">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Image</th>
                                                                                <th>Full Name</th>
                                                                                <th>Email</th>
                                                                                <th>Contact</th>
                                                                                <th>Designation</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php if (empty($employeeData)): ?>
                                                                                <tr>
                                                                                    <td colspan="5" class="text-center">No team members found</td>
                                                                                </tr>
                                                                            <?php else: ?>
                                                                                <?php foreach ($employeeData as $employee): ?>
                                                                                    <tr>
                                                                                        <td>
                                                                                            <?php
                                                                                            $imageSrc = !empty($employee['image_path']) ? htmlspecialchars($employee['image_path']) : '../files/assets/images/avatar-4.jpg';
                                                                                            ?>
                                                                                            <img class="img-radius img-40 align-top m-r-15" src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($employee['firstname']); ?>">
                                                                                        </td>
                                                                                        <td>
                                                                                            <?php echo htmlspecialchars($employee['firstname'] . ' ' . $employee['middlename'] . ' ' . $employee['lastname']); ?>
                                                                                        </td>
                                                                                        <td>
                                                                                            <a href="mailto:<?php echo htmlspecialchars($employee['email']); ?>">
                                                                                                <?php echo htmlspecialchars($employee['email']); ?>
                                                                                            </a>
                                                                                        </td>
                                                                                        <td><?php echo htmlspecialchars($employee['contact']); ?></td>
                                                                                        <td><?php echo htmlspecialchars($employee['designation']); ?></td>
                                                                                    </tr>
                                                                                <?php endforeach; ?>
                                                                            <?php endif; ?>
                                                                        </tbody>
                                                                        <tfoot>
                                                                            <tr>
                                                                                <th>Image</th>
                                                                                <th>Full Name</th>
                                                                                <th>Email</th>
                                                                                <th>Contact</th>
                                                                                <th>Designation</th>
                                                                            </tr>
                                                                        </tfoot>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- contact data table card end -->
                                                    </div>
                                                    <!-- tab pane info end -->
                                                </div>
                                                <!-- tab content end -->
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Page-body end -->
                            </div>
                        </div>
                        <!-- Main-body end -->
                        <div id="styleSelector">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Required Jquery -->
    <?php include('../includes/scripts.php')?>
    <script type="text/javascript" src="../files/assets/pages/data-table/js/data-table-custom.js"></script>

</body>

</html>
