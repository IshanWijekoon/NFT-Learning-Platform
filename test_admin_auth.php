<?php
// Test admin.php authentication
session_start();

echo "Testing admin.php authentication..." . PHP_EOL;

// Simulate login session
$_SESSION['user_id'] = 1;
$_SESSION['email'] = 'admin@gmail.com';
$_SESSION['role'] = 'admin';
$_SESSION['full_name'] = 'admin';

echo "Session variables set:" . PHP_EOL;
echo "- User ID: " . $_SESSION['user_id'] . PHP_EOL;
echo "- Email: " . $_SESSION['email'] . PHP_EOL;
echo "- Role: " . $_SESSION['role'] . PHP_EOL;
echo "- Full Name: " . $_SESSION['full_name'] . PHP_EOL;

// Test the authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "❌ Authentication check: FAILED - Would redirect to login.html" . PHP_EOL;
} else {
    echo "✅ Authentication check: PASSED - Admin access granted" . PHP_EOL;
}

include 'db.php';

// Test admin data retrieval
$admin_id = $_SESSION['user_id'];
$admin_query = "SELECT full_name, email FROM admins WHERE id = '$admin_id'";
$admin_result = mysqli_query($conn, $admin_query);
$admin = mysqli_fetch_assoc($admin_result);

if ($admin) {
    echo "✅ Admin data retrieved successfully:" . PHP_EOL;
    echo "- Name: " . $admin['full_name'] . PHP_EOL;
    echo "- Email: " . $admin['email'] . PHP_EOL;
} else {
    echo "❌ Failed to retrieve admin data" . PHP_EOL;
}
?>
