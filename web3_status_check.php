<?php
include 'db.php';

echo "<h1>Web3 Integration Status Check</h1>";

// Check if tables exist
$tables = ['web3_enrollments', 'web3_certificates', 'wallet_profiles'];
$all_tables_exist = true;

foreach ($tables as $table) {
    $check_table = "SHOW TABLES LIKE '$table'";
    $result = mysqli_query($conn, $check_table);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<p>✅ Table '$table' exists</p>";
    } else {
        echo "<p>❌ Table '$table' does NOT exist</p>";
        $all_tables_exist = false;
    }
}

// Check if files exist
$files = [
    'web3-wallet.js' => 'Web3 Wallet Manager JavaScript',
    'enroll_course_web3.php' => 'Web3 Enrollment API',
    'wallet_profile_api.php' => 'Wallet Profile API',
    'wallet-dashboard.php' => 'Wallet Dashboard',
    'web3-guide.html' => 'Web3 Guide'
];

echo "<h2>File Status</h2>";
$all_files_exist = true;

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "<p>✅ $description ($file) exists</p>";
    } else {
        echo "<p>❌ $description ($file) does NOT exist</p>";
        $all_files_exist = false;
    }
}

// Test database connection
echo "<h2>Database Connection</h2>";
if ($conn) {
    echo "<p>✅ Database connection successful</p>";
} else {
    echo "<p>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
}

// Check if web3_wallet_address column was added to learners table
echo "<h2>Database Schema Updates</h2>";
$check_column = "SHOW COLUMNS FROM learners LIKE 'web3_wallet_address'";
$result = mysqli_query($conn, $check_column);

if (mysqli_num_rows($result) > 0) {
    echo "<p>✅ web3_wallet_address column exists in learners table</p>";
} else {
    echo "<p>❌ web3_wallet_address column NOT found in learners table</p>";
}

// Overall status
echo "<h2>Overall Status</h2>";
if ($all_tables_exist && $all_files_exist && $conn) {
    echo "<div style='background: #10b981; color: white; padding: 1rem; border-radius: 8px; margin: 1rem 0;'>";
    echo "<h3>🎉 Web3 Integration is Ready!</h3>";
    echo "<p>All components are properly installed and configured.</p>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ul>";
    echo "<li>Test wallet connection on the <a href='course-browser.php' style='color: #fbbf24;'>course browser</a></li>";
    echo "<li>Try enrolling in a course with Web3</li>";
    echo "<li>Check your <a href='wallet-dashboard.php' style='color: #fbbf24;'>wallet dashboard</a></li>";
    echo "<li>Read the <a href='web3-guide.html' style='color: #fbbf24;'>Web3 guide</a></li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #dc2626; color: white; padding: 1rem; border-radius: 8px; margin: 1rem 0;'>";
    echo "<h3>⚠️ Setup Incomplete</h3>";
    echo "<p>Some components are missing. Please run the setup script:</p>";
    echo "<code style='background: rgba(255,255,255,0.2); padding: 0.5rem; border-radius: 4px; display: block; margin: 0.5rem 0;'>php setup_web3_tables.php</code>";
    echo "</div>";
}

echo "<h2>Test Links</h2>";
echo "<ul>";
echo "<li><a href='course-browser.php'>Course Browser (with Web3 integration)</a></li>";
echo "<li><a href='wallet-dashboard.php'>Wallet Dashboard</a></li>";
echo "<li><a href='web3-guide.html'>Web3 Integration Guide</a></li>";
echo "<li><a href='setup_web3_tables.php'>Re-run Database Setup</a></li>";
echo "</ul>";

echo "<style>";
echo "body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 2rem; background: #f5f5f5; }";
echo "h1, h2 { color: #333; }";
echo "p { margin: 0.5rem 0; }";
echo "ul { margin: 1rem 0; }";
echo "a { color: #667eea; text-decoration: none; }";
echo "a:hover { text-decoration: underline; }";
echo "code { background: #e5e7eb; padding: 0.2rem 0.4rem; border-radius: 4px; }";
echo "</style>";

mysqli_close($conn);
?>