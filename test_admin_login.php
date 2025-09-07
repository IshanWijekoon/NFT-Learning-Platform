<?php
// Test admin login
session_start();
include 'db.php';

echo "Testing admin login..." . PHP_EOL;

$email = 'admin@gmail.com';
$password = 'Admin@123';
$role = 'admin';

// Simulate the login process
$table = 'admins';
$sql = "SELECT id, email, password, full_name FROM `$table` WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "❌ User not found" . PHP_EOL;
    exit;
}

echo "✅ User found: " . $user['email'] . PHP_EOL;
echo "📋 Full name: " . $user['full_name'] . PHP_EOL;

// Test password verification
if (password_verify($password, $user['password'])) {
    echo "✅ Password verification: SUCCESS" . PHP_EOL;
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $role;
    $_SESSION['full_name'] = $user['full_name'] ?? '';
    
    echo "✅ Session set successfully" . PHP_EOL;
    echo "🔗 Redirect URL: admin.php" . PHP_EOL;
    
} else {
    echo "❌ Password verification: FAILED" . PHP_EOL;
}
?>
