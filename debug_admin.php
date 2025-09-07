<?php
include 'db.php';

// Check if admin exists
$query = 'SELECT id, email, password, full_name FROM admins WHERE email = "admin@gmail.com"';
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
    echo 'Admin found:' . PHP_EOL;
    echo 'ID: ' . $admin['id'] . PHP_EOL;
    echo 'Email: ' . $admin['email'] . PHP_EOL;
    echo 'Full Name: ' . ($admin['full_name'] ?? 'NULL') . PHP_EOL;
    echo 'Password Hash: ' . $admin['password'] . PHP_EOL;
    echo 'Password Hash Length: ' . strlen($admin['password']) . PHP_EOL;
    echo 'Is likely hashed: ' . (strlen($admin['password']) > 50 ? 'Yes' : 'No') . PHP_EOL;
    
    // Test password verification
    $test_password = 'Admin@123';
    $verify_result = password_verify($test_password, $admin['password']);
    echo 'Password verification result: ' . ($verify_result ? 'SUCCESS' : 'FAILED') . PHP_EOL;
    
    // Check if password is stored as plain text
    if ($admin['password'] === $test_password) {
        echo 'Password is stored as PLAIN TEXT (needs hashing)' . PHP_EOL;
    }
    
} else {
    echo 'No admin found with email admin@gmail.com' . PHP_EOL;
    echo 'Checking all admins:' . PHP_EOL;
    
    $all_query = 'SELECT id, email, full_name FROM admins';
    $all_result = mysqli_query($conn, $all_query);
    
    if ($all_result && mysqli_num_rows($all_result) > 0) {
        while ($admin = mysqli_fetch_assoc($all_result)) {
            echo 'ID: ' . $admin['id'] . ', Email: ' . $admin['email'] . ', Name: ' . ($admin['full_name'] ?? 'NULL') . PHP_EOL;
        }
    } else {
        echo 'No admins found in database' . PHP_EOL;
        echo 'Creating admin account...' . PHP_EOL;
        
        // Create admin account
        $hashed_password = password_hash('Admin@123', PASSWORD_DEFAULT);
        $create_query = "INSERT INTO admins (full_name, email, password, created_at) VALUES ('Administrator', 'admin@gmail.com', '$hashed_password', NOW())";
        
        if (mysqli_query($conn, $create_query)) {
            echo 'Admin account created successfully!' . PHP_EOL;
        } else {
            echo 'Error creating admin account: ' . mysqli_error($conn) . PHP_EOL;
        }
    }
}
?>
