
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Forgot Password | Leave Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

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

<?php include('includes/config.php'); ?>

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
                                        <i class="feather icon-mail text-primary f-60 p-t-15 p-b-20 d-block"></i>
                                        <h4>Forgot Password</h4>
                                        <p class="text-muted">Enter your email to receive a reset link</p>
                                    </div>
                                </div>

                                <div class="form-group form-primary">
                                    <input type="email" id="email" class="form-control" required placeholder="Email Address">
                                    <span class="form-bar"></span>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <button id="send-link" class="btn btn-primary btn-md btn-block waves-effect">
                                            <i class="icofont icofont-email"></i> Send Reset Link
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
        $('#send-link').click(function(e) {
            e.preventDefault();

            var email = $('#email').val();

            if (email.trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    text: 'Please enter your email address'
                });
                return;
            }

            $.ajax({
                url: 'send-password-reset.php',
                type: 'POST',
                data: {
                    email: email,
                    action: 'forgot_password'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            text: response.message
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