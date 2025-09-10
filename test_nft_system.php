<?php
session_start();
include 'db.php';
include 'nft_certificate_system.php';

echo "<h2>NFT Certificate System Test</h2>";

// Test 1: Check if tables exist
echo "<h3>1. Checking database tables...</h3>";

$tables = ['nft_certificates', 'nft_verifications'];
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✓ Table '$table' exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
    }
}

// Check if nft_certificate_image column exists in courses table
$result = mysqli_query($conn, "SHOW COLUMNS FROM courses LIKE 'nft_certificate_image'");
if (mysqli_num_rows($result) > 0) {
    echo "<p style='color: green;'>✓ Column 'nft_certificate_image' exists in courses table</p>";
} else {
    echo "<p style='color: red;'>✗ Column 'nft_certificate_image' does not exist in courses table</p>";
}

// Test 2: Check courses with certificate templates
echo "<h3>2. Checking courses with NFT certificate templates...</h3>";
$course_check = "SELECT id, course_name, nft_certificate_image FROM courses WHERE nft_certificate_image IS NOT NULL AND nft_certificate_image != ''";
$course_result = mysqli_query($conn, $course_check);

if ($course_result && mysqli_num_rows($course_result) > 0) {
    echo "<p>Courses with certificate templates:</p><ul>";
    while ($course = mysqli_fetch_assoc($course_result)) {
        $image_exists = file_exists($course['nft_certificate_image']) ? '✓' : '✗';
        echo "<li>Course ID: {$course['id']}, Name: {$course['course_name']}, Template: {$course['nft_certificate_image']} $image_exists</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>No courses found with NFT certificate templates</p>";
}

// Test 3: Check existing certificates
echo "<h3>3. Checking existing NFT certificates...</h3>";
$cert_check = "SELECT COUNT(*) as cert_count FROM nft_certificates";
$cert_result = mysqli_query($conn, $cert_check);
if ($cert_result) {
    $cert_row = mysqli_fetch_assoc($cert_result);
    echo "<p>Total certificates issued: {$cert_row['cert_count']}</p>";
} else {
    echo "<p style='color: red;'>Error checking certificates: " . mysqli_error($conn) . "</p>";
}

// Test 4: Check enrollments ready for certificates
echo "<h3>4. Checking completed enrollments...</h3>";
$enrollment_check = "SELECT e.*, c.course_name FROM enrollments e 
                     JOIN courses c ON e.course_id = c.id 
                     WHERE e.completed = 1 
                     ORDER BY e.completed_at DESC 
                     LIMIT 5";
$enrollment_result = mysqli_query($conn, $enrollment_check);

if ($enrollment_result && mysqli_num_rows($enrollment_result) > 0) {
    echo "<p>Recent completed enrollments:</p><ul>";
    while ($enrollment = mysqli_fetch_assoc($enrollment_result)) {
        echo "<li>Course: {$enrollment['course_name']}, Learner ID: {$enrollment['learner_id']}, Completed: {$enrollment['completed_at']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No completed enrollments found</p>";
}

echo "<br><a href='course-management.php' style='color: blue;'>← Back to Course Management</a>";
?>
