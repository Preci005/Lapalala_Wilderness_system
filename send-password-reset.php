<?php
include('includes/config.php');
include('sendmail.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !isset($_POST['action'])
    || $_POST['action'] !== 'forgot_password') {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Invalid request'
    ));
    exit;
}

$email = trim($_POST['email'] ?? '');

if ($email === '') {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Email address is required'
    ));
    exit;
}

/**
 * STEP 1: Check if user exists (NO get_result)
 */
$stmt = mysqli_prepare(
    $conn,
    "SELECT emp_id FROM tblemployees WHERE email_id = ? LIMIT 1"
);

if (!$stmt) {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Database prepare failed'
    ));
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

/* bind result instead of get_result */
mysqli_stmt_bind_result($stmt, $empId);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (empty($empId)) {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'No account found with this email'
    ));
    exit;
}

/**
 * STEP 2: Generate token
 */
$rawToken   = bin2hex(random_bytes(32));
$tokenHash = hash('sha256', $rawToken);


/**
 * STEP 3: Save token
 * Let MySQL calculate the expiration time using DATE_ADD
 */
$update = mysqli_prepare(
    $conn,
    "UPDATE tblemployees
     SET reset_token_hash = ?, 
         reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR)
     WHERE email_id = ?"
);

if (!$update) {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Database update prepare failed'
    ));
    exit;
}

/* Only two parameters now: token hash and email */
mysqli_stmt_bind_param($update, "ss", $tokenHash, $email);
mysqli_stmt_execute($update);

/* IMPORTANT: check affected rows */
if (mysqli_stmt_affected_rows($update) !== 1) {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Token was not saved (DB schema or email mismatch)'
    ));
    exit;
}

mysqli_stmt_close($update);

/**
 * STEP 4: Send email
 */
if (!sendPasswordResetEmail($email, $rawToken)) {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Email failed, but token was saved'
    ));
    exit;
}

echo json_encode(array(
    'status' => 'success',
    'message' => 'Password reset link has been sent to your email'
));
exit;
