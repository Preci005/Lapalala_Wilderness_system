<?php

session_start(); // start session

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include('includes/config.php');

if (!isset($_GET['token']) || empty($_GET['token'])) {
    die('Invalid or missing reset token.');
}

$rawToken  = $_GET['token'];
$tokenHash = hash('sha256', $rawToken);

// Store the hashed token in the session
$_SESSION['reset_token_hash'] = $tokenHash;

/**
 * Validate token
 */
$stmt = mysqli_prepare(
    $conn,
    "SELECT emp_id FROM tblemployees
     WHERE reset_token_hash = ?
     AND reset_token_expires_at > NOW()
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "s", $tokenHash);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) === 0) {
    die('This password reset link is invalid or has expired.');
}

if (!$result) {
    die('SQL error: ' . mysqli_error($conn));
}

$row   = mysqli_fetch_assoc($result);
$empId = $row['emp_id'];
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <title>Reset Password | Leave Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

    <!-- Favicon -->
    <link rel="icon" href="./files/assets/images/favicon.ico" type="image/x-icon">
  
    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,800" rel="stylesheet">

    <!-- Required Framework -->
    <link rel="stylesheet" type="text/css" href="./files/bower_components/bootstrap/css/bootstrap.min.css">

    <!-- Icons -->
    <link rel="stylesheet" type="text/css" href="./files/assets/icon/feather/css/feather.css">
    <link rel="stylesheet" type="text/css" href="./files/assets/icon/icofont/css/icofont.css">

    <!-- Style -->
    <link rel="stylesheet" type="text/css" href="./files/assets/css/style.css">
</head>

<body class="fix-menu">

    <section class="login-block">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">

                    <form class="md-float-material form-material">
                        <div class="auth-box card">
                            <div class="card-block">

                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <i class="feather icon-lock text-primary f-60 p-t-15 p-b-20 d-block"></i>
                                        <h4>Create New Password</h4>
                                        <p class="text-muted">Enter your new password below</p>
                                    </div>
                                </div>

                                <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">

                                <div class="form-group form-primary">
                                    <input type="password" id="new_password" class="form-control" required placeholder="New Password">
                                    <span class="form-bar"></span>
                                </div>

                                <div class="form-group form-primary">
                                    <input type="password" id="confirm_password" class="form-control" required placeholder="Confirm Password">
                                    <span class="form-bar"></span>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <button id="reset-btn" class="btn btn-primary btn-md btn-block waves-effect">
                                            <i class="icofont icofont-lock"></i> Reset Password
                                        </button>
                                    </div>
                                </div>

                                <p class="text-inverse text-right m-t-20">
                                    Back to <a href="index.php">Login</a>
                                </p>

                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script src="./files/bower_components/jquery/js/jquery.min.js"></script>
    <script src="./files/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $('#reset-btn').click(function(e) {
            e.preventDefault();

            const newPassword = $('#new_password').val();
            const confirmPassword = $('#confirm_password').val();
            
            if (!newPassword || !confirmPassword) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Please fill in all fields'
                });
                return;
            }

            if (newPassword !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    text: 'Passwords do not match'
                });
                return;
            }

            $.ajax({
                url: 'reset_forgot_function.php',
                type: 'POST',
                data: {
                    new_password: newPassword,
                    confirm_password: confirmPassword,
                 
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            text: response.message
                        }).then(() => {
                            window.location.href = 'index.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        text: 'Something went wrong. Please try again.'
                    });
                }
            });
        });
    </script>

</body>

</html>