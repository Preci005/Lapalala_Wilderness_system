<?php
session_start(); // Start the session

include('includes/config.php');

/**
 * 1️⃣ Validate request
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Retrieve the hashed token from the session
$tokenHash = $_SESSION['reset_token_hash'] ?? '';

if ($newPassword === '' || $confirmPassword === '' || $tokenHash === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required or session expired'
    ]);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Passwords do not match'
    ]);
    exit;
}

/**
 * 2️⃣ Validate token & get user
 */
$stmt = mysqli_prepare(
    $conn,
    "SELECT emp_id
     FROM tblemployees
     WHERE reset_token_hash = ?
       AND reset_token_expires_at > NOW()
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "s", $tokenHash);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $empId);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$empId) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Reset link is invalid or has expired'
    ]);
    exit;
}

/**
 * 3️⃣ Hash new password
 */
$hashedPassword = md5($newPassword);


/**
 * 4️⃣ Update password & invalidate token
 */
$update = mysqli_prepare(
    $conn,
    "UPDATE tblemployees
     SET password = ?,
         reset_token_hash = NULL,
         reset_token_expires_at = NULL
     WHERE emp_id = ?"
);
mysqli_stmt_bind_param($update, "si", $hashedPassword, $empId);
mysqli_stmt_execute($update);

if (mysqli_stmt_affected_rows($update) !== 1) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Password update failed'
    ]);
    exit;
}

// Clear the token from the session
unset($_SESSION['reset_token_hash']);

mysqli_stmt_close($update);

/**
 * 5️⃣ Success response
 */
echo json_encode([
    'status' => 'success',
    'message' => 'Password has been reset successfully'
]);
exit;
?>