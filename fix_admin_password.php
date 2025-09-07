<?php
include 'db.php';

echo "Updating admin password to use proper hashing..." . PHP_EOL;

$hashed_password = password_hash('Admin@123', PASSWORD_DEFAULT);
$update_query = "UPDATE admins SET password = '$hashed_password' WHERE email = 'admin@gmail.com'";

if (mysqli_query($conn, $update_query)) {
    echo 'Admin password updated successfully with hash!' . PHP_EOL;
    echo 'New password hash: ' . $hashed_password . PHP_EOL;
    
    // Verify the update worked
    $verify_query = "SELECT password FROM admins WHERE email = 'admin@gmail.com'";
    $result = mysqli_query($conn, $verify_query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $verify_result = password_verify('Admin@123', $row['password']);
        echo 'Password verification test: ' . ($verify_result ? 'SUCCESS' : 'FAILED') . PHP_EOL;
    }
} else {
    echo 'Error updating password: ' . mysqli_error($conn) . PHP_EOL;
}
?>
